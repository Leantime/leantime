import { appUrl } from "../core/instance-info.module.mjs";
import jQuery from 'jquery';


export const initSelectable = function(element, config, callback) {

    const selectableDropdowns = element;
    const selectablebutton = element;


    let items = element.closest('.data-selectable-items').find("li");

    items.each(function() {

        jQuery(this).on('click', function (e) {

            e.preventDefault();

            // const $selectedItem = $(this);
            // const $li = $selectedItem.is('li') ? $selectedItem : $selectedItem.closest(
            //     'li');

            // Get text from the clicked element
            const selectedText = jQuery(this).text().trim();

            // Update button text
            selectablebutton.text(selectedText);

            // Check for style on both the clicked element and its li parent
            const style = jQuery(this).attr('style') ||
                jQuery(this).attr('data-style');

            const indicatorClass = jQuery(this).attr('data-class') || jQuery(this).attr(
                'data-class');

            if (style) {
                selectablebutton.attr('style', style + ';');
            }

            // if (indicatorClass) {
            //     // Remove all CSS classes from the button element before adding new ones
            //     element.removeClass();
            //     element.addClass(indicatorClass + ' hover:bg-ghost');
            // }
        });
    });
        // $selectableDropdowns.each(function() {
        //     const $button = $(this);
        //     const $dropdown = $button.closest('.dropdown');
        //     const $items = $dropdown.find('[data-selectable-item] li, [data-selectable-item] li a');
        //
        //
        //
        // });
}

    // Make public what you want to have public, everything else is private
export const dropdowns = {
    initSelectable:initSelectable
};

export default dropdowns;
