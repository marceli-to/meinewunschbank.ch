<script setup>
import { nextTick, reactive, ref } from 'vue';
import Button from '@/components/Button.vue';
import FormCheckbox from '@/components/form/FormCheckbox.vue';
import FormInput from '@/components/form/FormInput.vue';
import FormLabel from '@/components/form/FormLabel.vue';
import FormLegend from '@/components/form/FormLegend.vue';
import FormTextarea from '@/components/form/FormTextarea.vue';
import PhotoUpload from '@/components/form/PhotoUpload.vue';
import { wishFormDefaults } from '@/support/wishFormDefaults';

const props = defineProps({
	title: { type: String, default: 'Jetzt Herzenswunsch einreichen' },
	// Set by the server outside production — see the block template.
	prefill: { type: Boolean, default: false },
});

// One flat object — mirrors the payload SubmitWishRequest validates, so a
// server-side error key maps straight onto a field.
const form = reactive({
	photo: null,
	wish: '',
	link: '',
	firstname: '',
	lastname: '',
	street: '',
	city: '',
	email: '',
	phone: '',
	birthdate: '',
	accepts_terms: false,
	accepts_publication: false,
});

// Everything but the photo, which a browser will not let us set.
if (props.prefill) {
	Object.assign(form, wishFormDefaults());
}

const root = ref(null);
const errors = ref({});
const sending = ref(false);
const done = ref(false);
const failed = ref(false);

