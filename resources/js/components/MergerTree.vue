<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, shallowRef, watch } from 'vue';
import { FINAL, banks as source, mergers } from '@/data/merger-tree';

const props = defineProps({
	panelId: { type: String, required: true },
	title: { type: String, default: '' },
	text: { type: String, default: '' },
});

// The chart switches layout on its own width, not the viewport's: below this
// it shows one label at a time and pins the detail panel to the screen bottom.
const WIDE_MIN = 960;
const X_MIN = 95;
const X_MAX = 1130;
const CHIP_GAP = 8;
// A merger row sits at least this far below the row above, so the lines into
// it can turn above its labels rather than through them.
const MERGER_GAP = 80;
const DOT_GAP = 5;
const NUMBERS = { 2: 'Zwei', 3: 'Drei', 4: 'Vier', 5: 'Fünf', 6: 'Sechs' };
const EASE = 'cubic-bezier(.22, .7, .25, 1)';
// Eases each keyframe step, as the agency's CSS keyframes did.
const POP = 'cubic-bezier(.16, 1.2, .36, 1)';

const STROKES = { a: 'stroke-sand', b: 'stroke-aubergine', c: 'stroke-rosewood', d: 'stroke-seafoam' };
const FILLS = { a: 'fill-sand', b: 'fill-aubergine', c: 'fill-rosewood', d: 'fill-seafoam', final: 'fill-brand' };

// Nothing selected: every line at full strength, the panel tells the whole story.
const NEUTRAL = {};

const banks = source.map((bank) => ({ ...bank, parents: [], children: [] }));
const byId = new Map(banks.map((bank) => [bank.id, bank]));

for (const [from, to] of mergers) {
	byId.get(to).parents.push(byId.get(from));
	byId.get(from).children.push(byId.get(to));
}

for (const bank of banks) {
	bank.title = `Raiffeisenbank ${bank.name}`;
	bank.kind = bank.parents.length ? 'Fusion' : 'Gründung';
	bank.text ??= `Gründung der ${bank.title}.`;
	bank.label = bank.id === FINAL ? bank.title : bank.name;
}

const edges = mergers.map(([from, to]) => ({ id: `${from}--${to}`, from: byId.get(from), to: byId.get(to) }));

const years = [...new Set(banks.map((bank) => bank.year))]
	.sort((a, b) => a - b)
	.map((year) => ({
		year,
		y: Math.min(...banks.filter((bank) => bank.year === year).map((bank) => bank.y)),
	}));

const shifts = new Map();

years.reduce((shift, row, index) => {
	const gap = index ? row.y - years[index - 1].y : Infinity;
	const merges = banks.some((bank) => bank.year === row.year && bank.parents.length);
	const next = shift + (merges ? Math.max(0, MERGER_GAP - gap) : 0);
	shifts.set(row.year, next);
	return next;
}, 0);

for (const row of years) row.y += shifts.get(row.year);
for (const bank of banks) bank.y += shifts.get(bank.year);

// Room below the final bank for its label.
const HEIGHT = Math.max(...banks.map((bank) => bank.y)) + 64;

const clamp = (value, min, max) => Math.max(min, Math.min(value, max));

const listNames = (names) => `${names.slice(0, -1).join(', ')} und ${names.at(-1)}`;

function walk(bank, key, seen = new Set()) {
	for (const next of bank[key]) {
		if (!seen.has(next)) {
			seen.add(next);
			walk(next, key, seen);
		}
	}
	return seen;
}

const lineage = (bank) => new Set([bank, ...walk(bank, 'parents'), ...walk(bank, 'children')]);

const tree = ref(null);
const chart = ref(null);
const svg = ref(null);
const rail = ref(null);
const detail = ref(null);

const nodeEls = {};
const traceEls = {};
const chipTextEls = {};

const open = ref(false);
const wide = ref(false);
const width = ref(0);
// Shallow, so the bank stays the raw object the Set lookups compare against.
const selection = shallowRef(NEUTRAL);
let pinned = selection.value;
const chipWidths = reactive({});
const detailTop = ref(0);

let toggle = null;
let toggleLabel = '';
let observer = null;
let animations = [];
let lastTap = -Infinity;
let scrollFrame = 0;

const reducedMotion = () => matchMedia('(prefers-reduced-motion: reduce)').matches;

