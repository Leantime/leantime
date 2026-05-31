leantime.menuController = (function () {


    //Functions

    var toggleSubmenu = function (submenuName) {

        if (submenuName === "") {
            return;
        }

        var submenuDisplay = jQuery('#submenu-' + submenuName).css('display');
        var submenuState = '';

        if (submenuDisplay == 'none') {
            jQuery('#submenu-' + submenuName).css('display', 'block');
            jQuery('#submenu-icon-' + submenuName).removeClass('fa-angle-right');
            jQuery('#submenu-icon-' + submenuName).addClass('fa-angle-down');
            submenuState = 'open';
        } else {
            jQuery('#submenu-' + submenuName).css('display', 'none');
            jQuery('#submenu-icon-' + submenuName).removeClass('fa-angle-down');
            jQuery('#submenu-icon-' + submenuName).addClass('fa-angle-right');
            submenuState = 'closed';
        }

        leantime.rpc('Api.Api.setSubmenuState', {
            submenu: submenuName,
            state: submenuState
        }).catch(function (e) { console.error('Could not update submenu state', e); });
    }

    var initProjectSelector = function () {

        jQuery(".project-select").chosen();

        jQuery(document).on('click', '.projectselector.dropdown-menu', function (e) {
            e.stopPropagation();
        });

        let currentTab = localStorage.getItem("currentMenuTab");

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

    // Below this width the sidebar is an off-canvas drawer (matches the
    // mobile-like CSS breakpoint in mobile.css). See #3185, #2878.
    var isMobileMenu = function () {
        return window.innerWidth < 1200;
    };

    var initLeftMenuHamburgerButton = function () {

        // On mobile/tablet always start with the drawer closed, regardless of
        // the saved desktop preference. Don't persist this — it's view-only.
        if (isMobileMenu()) {
            jQuery(".mainwrapper").removeClass("menuopen");
            jQuery(".mainwrapper").addClass("menuclosed");
        }

        jQuery('.barmenu').click(function (e) {

            // Don't let this click bubble to the close-on-outside handler below,
            // otherwise opening the drawer immediately closes it again.
            e.stopPropagation();

            // On mobile/tablet the drawer is view-only: toggle it but do NOT
            // persist, otherwise opening the drawer on a phone would overwrite
            // the user's saved desktop sidebar preference.
            var persistState = !isMobileMenu();

            if (jQuery(".mainwrapper").hasClass('menuopen')) {
                jQuery(".mainwrapper").removeClass("menuopen");
                jQuery(".mainwrapper").addClass("menuclosed");

                //If it doesn't have the class open, the user wants it to be open.
                if (persistState) {
                    leantime.menuRepository.updateUserMenuSettings("closed");
                }
            } else {
                jQuery(".mainwrapper").removeClass("menuclosed");
                jQuery(".mainwrapper").addClass("menuopen");

                //If it doesn't have the class open, the user wants it to be open.
                if (persistState) {
                    leantime.menuRepository.updateUserMenuSettings("open");
                }
            }

        });

        // Mobile drawer: tapping the dimmed backdrop closes it. The backdrop is
        // a real element covering the content, so this is reliable even when
        // content widgets stopPropagation on their own clicks. View-only.
        jQuery(".menu-backdrop").on('click', function () {
            jQuery(".mainwrapper").removeClass("menuopen").addClass("menuclosed");
        });

        // When the viewport crosses from desktop into mobile width, collapse
        // the drawer so it doesn't sit open over the content.
        var wasMobile = isMobileMenu();
        jQuery(window).on('resize', function () {
            var nowMobile = isMobileMenu();
            if (nowMobile && !wasMobile) {
                jQuery(".mainwrapper").removeClass("menuopen").addClass("menuclosed");
            }
            wasMobile = nowMobile;
        });

    };


    var toggleProjectDropDownList = function (id, set="", prefix) {

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

    let updateGroupDropdownSetting = function (ID, state, prefix) {

        leantime.rpc('Api.Api.setSubmenuState', {
            submenu: prefix + "-projectSelectorlist-group-" + ID,
            state: state
        }).catch(function (e) { console.error('Could not update submenu state', e); });

    };

    // Make public what you want to have public, everything else is private
    return {
        toggleSubmenu:toggleSubmenu,
        initProjectSelector:initProjectSelector,
        initLeftMenuHamburgerButton:initLeftMenuHamburgerButton,
        updateGroupDropdownSetting: updateGroupDropdownSetting,
        toggleProjectDropDownList:toggleProjectDropDownList
    };

})();