function csrf() {
	return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

// A field's message goes as soon as the user comes back to it. focusin, not
// focus, so one listener on the form covers every field.
function clearError(event) {
	delete errors.value[event.target.id];
}

async function submit() {
	if (sending.value) {
		return;
	}

	sending.value = true;
	failed.value = false;
	errors.value = {};

	const payload = new FormData();

	Object.entries(form).forEach(([key, value]) => {
		if (value === null || value === '') {
			return;
		}

		// Laravel's `accepted` rule wants a truthy scalar, not "true"/"false".
		payload.append(key, typeof value === 'boolean' ? (value ? '1' : '0') : value);
	});

	try {
		const response = await fetch('/api/wishes', {
			method: 'POST',
			headers: {
				'X-CSRF-TOKEN': csrf(),
				'X-Requested-With': 'XMLHttpRequest',
				Accept: 'application/json',
			},
			body: payload,
		});

		if (response.status === 422) {
			const body = await response.json();
			errors.value = Object.fromEntries(
				Object.entries(body.errors ?? {}).map(([key, messages]) => [key, messages[0]]),
			);

			// Put the first offending field in view — the form is long enough that
			// an error above the fold would otherwise go unnoticed. The messages
			// render on the next tick, and photo has no field of its own to reach.
			await nextTick();

			root.value?.querySelector('[role="alert"]')?.scrollIntoView({
				behavior: 'smooth',
				block: 'center',
			});

			return;
		}

		if (! response.ok) {
			failed.value = true;

			return;
		}

		done.value = true;
	} catch {
		failed.value = true;
	} finally {
		sending.value = false;
	}
}
</script>

<template>
	<section ref="root">
		<h2 class="text-balance font-bold leading-[1.1] text-lg md:text-2xl lg:text-4xl mb-32 md:mb-48 lg:mb-64">
			{{ title }}
		</h2>

		<template v-if="done">
			<div role="status" class="border border-brand p-20 md:p-32">
				<div class="font-bold mb-16 md:mb-20 lg:mb-24">
					Vielen Dank für Ihren Herzenswunsch!
				</div>
				<div>
					Wir haben Ihre Einreichung erhalten und melden uns bei Ihnen.
				</div>
			</div>
		</template>

		<template v-else>
			<form novalidate class="flex flex-col gap-24 md:gap-40 lg:gap-56" @focusin="clearError" @submit.prevent="submit">

				<fieldset aria-labelledby="photo-heading">
					<PhotoUpload
						v-model="form.photo"
						label="Ihr Foto auf der Wunschbank"
						required
						:error="errors.photo" />
				</fieldset>

				<fieldset>
					<FormLegend id="wish-legend" required>
						Was ist Ihr Herzenswunsch?
					</FormLegend>
					<FormTextarea
						id="wish"
						labelledby="wish-legend"
						v-model="form.wish"
						:rows="5"
						:error="errors.wish"
						placeholder="Beschreiben Sie hier Ihren Wunsch möglichst konkret. Teilen Sie uns mit, ob der Wunsch für Sie selbst, für eine andere Person oder für einen Verein bzw. eine Organisation bestimmt ist. Erzählen Sie uns, weshalb Ihnen dieser Wunsch am Herzen liegt und weshalb gerade er erfüllt werden sollte." />
				</fieldset>

				<fieldset>
					<FormLegend id="link-legend">
						Möchten Sie Ihren Wunsch mit einem Link ergänzen?
					</FormLegend>
					<FormTextarea
						id="link"
						labelledby="link-legend"
						v-model="form.link"
						:rows="2"
						:error="errors.link"
						placeholder="Falls es zu Ihrem Wunsch weitere Informationen gibt, können Sie den entsprechenden Link hier angeben." />
				</fieldset>

				<fieldset>
					<FormLegend required>
						Ihre Kontaktdaten
					</FormLegend>

					<div class="mb-16 md:mb-20 lg:mb-24">
            Hinweis: Herzenswünsche können von Personen ab 18 Jahren eingereicht werden. Wünsche für Kinder und Jugendliche sind selbstverständlich willkommen.
					</div>

					<div class="grid grid-cols-1 md:grid-cols-2 gap-x-16 lg:gap-x-20 gap-y-20 md:gap-y-24">
						<div>
							<FormLabel for="firstname">
								Vorname
							</FormLabel>
							<FormInput id="firstname" v-model="form.firstname" :error="errors.firstname" autocomplete="given-name" />
						</div>
						<div>
							<FormLabel for="lastname">
								Nachname
							</FormLabel>
							<FormInput id="lastname" v-model="form.lastname" :error="errors.lastname" autocomplete="family-name" />
						</div>
						<div>
							<FormLabel for="street">
								Strasse, Nr.
							</FormLabel>
							<FormInput id="street" v-model="form.street" :error="errors.street" autocomplete="street-address" />
						</div>
						<div>
							<FormLabel for="city">
								PLZ, Ort
							</FormLabel>
							<FormInput id="city" v-model="form.city" :error="errors.city" autocomplete="postal-code" />
						</div>
						<div>
							<FormLabel for="email">
								E-Mail-Adresse
							</FormLabel>
							<FormInput id="email" v-model="form.email" type="email" :error="errors.email" autocomplete="email" />
						</div>
						<div>
							<FormLabel for="phone">
								Telefonnummer
							</FormLabel>
							<FormInput id="phone" v-model="form.phone" type="tel" :error="errors.phone" autocomplete="tel" />
						</div>
						<div>
							<FormLabel for="birthdate">
								Geburtsdatum
							</FormLabel>
							<FormInput id="birthdate" v-model="form.birthdate" type="date" :error="errors.birthdate" autocomplete="bday" />
						</div>
					</div>
				</fieldset>

				<fieldset class="flex flex-col gap-20 md:gap-24 lg:gap-28">
					<FormCheckbox id="accepts_terms" v-model="form.accepts_terms" :error="errors.accepts_terms">
						Ich habe die Teilnahmebedingungen gelesen und akzeptiere sie.
					</FormCheckbox>
					<FormCheckbox id="accepts_publication" v-model="form.accepts_publication" :error="errors.accepts_publication">
						Ich bin damit einverstanden, dass im Falle einer Wunscherfüllung mein Vor- und Nachname,
						mein Foto sowie Informationen zu meinem Herzenswunsch veröffentlicht werden
						(Website, Social Media).
					</FormCheckbox>
				</fieldset>

				<fieldset class="flex flex-col-reverse md:flex-row md:items-center md:justify-between gap-16">
					<Button
						type="submit"
						:disabled="sending"
						:aria-busy="sending ? 'true' : null"
						class="self-start">
						{{ sending ? 'Wird gesendet …' : 'Absenden' }}
					</Button>
					<div class="text-xxs">
						* Pflichtfelder
					</div>
				</fieldset>

				<template v-if="failed">
					<div role="alert" class="font-bold text-brand text-[16px] md:text-[18px] lg:text-[20px]">
						Das hat leider nicht geklappt. Bitte versuchen Sie es später noch einmal.
					</div>
				</template>
			</form>
		</template>
	</section>
</template>