const positions = computed(() => {
	const left = wide.value ? 150 : 76;
	const right = wide.value ? 60 : 20;
	const scale = (width.value - left - right) / (X_MAX - X_MIN);
	return Object.fromEntries(banks.map((bank) => [bank.id, left + (bank.x - X_MIN) * scale]));
});

const paths = computed(() => Object.fromEntries(edges.map((edge) => {
	const ax = positions.value[edge.from.id];
	const bx = positions.value[edge.to.id];
	const top = edge.from.y + 8;
	const bottom = edge.to.y - 8;
	const clear = edge.to.id === FINAL ? 16 : 13 + 28 + 8;
	const bend = Math.min(edge.from.y + (edge.to.y - edge.from.y) * .65, edge.to.y - clear);
	const run = bend - top + Math.abs(bx - ax);
	return [edge.id, {
		d: `M${ax} ${top} V${bend} H${bx} V${bottom}`,
		// Lines into the same bank share their last stretch; pinning the dots
		// to the y axis lays them on top of each other instead of side by side.
		offset: (((bend - run) % DOT_GAP) + DOT_GAP) % DOT_GAP,
		// The mask overshoots both ends so it never halves the first or last dot.
		trace: `M${ax} ${top - 3} V${bend} H${bx} V${bottom + 3}`,
		length: Math.ceil(run + bottom - bend) + 8,
	}];
})));

const chips = computed(() => {
	const height = wide.value ? 28 : 26;
	const rows = new Map();
	const boxes = {};

	for (const bank of banks) {
		const w = chipWidths[bank.id] ?? 0;
		// The final bank's three merger lines arrive from above, so its label sits below.
		const y = bank.id === FINAL ? bank.y + 16 : bank.y - 13 - height;
		const box = { x: clamp(positions.value[bank.id] - w / 2, 0, width.value - w), y, w, h: height };
		boxes[bank.id] = box;
		rows.set(y, [...(rows.get(y) ?? []), box]);
	}

	// Only the wide layout shows every label, so only there can they collide.
	if (wide.value) {
		for (const row of rows.values()) {
			row.sort((a, b) => a.x - b.x);
			for (let i = 1; i < row.length; i++) {
				row[i].x = Math.max(row[i].x, row[i - 1].x + row[i - 1].w + CHIP_GAP);
			}
			row.at(-1).x = Math.min(row.at(-1).x, width.value - row.at(-1).w);
			for (let i = row.length - 2; i >= 0; i--) {
				row[i].x = Math.max(0, Math.min(row[i].x, row[i + 1].x - CHIP_GAP - row[i].w));
			}
		}
	}

	return boxes;
});

const isNeutral = computed(() => selection.value === NEUTRAL);

const active = computed(() => selection.value.bank
	? [selection.value.bank]
	: banks.filter((bank) => bank.year === selection.value.year));

const related = computed(() => {
	if (isNeutral.value) return new Set(banks);
	return selection.value.bank ? lineage(selection.value.bank) : new Set(active.value);
});

const activeYear = computed(() => selection.value.bank?.year ?? selection.value.year);

const description = computed(() => {
	const list = active.value;

	if (isNeutral.value) {
		return {
			year: '1902–2016',
			title: 'Elf Gründungen, neun Fusionen',
			text: 'Aus elf Raiffeisenbanken, gegründet zwischen 1902 und 1957, ist Schritt für Schritt die heutige Raiffeisenbank Weissenstein geworden.',
		};
	}

	if (list.length === 1) {
		return { year: list[0].year, title: list[0].title, text: list[0].text };
	}

	const count = NUMBERS[list.length] ?? list.length;

	if (list.every((bank) => bank.kind === 'Gründung')) {
		return {
			year: activeYear.value,
			title: `${count} Gründungen`,
			text: `Gründung der Raiffeisenbanken ${listNames(list.map((bank) => bank.name))}.`,
		};
	}

	return {
		year: activeYear.value,
		title: list.every((bank) => bank.kind === 'Fusion') ? `${count} Fusionen` : `${count} Ereignisse`,
		text: list.map((bank) => bank.text).join(' '),
	};
});

const isHighlighted = (bank) => active.value.includes(bank);

const isEdgeOn = (edge) => isNeutral.value || (selection.value.bank
	? related.value.has(edge.from) && related.value.has(edge.to)
	: active.value.includes(edge.from) || active.value.includes(edge.to));

