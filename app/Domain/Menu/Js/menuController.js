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
                iconEl.classList.remove('fa-angle-right');
                iconEl.classList.add('fa-angle-down');
            }
            submenuState = 'open';
        } else {
            submenuEl.style.display = 'none';
            if (iconEl) {
                iconEl.classList.remove('fa-angle-down');
                iconEl.classList.add('fa-angle-right');
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

        // Vanilla tabs implementation (replaces jQuery UI tabs)
        var tabsEl = document.querySelector('.projectSelectorTabs');
        if (tabsEl) {
            var tabLinks = tabsEl.querySelectorAll('ul > li > a');
            var currentTab = localStorage.getItem("currentMenuTab");
            var activeTabIndex = 0;

            // Find the saved tab index
            if (currentTab) {
                tabLinks.forEach(function (link, index) {
                    if (link.getAttribute('href') === '#' + currentTab) {
                        activeTabIndex = index;
                    }
                });
            }

            // Initialize: hide all panels, show the active one
            tabLinks.forEach(function (link, index) {
                var panelId = link.getAttribute('href');
                if (!panelId || panelId.charAt(0) !== '#') return;
                var panel = document.querySelector(panelId);
                if (!panel) return;

                if (index === activeTabIndex) {
                    panel.style.display = '';
                    link.parentElement.classList.add('ui-tabs-active');
                } else {
                    panel.style.display = 'none';
                    link.parentElement.classList.remove('ui-tabs-active');
                }

                // Tab click handler
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    // Hide all panels, deactivate all tabs
                    tabLinks.forEach(function (otherLink) {
                        var otherPanelId = otherLink.getAttribute('href');
                        if (otherPanelId && otherPanelId.charAt(0) === '#') {
                            var otherPanel = document.querySelector(otherPanelId);
                            if (otherPanel) otherPanel.style.display = 'none';
                        }
                        otherLink.parentElement.classList.remove('ui-tabs-active');
                    });
                    // Show this panel
                    if (panel) panel.style.display = '';
                    link.parentElement.classList.add('ui-tabs-active');
                    // Save to localStorage
                    var tabId = panelId.replace('#', '');
                    localStorage.setItem("currentMenuTab", tabId);
                });
            });
        }

    };

    var initLeftMenuHamburgerButton = function () {


        var newWidth = 68;
        if (window.innerWidth < 576) {
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
            var iconEl = togglerEl.querySelector("i");
            if (iconEl) {
                iconEl.classList.remove("fa-angle-down");
                iconEl.classList.add("fa-angle-right");
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
            var iconEl = togglerEl.querySelector("i");
            if (iconEl) {
                iconEl.classList.remove("fa-angle-right");
                iconEl.classList.add("fa-angle-down");
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

    // Make public what you want to have public, everything else is private
    return {
        toggleSubmenu:toggleSubmenu,
        initProjectSelector:initProjectSelector,
        initLeftMenuHamburgerButton:initLeftMenuHamburgerButton,
        updateGroupDropdownSetting: updateGroupDropdownSetting,
        toggleProjectDropDownList:toggleProjectDropDownList
    };

})();
