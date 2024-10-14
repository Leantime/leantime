import { companyColor } from './instance-info.module.mjs';
import jQuery from 'jquery';

const replaceSVGColors = function () {
    jQuery(document).ready(function () {
        if (companyColor == '#1b75bb') {
            return;
        }

        jQuery('svg').children().each(function () {
            if (jQuery(this).attr('fill') == '#1b75bb') {
                jQuery(this).attr('fill', companyColor);
            }
        });
    });
};

export default replaceSVGColors;
