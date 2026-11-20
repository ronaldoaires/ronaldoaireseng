// Swiper carousels: any element with [data-carousel] becomes one. Loaded on
// demand from app.js, so pages without a carousel never download Swiper.
import Swiper from 'swiper';
import { A11y, Autoplay, Keyboard, Navigation } from 'swiper/modules';
import 'swiper/css';

export function mountCarousels(roots) {
    roots.forEach((root) => {
        if (root.hasAttribute('data-carousel-shuffle')) {
            const wrapper = root.querySelector('.swiper-wrapper');

            if (wrapper) {
                const slides = Array.from(wrapper.children);

                for (let index = slides.length - 1; index > 0; index--) {
                    const swapIndex = Math.floor(Math.random() * (index + 1));
                    [slides[index], slides[swapIndex]] = [slides[swapIndex], slides[index]];
                }

                wrapper.replaceChildren(...slides);
            }
        }

        const scope = root.closest('[data-carousel-scope]') ?? root.parentElement;
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const autoplay = root.hasAttribute('data-carousel-autoplay') && ! reduceMotion;
        const finite = root.hasAttribute('data-carousel-finite');

        new Swiper(root, {
            modules: [A11y, Autoplay, Keyboard, Navigation],
            slidesPerView: 'auto',
            spaceBetween: 16,
            loop: ! finite,
            speed: finite ? 450 : 900,
            // Slow, steady advance; pauses while the pointer is over a card.
            autoplay: autoplay ? { delay: 3500, disableOnInteraction: false, pauseOnMouseEnter: true } : false,
            grabCursor: true,
            keyboard: { enabled: true, onlyInViewport: true },
            navigation: {
                prevEl: scope.querySelector('[data-carousel-prev]'),
                nextEl: scope.querySelector('[data-carousel-next]'),
                disabledClass: 'is-disabled',
            },
            a11y: {
                prevSlideMessage: 'Anterior',
                nextSlideMessage: 'Próximo',
            },
            breakpoints: {
                640: { spaceBetween: 24 },
            },
        });
    });
}
