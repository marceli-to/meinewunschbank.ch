const STEP = 80;
const MAX_STEPS = 6;

function reveal(entries, observer) {
	entries
		.filter((entry) => entry.isIntersecting)
		// Entries arrive in an arbitrary order, so a row of cards would stagger
		// out of sequence without sorting them back into document order.
		.sort((a, b) =>
			a.target.compareDocumentPosition(b.target) & Node.DOCUMENT_POSITION_FOLLOWING ? -1 : 1
		)
		.forEach((entry, index) => {
			entry.target.style.setProperty('--reveal-delay', `${Math.min(index, MAX_STEPS) * STEP}ms`);
			entry.target.classList.add('is-revealed');
			observer.unobserve(entry.target);
		});
}

// Elements opt in with .reveal, or with .reveal-group on a parent whose direct
// children should come in one after the other. The stagger counts position
// within the batch rather than within the group, so a grid arriving at once
// steps out while a tall list arriving item by item does not collect a growing
// delay. Revealing unobserves, so nothing plays twice on the way back up.
export default function initReveals() {
	const targets = document.querySelectorAll('.reveal, .reveal-group > *');

	if (!targets.length) {
		return;
	}

	const observer = new IntersectionObserver(reveal, {
		rootMargin: '0px 0px -20% 0px',
	});

	targets.forEach((target) => observer.observe(target));
}
