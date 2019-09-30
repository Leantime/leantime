leantime.menuController = (function () {

    //Variables

    //Constructor
    (function () {
        jQuery(document).ready(
            function () {
                _initProjectSelector();
                _initLeftMenuHamburgerButton();
            }
        );

    })();

    //Functions

    var _initProjectSelector = function () {

        jQuery(".project-select").chosen();

    };

    var _initLeftMenuHamburgerButton = function (){

        if (jQuery('.barmenu').hasClass('open')) {
            jQuery('.rightpanel, .headerinner').css({marginLeft: '260px'});
            jQuery('.logo, .leftpanel').css({marginLeft: 0});
            leantime.menuRepository.updateUserMenuSettings("open");
        } else {
            jQuery('.rightpanel, .headerinner').css({marginLeft: 0});
            jQuery('.logo, .leftpanel').css({marginLeft: '-260px'});
            leantime.menuRepository.updateUserMenuSettings("closed");
        }

        jQuery('.barmenu').click(function () {

            var lwidth = '260px';
            if (jQuery(window).width() < 340) {
                lwidth = '240px';
            }

            if (!jQuery(this).hasClass('open')) {
                jQuery('.rightpanel, .headerinner').animate({marginLeft: '260px'}, 'fast', function(){
                    jQuery('.barmenu').addClass('open');
                });
                jQuery('.logo, .leftpanel').animate({marginLeft: 0}, 'fast');

                leantime.menuRepository.updateUserMenuSettings("open");
            } else {

                jQuery('.rightpanel, .headerinner').animate({marginLeft: 0}, 'fast', function() {
                    jQuery('.barmenu').removeClass('open');
                });
                jQuery('.logo, .leftpanel').animate({marginLeft: '-' + '260px'}, 'fast');

                leantime.menuRepository.updateUserMenuSettings("closed");

            }
        });

        // show/hide left menu
        jQuery(window).resize(function () {

            if (jQuery('.barmenu').hasClass('open')) {
                jQuery('.rightpanel, .headerinner').css({marginLeft: '260px'});
                jQuery('.logo, .leftpanel').css({marginLeft: 0});
                leantime.menuRepository.updateUserMenuSettings("open");
            } else {
                jQuery('.rightpanel, .headerinner').css({marginLeft: 0});
                jQuery('.logo, .leftpanel').css({marginLeft: '-260px'});
                leantime.menuRepository.updateUserMenuSettings("closed");
            }
        });

    };

    // Make public what you want to have public, everything else is private
    return {};

})();
