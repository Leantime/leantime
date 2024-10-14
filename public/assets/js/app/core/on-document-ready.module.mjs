import jQuery from 'jquery';
import tippy from 'tippy.js';
import confetti from 'canvas-confetti';

export default function () {
    window.leantime.replaceSVGColors();

    jQuery(".confetti").click(confetti.start);

    tippy('[data-tippy-content]');

    if (jQuery('.login-alert .alert').text() !== '') {
        jQuery('.login-alert').fadeIn();
    }

    document.addEventListener('scroll', () => {
        document.documentElement.dataset.scroll = window.scrollY;
        if(window.scrollY > 25) {
            jQuery("body").addClass("scrolled");
        }

        if(window.scrollY <= 25) {
            jQuery("body").removeClass("scrolled");
        }
    });
};
