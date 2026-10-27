document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.siff-highlights-slider').forEach(function (slider) {
        // Avoid initializing the same slider more than once.
        if (slider.swiper) {
            return;
        }

        const slides = slider.querySelectorAll('.swiper-slide');

        new Swiper(slider, {
            slidesPerView: 1,
            loop: slides.length > 1,
            speed: 600,
            effect: 'slide',
            watchOverflow: true,

            navigation: {
                nextEl: slider.querySelector('.siff-highlight-next'),
                prevEl: slider.querySelector('.siff-highlight-prev')
            },

            pagination: {
                el: slider.querySelector('.siff-highlight-pagination'),
                clickable: true
            },

            keyboard: {
                enabled: true
            },

            a11y: {
                enabled: true
            }
        });
    });
});