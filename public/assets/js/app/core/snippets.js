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
    document.addEventListener('lt:ui:content.loaded', function (event) { initInfoTips(event.detail.root); });

    // forms.button-group segmented: expose the starting choice to assistive tech on render.
    var initSegmentedGroups = function (root) {
        if (!root || !root.querySelectorAll) {
            return;
        }
        root.querySelectorAll('.lt-btn-group--segmented > .btn:not([aria-pressed])').forEach(function (button) {
            button.setAttribute('aria-pressed', button.classList.contains('active') ? 'true' : 'false');
        });
    };
    htmx.onLoad(initSegmentedGroups);
    // Content inserted outside an htmx swap (dialogs …): see leantime.initLoadedContent in app.js.
    document.addEventListener('lt:ui:content.loaded', function (event) { initSegmentedGroups(event.detail.root); });

    // forms.button-group segmented: one picked option. Moves .active / aria-pressed to the clicked
    // button; the page's own click handler reacts to the choice.
    document.addEventListener('click', function (event) {
        var button = event.target.closest ? event.target.closest('.lt-btn-group--segmented > .btn') : null;
        if (!button) {
            return;
        }
        Array.prototype.forEach.call(button.parentElement.children, function (sibling) {
            var picked = sibling === button;
            sibling.classList.toggle('active', picked);
            sibling.setAttribute('aria-pressed', picked ? 'true' : 'false');
        });
    });

    // elements.accordion with an id remembers open/closed per user (same store as accordionToggle).
    // `toggle` does not bubble, so listen in the capture phase. Browsers also fire it when a
    // <details open> first renders; data-lt-open holds the rendered state so only a real change saves.
    document.addEventListener('toggle', function (event) {
        var details = event.target;
        if (!details || !details.matches || !details.matches('.lt-accordion > details')) {
            return;
        }
        // A DataTable set up while its section was closed has no column widths yet.
        if (details.open && window.jQuery && jQuery.fn.dataTable) {
            jQuery(details).find('table.dataTable').each(function () {
                jQuery(this).DataTable().columns.adjust();
            });
        }
        if (!details.hasAttribute('data-lt-accordion')) {
            return;
        }
        var state = details.open ? 'open' : 'closed';
        if (details.getAttribute('data-lt-open') === state) {
            return;
        }
        details.setAttribute('data-lt-open', state);
        leantime.rpc('Api.Api.setSubmenuState', {
            submenu: 'accordion_content-' + details.getAttribute('data-lt-accordion'),
            state: state
        }).catch(function (e) { console.error('Could not persist accordion state', e); });
    }, true);

    // elements.accordion actions sit over the summary row (outside <summary> so they don't toggle);
    // reserve exactly their width so long or several actions never cover the title.
    var accordionActionsObserver = typeof ResizeObserver === 'undefined' ? null : new ResizeObserver(function (entries) {
        entries.forEach(function (entry) {
            var accordion = entry.target.closest('.lt-accordion');
            if (accordion) {
                var box = entry.target.getBoundingClientRect();
                accordion.style.setProperty('--lt-accordion-actions-w', Math.ceil(box.width) + 'px');
                // Wrapped actions are taller than the summary row: reserve that height too.
                accordion.style.setProperty('--lt-accordion-actions-h', Math.ceil(box.height) + 'px');
            }
        });
    });
    var observeAccordionActions = function (root) {
        if (!accordionActionsObserver || !root || !root.querySelectorAll) {
            return;
        }
        root.querySelectorAll('.lt-accordion > .lt-accordion-actions').forEach(function (actions) {
            accordionActionsObserver.observe(actions);
        });
    };
    htmx.onLoad(observeAccordionActions);
    // Content inserted outside an htmx swap (dialogs …): see leantime.initLoadedContent in app.js.
    document.addEventListener('lt:ui:content.loaded', function (event) { observeAccordionActions(event.detail.root); });
    document.addEventListener('htmx:beforeCleanupElement', function (event) {
        if (accordionActionsObserver && event.target.matches && event.target.matches('.lt-accordion > .lt-accordion-actions')) {
            accordionActionsObserver.unobserve(event.target);
        }
    });

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

    // Relative luminance (WCAG) of a #rrggbb color, null when it can't be parsed.
    var relativeLuminance = function (hexColor) {
        var match = /^#?([0-9a-f]{6})$/i.exec(hexColor || '');
        if (!match) {
            return null;
        }

        var channels = [0, 2, 4].map(function (offset) {
            var value = parseInt(match[1].substr(offset, 2), 16) / 255;
            return value <= 0.03928 ? value / 12.92 : Math.pow((value + 0.055) / 1.055, 2.4);
        });

        return 0.2126 * channels[0] + 0.7152 * channels[1] + 0.0722 * channels[2];
    };

    // Live preview of a color scheme. Applies the same contrast rules as sections/header.blade.php:
    // a nav scrim when white text is below AA on an accent, and a dark primary-button label when
    // white is below AA on accent1 and dark text reads better.
    var toggleColors = function (accent1, accent2) {

        var navNeedsScrim = [accent1, accent2].some(function (accent) {
            var luminance = relativeLuminance(accent);
            return luminance === null || 1.05 / (luminance + 0.05) < 4.5;
        });

        var css = ":root { --accent1: " + accent1 + "; --accent2: " + accent2 + "; --nav-scrim: " + (navNeedsScrim ? "0.22" : "0") + ";";

        var primaryLuminance = relativeLuminance(accent1);
        if (primaryLuminance !== null) {
            var whiteLabelContrast = 1.05 / (primaryLuminance + 0.05);
            var darkLabelContrast = (primaryLuminance + 0.05) / (0.0103 + 0.05);
            if (whiteLabelContrast < 4.5 && darkLabelContrast > whiteLabelContrast) {
                css += " --main-action-label-color: #1a1a1a;";
            }
        }

        jQuery("#colorSchemeSetter").html(css + " }");
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
