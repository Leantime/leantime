import jQuery from 'jquery';
import { appUrl } from 'js/app/core/instance-info.module';
import { updateUserMenuSettings } from './menuRepository';

export const toggleSubmenu = function (submenuName, submenuDisplay) {
    if (submenuName === "") {
        return;
    }

    const submenuState = (submenuDisplay === 'none' || submenuDisplay === '' || submenuDisplay === 'closed') ? 'open' : 'closed';

    jQuery.ajax({
        type: 'PATCH',
        url: appUrl + '/api/submenu',
        data: {
            submenu: submenuName,
            state: submenuState
        }
    });
}

export const initProjectSelector = function () {

    jQuery(document).on('click', '.projectselector.dropdown-menu', function (e) {
        e.stopPropagation();
    });

    let currentTab = localStorage.getItem("currentMenuTab");

    let activeTabIndex;

    if (typeof currentTab === 'undefined') {
        activeTabIndex = 0;
    } else {
        activeTabIndex = jQuery('.projectSelectorTabs').find('a[href="#' + currentTab + '"]').parent().index();
    }

    jQuery('.projectSelectorTabs').tabs({
        create: function ( event, ui ) {

        },
        activate: function (event, ui) {
            localStorage.setItem("currentMenuTab", ui.newPanel[0].id);
        },
        load: function () {

        },
        enable: function () {

        },
        active: activeTabIndex

    });

};

export const initLeftMenuHamburgerButton = function () {
    var newWidth = 68;
    if (window.innerWidth < 576) {
        jQuery(".mainwrapper").removeClass("menuopen");
        jQuery(".mainwrapper").addClass("menuclosed");
    }

    jQuery('.barmenu').click(function () {

        if (jQuery(".mainwrapper").hasClass('menuopen')) {
            jQuery(".mainwrapper").removeClass("menuopen");
            jQuery(".mainwrapper").addClass("menuclosed");

            //If it doesn't have the class open, the user wants it to be open.
            updateUserMenuSettings("closed");
        } else {
            jQuery(".mainwrapper").removeClass("menuclosed");
            jQuery(".mainwrapper").addClass("menuopen");

            //If it doesn't have the class open, the user wants it to be open.
            updateUserMenuSettings("open");
        }

    });
};

export const toggleProjectDropDownList = function (id, set="", prefix) {
    //toggler-ID (link to click on open/close)
    //dropdown-ID (dropdown to open/close)

    //Part 1 allow devs to set open/closed state.
    //This means we need to do the opposite of what the current state is.
    if (set === "closed") {
        jQuery("#" + prefix + "-toggler-" + id).removeClass("closed");
        jQuery("#" + prefix + "-toggler-" + id).removeClass("open");
        jQuery("#" + prefix + "-toggler-" + id).addClass("open");
    } else if (set === "open") {
        jQuery("#" + prefix + "-toggler-" + id).removeClass("open");
        jQuery("#" + prefix + "-toggler-" + id).removeClass("closed");
        jQuery("#" + prefix + "-toggler-" + id).addClass("closed");
    }

    //Part 2
    //Do the toggle. If the link has the class open, we need to close it.
    if (jQuery("#" + prefix + "-toggler-" + id).hasClass("open")) {
        //Update class on link
        jQuery("#" + prefix + "-toggler-" + id).removeClass("open");
        jQuery("#" + prefix + "-toggler-" + id).addClass("closed");

        //Update icon on link
        jQuery("#" + prefix + "-toggler-" + id).find("i").removeClass("fa-angle-down");
        jQuery("#" + prefix + "-toggler-" + id).find("i").addClass("fa-angle-right");


        jQuery("#" + prefix + "-projectSelectorlist-group-" + id).addClass("closed");
        jQuery("#" + prefix + "-projectSelectorlist-group-" + id).removeClass("open");

        updateGroupDropdownSetting(id, "closed", prefix);
    } else {
        //Update class on link
        jQuery("#" + prefix + "-toggler-" + id).removeClass("closed");
        jQuery("#" + prefix + "-toggler-" + id).addClass("open");

        //Update icon on link
        jQuery("#" + prefix + "-toggler-" + id).find("i").removeClass("fa-angle-right");
        jQuery("#" + prefix + "-toggler-" + id).find("i").addClass("fa-angle-down");


        jQuery("#" + prefix + "-projectSelectorlist-group-" + id).addClass("open");
        jQuery("#" + prefix + "-projectSelectorlist-group-" + id).removeClass("closed");

        updateGroupDropdownSetting(id, "open", prefix);
    }
};

export const updateGroupDropdownSetting = function (ID, state, prefix) {
    jQuery.ajax({
        type : 'PATCH',
        url  : appUrl + '/api/submenu',
        data : {
            submenu : prefix + "-projectSelectorlist-group-" + ID,
            state   : state
        }
    });
};

// Make public what you want to have public, everything else is private
export const menuController = {
    toggleSubmenu: toggleSubmenu,
    initProjectSelector: initProjectSelector,
    initLeftMenuHamburgerButton: initLeftMenuHamburgerButton,
    updateGroupDropdownSetting: updateGroupDropdownSetting,
    toggleProjectDropDownList: toggleProjectDropDownList
};