const markerSize = (bank) => bank.id === FINAL ? 16 : wide.value ? 12 : 10;

function selectBank(bank) {
	selection.value = { bank };
}

function selectYear(year) {
	const matches = banks.filter((bank) => bank.year === year);
	selection.value = matches.length === 1 ? { bank: matches[0] } : { year };
}

// Hover only previews; a click, tap, keyboard focus or scroll pins the
// selection, and the chart falls back to it when the mouse leaves. A click on
// empty chart space or Escape pins the neutral state again.
function pin(select) {
	select();
	pinned = selection.value;
}

function onTap(select) {
	lastTap = performance.now();
	pin(select);
}

function onHover(event, select) {
	if (event.pointerType !== 'mouse') return;
	// Holds off the narrow layout's scroll-follow, which would undo the hover.
	lastTap = performance.now();
	select();
}

function reset() {
	pin(() => selection.value = NEUTRAL);
}

function onLeave(event) {
	if (event.pointerType === 'mouse') selection.value = pinned;
}

function measureChips() {
	// Measured in bold, the weight a label takes when selected.
	svg.value.classList.add('is-measuring');
	for (const bank of banks) {
		chipWidths[bank.id] = Math.ceil(chipTextEls[bank.id].getComputedTextLength()) + (wide.value ? 28 : 20);
	}
	svg.value.classList.remove('is-measuring');
}

async function placeDetail() {
	if (!wide.value) return;
	await nextTick();
	if (!active.value.length) {
		detailTop.value = 0;
		return;
	}
	const y = active.value.reduce((sum, bank) => sum + bank.y, 0) / active.value.length;
	const height = detail.value.offsetHeight;
	detailTop.value = Math.round(clamp(y - height / 2, 0, rail.value.clientHeight - height));
}

async function relayout() {
	wide.value = tree.value.clientWidth >= WIDE_MIN;
	await nextTick();
	width.value = chart.value.clientWidth;
	await nextTick();
	measureChips();
	placeDetail();
}

function stop() {
	animations.forEach((animation) => animation.cancel());
	animations = [];
}

// The agency's two chapters: every bank pops in, oldest first, then the
// merger lines grow in, one merger year at a time.
function play() {
	stop();

	if (reducedMotion()) return;

	const order = [...banks].sort((a, b) => a.year - b.year || a.y - b.y || a.x - b.x);

	order.forEach((bank, index) => {
		animations.push(nodeEls[bank.id].animate(
			[
				{ opacity: 0, transform: 'translateY(-8px) scale(.87)', easing: POP },
				{ opacity: 1, offset: .75, easing: POP },
				{ opacity: 1, transform: 'none' },
			],
			{ duration: 420, delay: 120 + index * 155, fill: 'backwards' },
		));
	});

	const stages = [...new Set(edges.map((edge) => edge.to.year))].sort((a, b) => a - b);

	for (const edge of edges) {
		animations.push(traceEls[edge.id].animate(
			[{ strokeDashoffset: paths.value[edge.id].length }, { strokeDashoffset: 0 }],
			{ duration: 670, delay: 3500 + stages.indexOf(edge.to.year) * 760, fill: 'backwards' },
		));
	}
}

async function setOpen(next) {
	open.value = next;
	toggle.setAttribute('aria-expanded', String(next));
	toggle.textContent = next ? 'Weniger anzeigen' : toggleLabel;

	if (!next) {
		stop();
		return;
	}

	lastTap = performance.now();
	selection.value = NEUTRAL;
	pinned = NEUTRAL;
	await nextTick();
	play();
	toggle.closest('[data-merger-tree-band]')?.scrollIntoView({ block: 'start' });
}

// On narrow layouts the bank nearest a reading line follows the page scroll.
function followScroll() {
	scrollFrame = 0;

	if (wide.value || !open.value || performance.now() - lastTap < 800) return;

	const line = innerHeight * .4 - chart.value.getBoundingClientRect().top;

	if (line < 0 || line > HEIGHT) {
		if (!isNeutral.value) pin(() => selection.value = NEUTRAL);
		return;
	}

	let best = null;
	let bestScore = Infinity;

	for (const bank of banks) {
		const score = Math.abs(bank.y - line) + Math.abs(positions.value[bank.id] - width.value / 2) * .02;
		if (score < bestScore) {
			best = bank;
			bestScore = score;
		}
	}

	if (selection.value.bank !== best) pin(() => selectBank(best));
}

