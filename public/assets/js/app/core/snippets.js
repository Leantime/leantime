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
        leantime.toast.show({message: leantime.i18n.__("short_notifications.url_copied"), style: "success"});

    };

    var copyToClipboard = function (content) {

        navigator.clipboard.writeText(content);

        // Alert the copied text
        leantime.toast.show({message: leantime.i18n.__("short_notifications.url_copied"), style: "success"});

    };

    var initConfettiClick = function() {
        document.querySelectorAll(".confetti").forEach(function(el) {
            el.addEventListener("click", function() {
                confetti.start();
            });
        });
    };

    var accordionToggle = function (id) {

        var toggleEl = document.getElementById("accordion_toggle_" + id);
        var currentLink = toggleEl ? toggleEl.querySelector(".material-symbols-outlined") : null;
        var submenuName = 'accordion_content-'+id;
        var submenuState = "closed";
        var contentEl = document.getElementById('accordion_content-' + id);

        if(currentLink && currentLink.textContent.trim() === "chevron_right"){
            currentLink.textContent = "expand_more";
            if (contentEl) {
                contentEl.style.display = '';
            }
            submenuState = "open";

        }else if(currentLink){

            currentLink.textContent = "chevron_right";

            if (contentEl) {
                contentEl.style.display = 'none';
            }
            submenuState = "closed";
        }

        var formData = new FormData();
        formData.append('submenu', submenuName);
        formData.append('state', submenuState);

        fetch(leantime.appUrl + '/api/submenu', {
            method: 'PATCH',
            body: formData,
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

    };

    var toggleTheme = function (theme) {

        var themeStyleSheet = document.getElementById("themeStyleSheet");
        var themeUrl = themeStyleSheet.getAttribute("href");

        if(theme == "light"){
            themeUrl = themeUrl.replace("dark.css", "light.css");
            themeStyleSheet.setAttribute("href", themeUrl);
        }else if (theme == "dark"){
            themeUrl = themeUrl.replace("light.css", "dark.css");
            themeStyleSheet.setAttribute("href", themeUrl);
        }

    };

    var toggleBg = function (theme) {

        var themeStyleSheet = document.getElementById("themeStyleSheet");
        var themeUrl = themeStyleSheet.getAttribute("href");

        if(theme == "minimal"){
            themeUrl = themeUrl.replace("default", "minimal");
            themeStyleSheet.setAttribute("href", themeUrl);
        }else if (theme == "default"){
            themeUrl = themeUrl.replace("minimal", "default");
            themeStyleSheet.setAttribute("href", themeUrl);
        }

    };

    var toggleFont = function (font) {

        document.getElementById("fontStyleSetter").innerHTML = ":root { --primary-font-family: '" + font + "', 'Helvetica Neue', Helvetica, sans-serif; }";

    };

    var toggleColors = function (accent1, accent2) {

        document.getElementById("colorSchemeSetter").innerHTML = ":root { --accent1: " + accent1 + "; --accent2: " + accent2 + "}";

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
