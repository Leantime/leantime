import i18n from 'i18n';
import { appUrl } from './instance-info.module';

var copyUrl = function (field) {
    // Get the text field
    var copyText = document.getElementById(field);

    // Select the text field
    copyText.select();
    copyText.setSelectionRange(0, 99999); // For mobile devices

    // Copy the text inside the text field
    navigator.clipboard.writeText(copyText.value);

    // Alert the copied text
    jQuery.growl({message: i18n.__("short_notifications.url_copied"), style: "success"});
};

var copyToClipboard = function (content) {
    navigator.clipboard.writeText(content);

    // Alert the copied text
    jQuery.growl({message: i18n.__("short_notifications.url_copied"), style: "success"});
};

var initConfettiClick = function() {
    jQuery(".confetti").click(function(){
        confetti.start();
    });
};

var accordionToggle = function (id) {
    var currentLink = jQuery("#accordion_toggle_"+id).find("i.fa").first();
    var submenuName = 'accordion_content-'+id;
    var submenuState = "closed";

    if (currentLink.hasClass("fa-angle-right")){
        currentLink.removeClass("fa-angle-right");
        currentLink.addClass("fa-angle-down");
        jQuery('#accordion_content-'+id).slideDown("fast");
        submenuState = "open";
    } else {
        currentLink.removeClass("fa-angle-down");
        currentLink.addClass("fa-angle-right");

        jQuery('#accordion_content-'+id).slideUp("fast");
        submenuState = "closed";
    }

    jQuery.ajax({
        type: 'PATCH',
        url: appUrl + '/api/submenu',
        data: {
            submenu: submenuName,
            state: submenuState
        }
    });
};

var toggleTheme = function (theme) {
    var themeUrl = jQuery("#themeStyleSheet").attr("href");

    if(theme == "light"){
        themeUrl = themeUrl.replace("dark.css", "light.css");
        jQuery("#themeStyleSheet").attr("href", themeUrl);
    }else if (theme == "dark"){
        themeUrl = themeUrl.replace("light.css", "dark.css");
        jQuery("#themeStyleSheet").attr("href", themeUrl);
    }
};

var toggleFont = function (font) {
    jQuery("#fontStyleSetter").html(":root { --primary-font-family: '"+font+"', 'Helvetica Neue', Helvetica, sans-serif; }")
};

var toggleColors = function (accent1, accent2) {
    jQuery("#colorSchemeSetter").html(":root { --accent1: "+accent1+"; --accent2: "+accent2+"}")
};

// Make public what you want to have public, everything else is private
export default {
    copyUrl: copyUrl,
    copyToClipboard: copyToClipboard,
    initConfettiClick: initConfettiClick,
    accordionToggle: accordionToggle,
    toggleTheme: toggleTheme,
    toggleFont: toggleFont,
    toggleColors: toggleColors
};
