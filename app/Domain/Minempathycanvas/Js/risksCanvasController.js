import jQuery from 'jquery';
import i18n from 'i18n';
import { appUrl } from 'js/app/core/instance-info.module';

let canvasName = 'minempathy';

export const setRowHeights = function () {
    var nbRows = 3;
    var rowHeight = jQuery("html").height() - 320 - 20 * nbRows;

    var firstRowHeight = rowHeight / nbRows;
    jQuery("#firstRow div.contentInner").each(function () {
        if (jQuery(this).height() > firstRowHeight) {
            firstRowHeight = jQuery(this).height() + 50;
        }
    });
    jQuery("#firstRow .column .contentInner").css("height", firstRowHeight);

    var secondRowHeight = rowHeight / nbRows;
    jQuery("#secondRow div.contentInner").each(function () {
        if (jQuery(this).height() > secondRowHeight) {
            secondRowHeight = jQuery(this).height() + 50;
        }
    });

    jQuery("#secondRow .column .contentInner").css("height", secondRowHeight);

    var thirdRowHeight = rowHeight / nbRows;
    jQuery("#thirdRow div.contentInner").each(function () {
        if (jQuery(this).height() > thirdRowHeight) {
            thirdRowHeight = jQuery(this).height() + 50;
        }
    });

    jQuery("#thirdRow .column .contentInner").css("height", thirdRowHeight);
};

// Make public what you want to have public, everything else is private
export default {
    setRowHeights: setRowHeights
};
