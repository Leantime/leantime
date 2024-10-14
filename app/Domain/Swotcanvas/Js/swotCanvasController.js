import jQuery from 'jquery';
import i18n from 'i18n';

let canvasName = 'swot';

export const setRowHeights = function () {
    var nbRows = 2;
    var rowHeight = jQuery("html").height() - 320 - 20 * nbRows - 25;

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
};

export default {
    setRowHeights: setRowHeights
};
