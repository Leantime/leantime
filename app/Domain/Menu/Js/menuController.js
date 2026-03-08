leantime.menuController = (function () {


    //Functions

    var toggleSubmenu = function (submenuName) {

        if (submenuName === "") {
            return;
        }

        var submenuEl = document.querySelector('#submenu-' + submenuName);
        var iconEl = document.querySelector('#submenu-icon-' + submenuName);
        var submenuState = '';

        if (!submenuEl) {
            return;
        }

        if (submenuEl.style.display === 'none' || getComputedStyle(submenuEl).display === 'none') {
            submenuEl.style.display = 'block';
            if (iconEl) {
                iconEl.textContent = 'expand_more';
            }
            submenuState = 'open';
        } else {
            submenuEl.style.display = 'none';
            if (iconEl) {
                iconEl.textContent = 'chevron_right';
            }
            submenuState = 'closed';
        }

        fetch(leantime.appUrl + '/api/submenu', {
            method: 'PATCH',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                submenu: submenuName,
                state: submenuState
            })
        });
    }

    var initProjectSelector = function () {

        document.addEventListener('click', function (e) {
            if (e.target.closest('.projectselector.dropdown-menu')) {
                e.stopPropagation();
            }
        });

    };

    var initLeftMenuHamburgerButton = function () {


        var newWidth = 68;
        // Match the CSS mobile breakpoint: collapse sidebar on all screens < 768px
        if (window.innerWidth < 768) {
            var mainwrapperEl = document.querySelector(".mainwrapper");
            if (mainwrapperEl) {
                mainwrapperEl.classList.remove("menuopen");
                mainwrapperEl.classList.add("menuclosed");
            }
        }

        var barmenuEl = document.querySelector('.barmenu');
        if (barmenuEl) {
            barmenuEl.addEventListener('click', function () {

                var mainwrapperEl = document.querySelector(".mainwrapper");
                if (!mainwrapperEl) {
                    return;
                }

                if (mainwrapperEl.classList.contains('menuopen')) {
                    mainwrapperEl.classList.remove("menuopen");
                    mainwrapperEl.classList.add("menuclosed");

                    //If it doesn't have the class open, the user wants it to be open.
                    leantime.menuRepository.updateUserMenuSettings("closed");
                } else {
                    mainwrapperEl.classList.remove("menuclosed");
                    mainwrapperEl.classList.add("menuopen");

                    //If it doesn't have the class open, the user wants it to be open.
                    leantime.menuRepository.updateUserMenuSettings("open");
                }

            });
        }

        // On mobile: tap the backdrop (::before pseudo-element area) to close sidebar.
        // We detect taps on the mainwrapper that land outside the leftpanel.
        if (window.innerWidth < 768) {
            var mainwrapperEl = document.querySelector(".mainwrapper");
            if (mainwrapperEl) {
                mainwrapperEl.addEventListener('click', function (e) {
                    if (!mainwrapperEl.classList.contains('menuopen')) return;
                    var leftpanel = document.querySelector('.leftpanel');
                    if (leftpanel && !leftpanel.contains(e.target) && !e.target.closest('.barmenu')) {
                        mainwrapperEl.classList.remove("menuopen");
                        mainwrapperEl.classList.add("menuclosed");
                        leantime.menuRepository.updateUserMenuSettings("closed");
                    }
                });
            }
        }

    };


    var toggleProjectDropDownList = function (id, set="", prefix) {

        //toggler-ID (link to click on open/close)
        //dropdown-ID (dropdown to open/close)

        var togglerEl = document.querySelector("#" + prefix + "-toggler-" + id);
        var groupEl = document.querySelector("#" + prefix + "-projectSelectorlist-group-" + id);

        if (!togglerEl) {
            return;
        }

        //Part 1 allow devs to set open/closed state.
        //This means we need to do the opposite of what the current state is.
        if (set === "closed") {
            togglerEl.classList.remove("closed", "open");
            togglerEl.classList.add("open");
        } else if (set === "open") {
            togglerEl.classList.remove("open", "closed");
            togglerEl.classList.add("closed");
        }

        //Part 2
        //Do the toggle. If the link has the class open, we need to close it.
        if (togglerEl.classList.contains("open")) {
            //Update class on link
            togglerEl.classList.remove("open");
            togglerEl.classList.add("closed");

            //Update icon on link
            var iconEl = togglerEl.querySelector(".material-symbols-outlined");
            if (iconEl) {
                iconEl.textContent = 'chevron_right';
            }

            if (groupEl) {
                groupEl.classList.add("closed");
                groupEl.classList.remove("open");
            }

            updateGroupDropdownSetting(id, "closed", prefix);
        } else {
            //Update class on link
            togglerEl.classList.remove("closed");
            togglerEl.classList.add("open");

            //Update icon on link
            var iconEl = togglerEl.querySelector(".material-symbols-outlined");
            if (iconEl) {
                iconEl.textContent = 'expand_more';
            }

            if (groupEl) {
                groupEl.classList.add("open");
                groupEl.classList.remove("closed");
            }

            updateGroupDropdownSetting(id, "open", prefix);
        }

    };

    let updateGroupDropdownSetting = function (ID, state, prefix) {

        fetch(leantime.appUrl + '/api/submenu', {
            method: 'PATCH',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                submenu: prefix + "-projectSelectorlist-group-" + ID,
                state: state
            })
        });

    };

    /**
     * Updates the active state of left nav menu items based on the current URL.
     * Called after HTMX boosted navigation swaps content without reloading the nav.
     */
    var updateLeftNavActiveState = function () {

        var currentPath = window.location.pathname.replace(/\/+$/, '');
        var navLinks = document.querySelectorAll('.leftmenu .nav-tabs.nav-stacked .dropdown ul li:not(.submenuToggle):not(.title):not(.separator) a[href]');
        var bestMatch = null;
        var bestMatchLength = 0;

        navLinks.forEach(function (link) {
            var linkItem = link.closest('li');
            if (!linkItem) {
                return;
            }

            // Remove active from all items first
            linkItem.classList.remove('active');

            // Extract path from href (strip origin if absolute URL)
            var linkPath = link.getAttribute('href');
            try {
                var url = new URL(linkPath, window.location.origin);
                linkPath = url.pathname.replace(/\/+$/, '');
            } catch (e) {
                return;
            }

            // Match: current path starts with or equals the link path
            // Use the longest match to handle overlapping paths (e.g. /tickets/showKanban vs /tickets/roadmap)
            if (linkPath && currentPath.indexOf(linkPath) === 0 && linkPath.length > bestMatchLength) {
                bestMatch = linkItem;
                bestMatchLength = linkPath.length;
            }
        });

        // Also check the fixed settings menu point
        var fixedItems = document.querySelectorAll('.fixedMenuPoint a[href]');
        fixedItems.forEach(function (link) {
            var linkItem = link.closest('.fixedMenuPoint');
            if (!linkItem) {
                return;
            }
            linkItem.classList.remove('active');

            var linkPath = link.getAttribute('href');
            try {
                var url = new URL(linkPath, window.location.origin);
                linkPath = url.pathname.replace(/\/+$/, '');
            } catch (e) {
                return;
            }

            if (linkPath && currentPath.indexOf(linkPath) === 0 && linkPath.length > bestMatchLength) {
                bestMatch = linkItem;
                bestMatchLength = linkPath.length;
            }
        });

        if (bestMatch) {
            bestMatch.classList.add('active');
        }
    };

    // Listen for HTMX history pushes to update active nav state after boosted navigation
    document.addEventListener('htmx:pushedIntoHistory', function () {
        updateLeftNavActiveState();
    });

    // Make public what you want to have public, everything else is private
    return {
        toggleSubmenu:toggleSubmenu,
        initProjectSelector:initProjectSelector,
        initLeftMenuHamburgerButton:initLeftMenuHamburgerButton,
        updateGroupDropdownSetting: updateGroupDropdownSetting,
        toggleProjectDropDownList:toggleProjectDropDownList,
        updateLeftNavActiveState:updateLeftNavActiveState
    };

})();