function onScroll() {
	if (!scrollFrame) scrollFrame = requestAnimationFrame(followScroll);
}

const onToggle = () => setOpen(!open.value);

watch(selection, placeDetail);

onMounted(() => {
	toggle = document.querySelector(`[aria-controls="${props.panelId}"]`);
	toggleLabel = toggle.textContent.trim();
	toggle.addEventListener('click', onToggle);

	observer = new ResizeObserver(relayout);
	observer.observe(tree.value);
	document.fonts?.ready.then(relayout);
	addEventListener('scroll', onScroll, { passive: true });
});

onBeforeUnmount(() => {
	toggle?.removeEventListener('click', onToggle);
	observer?.disconnect();
	removeEventListener('scroll', onScroll);
	stop();
});
</script>

<template>
	<div
		class="grid transition-[grid-template-rows] duration-600 motion-reduce:transition-none"
		:class="open ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'"
		:style="{ transitionTimingFunction: EASE }"
		:inert="open ? null : ''">
		<!-- clip rather than hidden, so the sticky detail panel still sticks to the viewport;
			min-w-0 because clip, unlike hidden, leaves the chart's width as a floor -->
		<div class="min-h-0 min-w-0 overflow-clip">
			<div class="pt-40 md:pt-56 lg:pt-72">
				<div
					class="bg-snow px-16 pt-32 md:px-32 lg:px-44 lg:pt-44"
					:class="wide && 'pb-32 lg:pb-44'">

					<h3 v-if="title" class="text-balance font-bold text-[32px] md:text-[44px] lg:text-[64px] leading-[1.05] tracking-[-.02em] mb-12 md:mb-16">
						{{ title }}
					</h3>

					<div v-if="text" class="max-w-[56.25rem] text-[16px] md:text-[18px] lg:text-[20px] leading-[1.4] mb-16 md:mb-20">
						{{ text }}
					</div>

					<div class="text-[15px] md:text-[16px] text-charcoal mb-32 lg:mb-40">
						Wählen Sie eine Bank oder ein Jahr, um den Weg nachzuverfolgen.
					</div>

					<div
						ref="tree"
						@pointerleave="onLeave"
						@keydown.esc="reset"
						:class="wide ? 'grid grid-cols-[minmax(0,1fr)_280px] gap-32' : 'relative'">

						<div ref="chart">
							<svg
								ref="svg"
								@click.self="reset"
								:viewBox="`0 0 ${width} ${HEIGHT}`"
								:width="width"
								:height="HEIGHT"
								class="block overflow-visible"
								role="group"
								aria-label="Gründungen und Fusionen bis zur Raiffeisenbank Weissenstein">

								<defs>
									<mask
										v-for="edge in edges"
										:id="`merger-tree-${edge.id}`"
										:key="edge.id"
										maskUnits="userSpaceOnUse"
										x="0"
										y="0"
										:width="width"
										:height="HEIGHT">
										<path
											:ref="(el) => traceEls[edge.id] = el"
											:d="paths[edge.id].trace"
											:stroke-dasharray="paths[edge.id].length"
											fill="none"
											stroke="white"
											stroke-width="14" />
									</mask>
								</defs>

								<text
									v-for="row in years"
									:key="row.year"
									x="0"
									:y="row.y"
									tabindex="0"
									role="button"
									:aria-label="`Ereignisse im Jahr ${row.year}`"
									:aria-pressed="row.year === activeYear && !selection.bank"
									class="font-bold tabular-nums cursor-pointer outline-none [dominant-baseline:central] transition-[font-size,fill] duration-300 hover:fill-brand focus-visible:fill-brand focus-visible:underline"
									:class="[
										row.year === activeYear ? 'fill-brand' : 'fill-ink',
										wide
											? (row.year === activeYear ? 'text-[34px]' : 'text-[25px]')
											: (row.year === activeYear ? 'text-[22px]' : 'text-[15px]'),
									]"
									@click="onTap(() => selectYear(row.year))"
									@pointerenter="onHover($event, () => selectYear(row.year))"
									@focus="pin(() => selectYear(row.year))"
									@keydown.enter.space.prevent="selectYear(row.year)">
									{{ row.year }}
								</text>

								<path
									v-for="edge in edges"
									:key="edge.id"
									:d="paths[edge.id].d"
									:mask="`url(#merger-tree-${edge.id})`"
									:stroke-dashoffset="paths[edge.id].offset"
									class="fill-none [stroke-dasharray:0_5] [stroke-linecap:round] transition-[opacity,stroke-width] duration-400"
									:class="[STROKES[edge.from.group], isEdgeOn(edge) ? 'opacity-100 stroke-[2.75]' : 'opacity-15 stroke-2']" />

								<g
									v-for="bank in banks"
									:key="bank.id"
									:ref="(el) => nodeEls[bank.id] = el"
									tabindex="0"
									role="button"
									:aria-label="`${bank.year}, ${bank.kind}: ${bank.title}`"
									:aria-pressed="selection.bank === bank"
									class="group cursor-pointer outline-none origin-center [transform-box:fill-box]"
									@click="onTap(() => selectBank(bank))"
									@pointerenter="onHover($event, () => selectBank(bank))"
									@focus="pin(() => selectBank(bank))"
									@keydown.enter.space.prevent="selectBank(bank)">

									<rect
										:x="positions[bank.id] - 18"
										:y="bank.y - 18"
										width="36"
										height="36"
										class="fill-transparent" />

									<rect
										:x="positions[bank.id] - markerSize(bank) / 2"
										:y="bank.y - markerSize(bank) / 2"
										:width="markerSize(bank)"
										:height="markerSize(bank)"
										class="group-focus-visible:stroke-ink group-focus-visible:stroke-2 transition-opacity duration-300"
										:class="[FILLS[bank.group], related.has(bank) ? 'opacity-100' : 'opacity-22']" />

									<g :class="wide || selection.bank === bank ? 'visible' : 'invisible'">
										<!-- An opaque backing, so a dimmed label still hides the lines behind it -->
										<rect
											:x="chips[bank.id].x"
											:y="chips[bank.id].y"
											:width="chips[bank.id].w"
											:height="chips[bank.id].h"
											class="fill-snow" />
										<g
											class="transition-opacity duration-300"
											:class="related.has(bank) ? 'opacity-100' : 'opacity-22'">
											<rect
												:x="chips[bank.id].x"
												:y="chips[bank.id].y"
												:width="chips[bank.id].w"
												:height="chips[bank.id].h"
												class="transition-colors duration-300"
												:class="isHighlighted(bank) ? 'fill-linen' : 'fill-mist'" />
											<text
												:ref="(el) => chipTextEls[bank.id] = el"
												:x="chips[bank.id].x + chips[bank.id].w / 2"
												:y="chips[bank.id].y + chips[bank.id].h / 2 + 1"
												class="fill-ink pointer-events-none [text-anchor:middle] [dominant-baseline:central] [.is-measuring_&]:font-bold"
												:class="[
													wide ? 'text-[15px]' : 'text-[14px]',
													isHighlighted(bank) || !wide ? 'font-bold' : 'font-normal',
												]">
												{{ bank.label }}
											</text>
										</g>
									</g>

								</g>

							</svg>
						</div>

						<div
							ref="rail"
							:class="wide ? 'relative' : 'sticky bottom-0 z-10 -mx-16 md:-mx-32 lg:-mx-44'">
							<aside
								ref="detail"
								aria-live="polite"
								class="bg-mist"
								:class="wide
									? 'absolute inset-x-0 top-0 p-24 transition-transform duration-600 motion-reduce:transition-none'
									: 'min-h-168 px-16 pt-18 pb-22 md:px-32 lg:px-44'"
								:style="wide ? { transform: `translateY(${detailTop}px)`, transitionTimingFunction: EASE } : null">
								<div
									class="text-brand font-bold leading-none tabular-nums mb-6"
									:class="isNeutral
										? (wide ? 'text-[34px]' : 'text-[30px]')
										: (wide ? 'text-[48px]' : 'text-[40px]')">
									{{ description.year }}
								</div>
								<h4
									class="text-balance font-bold leading-[1.2] mb-6"
									:class="wide ? 'text-[22px]' : 'text-[19px]'">
									{{ description.title }}
								</h4>
								<div
									class="leading-[1.45]"
									:class="wide ? 'text-[17px]' : 'text-[16px]'">
									{{ description.text }}
								</div>
							</aside>
						</div>

					</div>

				</div>
			</div>
		</div>
	</div>
</template>
