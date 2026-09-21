import Swiper from 'swiper';
import { Navigation } from 'swiper/modules';
import 'swiper/css';

// Swiper's own default is a 300ms slide, short enough that an arrow click reads
// as a jump rather than a movement. The curve lives in partials/slider.css.
const SPEED = 900;

// One Swiper per data-swiper-scope, so a page can hold several and each still
// finds its own buttons rather than the first pair on the page. The scope's
// value picks the preset; an empty one falls back to teasers.
const presets = {
    teasers: {
        slidesPerView: 1,
        spaceBetween: 24,
        breakpoints: {
            768: { slidesPerView: 2, spaceBetween: 24 },
            1024: { slidesPerView: 3, spaceBetween: 32 },
        },
    },
    testimonials: {
        slidesPerView: 1,
        spaceBetween: 24,
        loop: true,
    },
    milestones: {
        slidesPerView: 1,
        spaceBetween: 24,
        breakpoints: {
            768: { slidesPerView: 2, spaceBetween: 96 },
            1024: { slidesPerView: 3, spaceBetween: 144 },
        },
    },
};

function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

export default function initSliders() {
    document.querySelectorAll('[data-swiper-scope]').forEach((scope) => {
        const container = scope.querySelector('[data-swiper]');

        if (!container) {
            return;
        }

        new Swiper(container, {
            modules: [Navigation],
            speed: prefersReducedMotion() ? 0 : SPEED,
            ...(presets[scope.dataset.swiperScope] ?? presets.teasers),
            navigation: {
                prevEl: scope.querySelector('[data-swiper-prev]'),
                nextEl: scope.querySelector('[data-swiper-next]'),
            },
        });
    });
}
