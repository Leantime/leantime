import jQuery from 'jquery';
import i18n from 'i18n';
import { appUrl } from 'js/app/core/instance-info.module';

// To be set
let canvasName = 'dbm';

export const setRowHeights = function () {

    var nbRows = 3;
    var rowHeight = jQuery("html").height() - 320 - 20 * nbRows;

    var firstRowHeight = rowHeight * 0.375;
    jQuery("#firstRow div.contentInner").each(function () {
        if (jQuery(this).height() > firstRowHeight) {
            firstRowHeight = jQuery(this).height() + 50;
        }
    });
    var secondRowHeight = rowHeight * 0.375;
    jQuery("#secondRow div.contentInner").each(function () {
        if (jQuery(this).height() > secondRowHeight) {
            secondRowHeight = jQuery(this).height() + 50;
        }
    });
    var secondRowHeightTop = secondRowHeight * 0.5;
    jQuery("#secondRowTop div.contentInner").each(function () {
        if (jQuery(this).height() > secondRowHeightTop) {
            secondRowHeightTop = jQuery(this).height() + 50;
        }
    });
    var secondRowHeightBottom = secondRowHeight * 0.5;
    jQuery("#secondRowBottom div.contentInner").each(function () {
        if (jQuery(this).height() > secondRowHeightBottom) {
            secondRowHeightBottom = jQuery(this).height() + 50;
        }
    });
    if (secondRowHeightTop + secondRowHeightBottom + 25 > secondRowHeight) {
        secondRowHeight = secondRowHeightTop + secondRowHeightBottom + 50;
    }
    var thirdRowHeight = rowHeight * 0.25;
    jQuery("#thirdRow div.contentInner").each(function () {
        if (jQuery(this).height() > thirdRowHeight) {
            thirdRowHeight = jQuery(this).height() + 50;
        }
    });

    jQuery("#firstRow .column .contentInner").css("height", firstRowHeight);
    jQuery("#secondRow .column .contentInner").css("height", secondRowHeight);
    jQuery("#secondRowTop .column .contentInner").css("height", secondRowHeightTop);
    jQuery("#secondRowBottom .column .contentInner").css("height", secondRowHeightBottom);
    jQuery("#thirdRow .column .contentInner").css("height", thirdRowHeight);

};

// Make public what you want to have public, everything else is private
export default {
    setRowHeights: setRowHeights
};
