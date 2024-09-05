import jQuery from 'jquery';
import i18n from 'i18n';
import { appUrl } from 'js/app/core/instance-info.module';

let canvasName = '';

export const setCanvasName = function (name) {
    canvasName = name;
};

export const initFilterBar = function () {

    jQuery(window).bind("load", function () {
        jQuery(".loading").fadeOut();
        jQuery(".filterBar .row-fluid").css("opacity", "1");


    });

};

export const initUserDropdown = function () {

    jQuery("body").on(
        "click",
        ".userDropdown .dropdown-menu a",
        function () {

            var dataValue = jQuery(this).attr("data-value").split("_");
            var dataLabel = jQuery(this).attr('data-label');

            if (dataValue.length == 3) {
                var canvasId = dataValue[0];
                var userId = dataValue[1];
                var profileImageId = dataValue[2];

                jQuery.ajax(
                    {
                        type: 'PATCH',
                        url: appUrl + '/api/' + canvasName + 'canvas',
                        data:
                            {
                                id : canvasId,
                                author:userId
                        }
                    }
                ).done(
                    function () {
                        jQuery("#userDropdownMenuLink" + canvasId + " span.text span#userImage" + canvasId + " img").attr("src", appUrl + "/api/users?profileImage=" + userId);
                        jQuery.growl({message: i18n.__("short_notifications.user_updated"), style: "success"});
                    }
                );
            }
        }
    );
};

export const initStatusDropdown = function () {

    jQuery("body").on(
        "click",
        ".statusDropdown .dropdown-menu a",
        function () {

            var dataValue = jQuery(this).attr("data-value").split("/");
            var dataLabel = jQuery(this).attr('data-label');

            if (dataValue.length == 2) {
                var canvasItemId = dataValue[0];
                var status = dataValue[1];
                var statusClass = jQuery(this).attr('class');


                jQuery.ajax(
                    {
                        type: 'PATCH',
                        url: appUrl + '/api/' + canvasName + 'canvas',
                        data:
                            {
                                id : canvasItemId,
                                status: status
                        }
                    }
                ).done(
                    function () {
                        jQuery("#statusDropdownMenuLink" + canvasItemId + " span.text").text(dataLabel);
                        jQuery("#statusDropdownMenuLink" + canvasItemId).removeClass().addClass(statusClass + " dropdown-toggle f-left status ");
                        jQuery.growl({message: i18n.__("short_notifications.status_updated")});

                    }
                );
            }
        }
    );

};

export const initRelatesDropdown = function () {

    jQuery("body").on(
        "click",
        ".relatesDropdown .dropdown-menu a",
        function () {

            var dataValue = jQuery(this).attr("data-value").split("/");
            var dataLabel = jQuery(this).attr('data-label');

            if (dataValue.length == 2) {
                var canvasItemId = dataValue[0];
                var relates = dataValue[1];
                var relatesClass = jQuery(this).attr('class');


                jQuery.ajax(
                    {
                        type: 'PATCH',
                        url: appUrl + '/api/' + canvasName + 'canvas',
                        data:
                            {
                                id : canvasItemId,
                                relates: relates
                        }
                    }
                ).done(
                    function () {
                        jQuery("#relatesDropdownMenuLink" + canvasItemId + " span.text").text(dataLabel);
                        jQuery("#relatesDropdownMenuLink" + canvasItemId).removeClass().addClass(relatesClass + " dropdown-toggle f-left relates ");
                        jQuery.growl({message: i18n.__("short_notifications.relates_updated")});

                    }
                );
            }
        }
    );

};

// Make public what you want to have public, everything else is private
export default {
    setCanvasName: setCanvasName,
    initFilterBar: initFilterBar,
    initUserDropdown: initUserDropdown,
    initStatusDropdown: initStatusDropdown,
    initRelatesDropdown: initRelatesDropdown,
};
