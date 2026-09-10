import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import { createApp } from 'vue';
import initSliders from './behaviors/slider';
import WishForm from './components/WishForm.vue';

// Alpine handles lightweight UI state (menu toggles, transitions).
window.Alpine = Alpine;
Alpine.plugin(collapse);
Alpine.start();

initSliders();

// Vue island — the wish form, mounted wherever a page places the block.
const el = document.getElementById('wish-form');

if (el) {
    createApp(WishForm, {
        title: el.dataset.title ?? '',
        prefill: el.dataset.prefill === 'true',
    }).mount(el);
}
