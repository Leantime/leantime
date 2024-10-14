import jQuery from 'jquery';
import i18n from 'i18n';
import { appUrl } from 'js/app/core/instance-info.module';

// To be set
let canvasName = 'em';

// To be implemented
export const setRowHeights = function () {

    var nbRows = 4;
    var rowHeight = jQuery("html").height() - 320 - 20 * nbRows - 3 * 50;

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

    var fourthRowHeight = rowHeight / nbRows;
    jQuery("#fourthRow div.contentInner").each(function () {
        if (jQuery(this).height() > fourthRowHeight) {
            fourthRowHeight = jQuery(this).height() + 50;
        }
    });
    jQuery("#fourthRow .column .contentInner").css("height", fourthRowHeight);

};

export default {
    setRowHeights: setRowHeights
};
