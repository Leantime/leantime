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

    var _initProjectSelector = function () {

        jQuery(".project-select").chosen();

    };

    var _initLeftMenuHamburgerButton = function (){

        if (jQuery('.barmenu').hasClass('open')) {
            jQuery('.rightpanel').css({marginLeft: '240px'});
            jQuery('.header').css({marginLeft: '240px', width:'calc(100%-240px)'});
            jQuery('.logo, .leftpanel').css({marginLeft: 0});
            leantime.menuRepository.updateUserMenuSettings("open");
        } else {
            jQuery('.rightpanel, .header').css({marginLeft: 0});
            jQuery('.header').css({marginLeft: 0, width:'100%'});
            jQuery('.logo, .leftpanel').css({marginLeft: '-240px'});
            leantime.menuRepository.updateUserMenuSettings("closed");
        }

        jQuery('.barmenu').click(function () {

            var lwidth = '240px';


            if (!jQuery(this).hasClass('open')) {

                jQuery('.header').animate({marginLeft: '240px', width:'-=240px'}, 'fast');

                jQuery('.logo, .leftpanel').animate({marginLeft: 0}, 'fast');

                jQuery('.rightpanel').animate({marginLeft: '240px'}, 'fast', function(){
                    jQuery('.barmenu').addClass('open');
                });


                leantime.menuRepository.updateUserMenuSettings("open");
            } else {

                jQuery('.rightpanel').animate({marginLeft: 0}, 'fast', function() {
                    jQuery('.barmenu').removeClass('open');
                });

                jQuery('.header').animate({marginLeft: '0', width:'100%'}, 'fast');
                jQuery('.logo, .leftpanel').animate({marginLeft: '-' + '240px'}, 'fast');

                leantime.menuRepository.updateUserMenuSettings("closed");

            }
        });

    };

    var _initProjectSelectorToggle = function (id, element) {

        jQuery(document).on('click', '.project-selector .dropdown-menu', function (e) {
            e.stopPropagation();
        });

    };

    var toggleClientList = function  (id, element) {

        jQuery(".client_"+id).toggle("fast");

        if(jQuery(element).find("i").hasClass("fa-caret-down")){
            jQuery(element).find("i").removeClass("fa-caret-down");
            jQuery(element).find("i").addClass("fa-caret-up");
        }else{
            jQuery(element).find("i").removeClass("fa-caret-up");
            jQuery(element).find("i").addClass("fa-caret-down");
        }

    }

    // Make public what you want to have public, everything else is private
    return {
        toggleClientList:toggleClientList,
    };

})();
