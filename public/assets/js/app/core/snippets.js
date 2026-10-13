leantime.snippets = (function () {

    var copyUrl = function (field) {

        // Get the text field
        var copyText = document.getElementById(field);

        // Select the text field
        copyText.select();
        copyText.setSelectionRange(0, 99999); // For mobile devices

        // Copy the text inside the text field
        navigator.clipboard.writeText(copyText.value);

        // Alert the copied text
        jQuery.growl({message: leantime.i18n.__("short_notifications.url_copied"), style: "success"});

    };

    var copyToClipboard = function (content) {

        navigator.clipboard.writeText(content);

        // Alert the copied text
        jQuery.growl({message: leantime.i18n.__("short_notifications.url_copied"), style: "success"});

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

        if(currentLink.hasClass("fa-angle-right")){
            currentLink.removeClass("fa-angle-right");
            currentLink.addClass("fa-angle-down");
            jQuery('#accordion_content-'+id).slideDown("fast");
            submenuState = "open";

        }else{

            currentLink.removeClass("fa-angle-down");
            currentLink.addClass("fa-angle-right");

            jQuery('#accordion_content-'+id).slideUp("fast");
            submenuState = "closed";
        }

        leantime.rpc('Api.Api.setSubmenuState', {
            submenu : submenuName,
            state   : submenuState
        }).catch(function (e) { console.error('Could not persist accordion state', e); });

    };

    // elements.info-tip: its own tippy (hover, focus and tap) so it also works on touch screens,
    // where leantime.initTooltips turns plain data-tippy-content tooltips off.
    var initInfoTips = function (root) {
        if (typeof tippy === 'undefined' || !root || !root.querySelectorAll) {
            return;
        }
        var buttons = Array.prototype.slice.call(root.querySelectorAll('button[data-lt-info]'));
        // An outerHTML swap hands htmx.onLoad the button itself.
        if (root.matches && root.matches('button[data-lt-info]')) {
            buttons.unshift(root);
        }
        buttons.forEach(function (button) {
            if (button._tippy) {
                return;
            }
            tippy(button, {
                content: button.getAttribute('data-lt-info'),
                // The button already describes itself (aria-describedby → its sr-only text);
                // Tippy's own aria-describedby would read the same text twice.
                aria: { content: null, expanded: false },
                trigger: 'mouseenter focus click',
                interactive: false,
                maxWidth: 320,
                onShow: function () { button.setAttribute('aria-expanded', 'true'); },
                onHide: function () { button.setAttribute('aria-expanded', 'false'); }
            });
        });
    };
    htmx.onLoad(initInfoTips);
    // Release the popper and its document listeners when htmx removes a tip (even an open one).
    document.addEventListener('htmx:beforeCleanupElement', function (event) {
        var element = event.target;
        if (element && element.matches && element.matches('button[data-lt-info]') && element._tippy) {
            element._tippy.destroy();
        }
    });
    document.addEventListener('lt:ui:modal.loaded', function (event) { initInfoTips(event.detail.root); });

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

    var toggleBg = function (theme) {

        var themeUrl = jQuery("#themeStyleSheet").attr("href");

        if(theme == "minimal"){
            themeUrl = themeUrl.replace("default", "minimal");
            jQuery("#themeStyleSheet").attr("href", themeUrl);
        }else if (theme == "default"){
            themeUrl = themeUrl.replace("minimal", "default");
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
    return {
        copyUrl:copyUrl,
        copyToClipboard:copyToClipboard,
        initConfettiClick:initConfettiClick,
        accordionToggle:accordionToggle,
        toggleTheme:toggleTheme,
        toggleFont:toggleFont,
        toggleColors:toggleColors,
        toggleBg:toggleBg
    };

})();
