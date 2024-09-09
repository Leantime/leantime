import jQuery from 'jquery';
import i18n from 'i18n';
import { appUrl } from 'js/app/core/instance-info.module';

// To be set
let canvasName = 'obm';

// To be implemented
export const setRowHeights = function () {
    var nbRows = 3;
    var rowHeight = jQuery("html").height() - 320 - 20 * nbRows;

    var firstRowHeight = rowHeight * 0.6666;
    jQuery("#firstRow div.contentInner").each(function () {
        if (jQuery(this).height() > firstRowHeight) {
            firstRowHeight = jQuery(this).height() + 50;
        }
    });

    var firstRowHeightTop = firstRowHeight * 0.5;
    jQuery("#firstRowTop div.contentInner").each(function () {
        if (jQuery(this).height() > firstRowHeightTop) {
            firstRowHeightTop = jQuery(this).height() + 50;
        }
    });
    var firstRowHeightBottom = firstRowHeight * 0.5;
    jQuery("#firstRowBottom div.contentInner").each(function () {
        if (jQuery(this).height() > firstRowHeightBottom) {
            firstRowHeightBottom = jQuery(this).height() + 50;
        }
    });
    if (firstRowHeightTop + firstRowHeightBottom + 25 > firstRowHeight) {
        firstRowHeight = firstRowHeightTop + firstRowHeightBottom + 50;
    }

    var secondRowHeight = rowHeight * 0.333;
    jQuery("#secondRow div.contentInner").each(function () {
        if (jQuery(this).height() > secondRowHeight) {
            secondRowHeight = jQuery(this).height() + 50;
        }
    });

    jQuery("#firstRow .column .contentInner").css("height", firstRowHeight);
    jQuery("#firstRowTop div.contentInner").css("height", firstRowHeightTop);
    jQuery("#firstRowBottom div.contentInner").css("height", firstRowHeightBottom);
    jQuery("#secondRow .column .contentInner").css("height", secondRowHeight);
};

// Make public what you want to have public, everything else is private
export default {
    setRowHeights: setRowHeights
};
