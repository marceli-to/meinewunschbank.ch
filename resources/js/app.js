import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import { createApp } from 'vue';
import initSliders from '@/behaviors/slider';
import initReveals from '@/behaviors/reveal';
import WishForm from '@/components/WishForm.vue';

// Alpine handles lightweight UI state (menu toggles, transitions).
window.Alpine = Alpine;
Alpine.plugin(collapse);
Alpine.start();

initSliders();
initReveals();

// Vue island — the wish form, mounted wherever a page places the block.
const el = document.getElementById('wish-form');

if (el) {
    createApp(WishForm, {
        title: el.dataset.title ?? '',
        sitekey: el.dataset.sitekey ?? '',
    }).mount(el);
}

// Loaded only where a milestones block switches the merger tree on.
const tree = document.querySelector('[data-merger-tree]');

if (tree) {
    import('@/components/MergerTree.vue').then(({ default: MergerTree }) => {
        createApp(MergerTree, { panelId: tree.id }).mount(tree);
    });
}
