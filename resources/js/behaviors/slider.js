import Swiper from 'swiper';
import { Navigation } from 'swiper/modules';
import 'swiper/css';

// One Swiper per data-swiper-scope, so a page can hold several and each still
// finds its own buttons rather than the first pair on the page.
export default function initSliders() {
    document.querySelectorAll('[data-swiper-scope]').forEach((scope) => {
        const container = scope.querySelector('[data-swiper]');

        if (!container) {
            return;
        }

        new Swiper(container, {
            modules: [Navigation],
            slidesPerView: 1,
            spaceBetween: 24,
            breakpoints: {
                768: { slidesPerView: 2, spaceBetween: 24 },
                1024: { slidesPerView: 3, spaceBetween: 32 },
            },
            navigation: {
                prevEl: scope.querySelector('[data-swiper-prev]'),
                nextEl: scope.querySelector('[data-swiper-next]'),
            },
        });
    });
}
