leantime.menuController = (function () {

    //Variables

    //Constructor
    (function () {
        jQuery(document).ready(
            function () {
                _initProjectSelector();
                _initLeftMenuHamburgerButton();
                _initProjectSelectorToggle();
            }
        );

    })();

    //Functions

    var toggleSubmenu = function (submenuName) {

        if(submenuName === "") {
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

        jQuery.ajax({
            type : 'PATCH',
            url  : leantime.appUrl + '/api/submenu',
            data : {
                submenu : submenuName,
                state   : submenuState
            }
        });
    }

    var _initProjectSelector = function () {

        jQuery(".project-select").chosen();

    };

    var _initLeftMenuHamburgerButton = function () {


        if(window.innerWidth < 576) {
            jQuery('.barmenu').removeClass('open');
        }

        if (jQuery('.barmenu').hasClass('open')) {

            jQuery('.rightpanel').css({marginLeft: '240px'});
            jQuery('.header').animate({marginLeft: '240px'}, 'fast');
            jQuery('.header').animate({width:'calc(100% - 240px)'}, 'fast');
            jQuery('.logo, .leftpanel').css({marginLeft: 0});
            leantime.menuRepository.updateUserMenuSettings("open");
            jQuery('.logo').show();
            jQuery('.logo, #expandedMenu').css({display: 'block'});
            jQuery("#minimizedMenu").css({display: 'none'});

        } else {

            jQuery('.rightpanel').css({marginLeft: '68px'});
            jQuery('.header').animate({marginLeft: '68px'}, 'fast');
            jQuery('.header').animate({width:'calc(100% - 68px)'}, 'fast');
            jQuery('.logo, .leftpanel').css({marginLeft: '0'});
            jQuery('.logo').hide();
            jQuery('.logo, #expandedMenu').css({display: 'none'});
            jQuery("#minimizedMenu").css({display: 'block'});

            leantime.menuRepository.updateUserMenuSettings("closed");
        }

        jQuery('.barmenu').click(function () {

            var lwidth = '240px';

            if (!jQuery(this).hasClass('open')) {

                jQuery("#minimizedMenu").css({display: 'none'});
                jQuery('.logo, #expandedMenu').css({display: 'block'});

                jQuery('.header').animate({marginLeft: '240px'}, 'fast');
                jQuery('.header').css({width:'calc(100% - 240px)'});
                jQuery('.leftpanel').animate({width: "240px"}, 'fast');

                jQuery('.rightpanel').animate({marginLeft: '240px'}, 'fast', function () {
                    jQuery('.barmenu').addClass('open');
                });

                leantime.menuRepository.updateUserMenuSettings("open");

            } else {

                jQuery('.logo, #expandedMenu').css({display: 'none'});
                jQuery("#minimizedMenu").show();

                jQuery('.rightpanel').animate({marginLeft: '68px'}, 'fast', function () {
                    jQuery('.barmenu').removeClass('open');
                });

                jQuery('.header').animate({marginLeft: '68px'}, 'fast');
                jQuery('.header').css({width:'calc(100% - 68px)'});
                jQuery('.leftpanel').animate({width:'68px'}, 'fast');

                leantime.menuRepository.updateUserMenuSettings("closed");

            }

        });

    };

    var _initProjectSelectorToggle = function (id, element) {

        jQuery(document).on('click', '.project-selector .dropdown-menu', function (e) {
            e.stopPropagation();
        });

    };

    var toggleClientListHorizontal = function (id, element) {

        jQuery(".selectorList.projectList li").not(".nav-header, .fixedBottom").hide();

        jQuery(".client_" + id).show();
        jQuery(".client_" + id).show();

        jQuery(".selectorList.clientList li").removeClass("active");
        jQuery(element).addClass("active");


    };

    var toggleClientList = function (id, element, set="") {

        //MEthod is executed on click and does the oposite of the current state.
        //(eg when closed->open; when open->close)
        //To force a state we need to ensure it is the oposite of the state requested
        if(set === "closed") {
            jQuery(element).removeClass("closed");
            jQuery(element).removeClass("open");
            jQuery(element).addClass("open");
        }else if(set === "open"){
            jQuery(element).removeClass("open");
            jQuery(element).removeClass("closed");
            jQuery(element).addClass("closed");
        }

        if(jQuery(element).hasClass("open")){

            jQuery(".client_" + id).hide("fast");
            jQuery(element).removeClass("open");
            jQuery(element).addClass("closed");

            jQuery(element).find("i").removeClass("fa-angle-down");
            jQuery(element).find("i").addClass("fa-angle-right");

            updateClientDropdownSetting(id, "closed");

        }else{

            jQuery(".client_" + id).show("fast");
            jQuery(element).removeClass("closed");
            jQuery(element).addClass("open");

            jQuery(element).find("i").removeClass("fa-angle-right");
            jQuery(element).find("i").addClass("fa-angle-down");

            updateClientDropdownSetting(id, "open");

        }

    };

    let updateClientDropdownSetting = function(clientId, state) {

        jQuery.ajax({
            type : 'PATCH',
            url  : leantime.appUrl + '/api/submenu',
            data : {
                submenu : "clientDropdown-"+clientId,
                state   : state
            }
        });

    };

    // Make public what you want to have public, everything else is private
    return {
        toggleClientList:toggleClientList,
        toggleSubmenu:toggleSubmenu
    };

})();
