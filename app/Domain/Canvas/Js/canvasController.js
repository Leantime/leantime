leantime.canvasController = (function () {

    var canvasName = '';

    var setCanvasName = function (name) {
        canvasName = name;
    };

    var initFilterBar = function () {

        window.addEventListener("load", function () {
            document.querySelectorAll(".loading").forEach(function (el) {
                el.style.display = 'none';
            });
            document.querySelectorAll(".filterBar .row-fluid").forEach(function (el) {
                el.style.opacity = "1";
            });
        });

    };

    var initCanvasLinks = function () {
        // No-op: inline modals now use native <dialog> with onclick handlers.
        // Generic canvas templates use modalManager hash-link delegation.
    };

    var closeModal = false;

    //Variables
    var canvasoptions = {
        sizes: {
            minW:  700,
            minH: 1000,
        },
        resizable: true,
        autoSizable: true,
        callbacks: {
            beforeShowCont: function () {
                document.querySelectorAll(".showDialogOnLoad").forEach(function (el) {
                    el.style.display = '';
                });
                if (closeModal == true) {
                    closeModal = false;
                    location.reload();
                }
            },
            afterShowCont: function () {
                window.htmx.process('#global-modal-content');
                jQuery("." + canvasName + "CanvasModal, #commentForm, #commentForm .deleteComment, ." + canvasName + "CanvasMilestone .deleteMilestone").nyroModal(canvasoptions);

            },
            beforeClose: function () {
                location.reload();
            }
        },
        titleFromIframe: true

    };

    //Functions

    var _initModals = function () {
        jQuery("." + canvasName + "CanvasModal, #commentForm, #commentForm .deleteComment, ." + canvasName + "CanvasMilestone .deleteMilestone").nyroModal(canvasoptions);
    };

    var openModalManually = function (url) {
        jQuery.nmManual(url, canvasoptions);
    };

    var toggleMilestoneSelectors = function (trigger) {
        if (trigger == 'existing') {
            document.querySelectorAll('#newMilestone, #milestoneSelectors').forEach(function (el) {
                el.style.display = 'none';
            });
            var existingEl = document.getElementById('existingMilestone');
            if (existingEl) { existingEl.style.display = ''; }
            _initModals();
        }
        if (trigger == 'new') {
            var newEl = document.getElementById('newMilestone');
            if (newEl) { newEl.style.display = ''; }
            document.querySelectorAll('#existingMilestone, #milestoneSelectors').forEach(function (el) {
                el.style.display = 'none';
            });
            _initModals();
        }

        if (trigger == 'hide') {
            document.querySelectorAll('#newMilestone, #existingMilestone').forEach(function (el) {
                el.style.display = 'none';
            });
            var selectorsEl = document.getElementById('milestoneSelectors');
            if (selectorsEl) { selectorsEl.style.display = ''; }
        }
    };

    var setCloseModal = function () {
        closeModal = true;
    };

    var initUserDropdown = function () {

        document.body.addEventListener(
            "click",
            function (e) {
                var target = e.target.closest(".userDropdown .dropdown-menu a");
                if (!target) { return; }

                var dataValue = target.getAttribute("data-value").split("_");
                var dataLabel = target.getAttribute('data-label');

                if (dataValue.length == 3) {
                    var canvasId = dataValue[0];
                    var userId = dataValue[1];
                    var profileImageId = dataValue[2];

                    fetch(leantime.appUrl + '/api/' + canvasName + 'canvas', {
                        method: 'PATCH',
                        credentials: 'include',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new URLSearchParams({
                            id: canvasId,
                            author: userId
                        })
                    }).then(function () {
                        var img = document.querySelector("#userDropdownMenuLink" + canvasId + " span.text span#userImage" + canvasId + " img");
                        if (img) { img.setAttribute("src", leantime.appUrl + "/api/users?profileImage=" + userId); }
                        leantime.toast.show({message: leantime.i18n.__("short_notifications.user_updated"), style: "success"});
                    });
                }
            }
        );
    };

    var initStatusDropdown = function () {

        document.body.addEventListener(
            "click",
            function (e) {
                var target = e.target.closest(".statusDropdown .dropdown-menu a");
                if (!target) { return; }

                var dataValue = target.getAttribute("data-value").split("/");
                var dataLabel = target.getAttribute('data-label');

                if (dataValue.length == 2) {
                    var canvasItemId = dataValue[0];
                    var status = dataValue[1];
                    var statusClass = target.getAttribute('class');

                    fetch(leantime.appUrl + '/api/' + canvasName + 'canvas', {
                        method: 'PATCH',
                        credentials: 'include',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new URLSearchParams({
                            id: canvasItemId,
                            status: status
                        })
                    }).then(function () {
                        var textEl = document.querySelector("#statusDropdownMenuLink" + canvasItemId + " span.text");
                        if (textEl) { textEl.textContent = dataLabel; }
                        var linkEl = document.getElementById("statusDropdownMenuLink" + canvasItemId);
                        if (linkEl) { linkEl.className = statusClass + " dropdown-toggle f-left status "; }
                        leantime.toast.show({message: leantime.i18n.__("short_notifications.status_updated")});
                    });
                }
            }
        );

    };

    var initRelatesDropdown = function () {

        document.body.addEventListener(
            "click",
            function (e) {
                var target = e.target.closest(".relatesDropdown .dropdown-menu a");
                if (!target) { return; }

                var dataValue = target.getAttribute("data-value").split("/");
                var dataLabel = target.getAttribute('data-label');

                if (dataValue.length == 2) {
                    var canvasItemId = dataValue[0];
                    var relates = dataValue[1];
                    var relatesClass = target.getAttribute('class');

                    fetch(leantime.appUrl + '/api/' + canvasName + 'canvas', {
                        method: 'PATCH',
                        credentials: 'include',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new URLSearchParams({
                            id: canvasItemId,
                            relates: relates
                        })
                    }).then(function () {
                        var textEl = document.querySelector("#relatesDropdownMenuLink" + canvasItemId + " span.text");
                        if (textEl) { textEl.textContent = dataLabel; }
                        var linkEl = document.getElementById("relatesDropdownMenuLink" + canvasItemId);
                        if (linkEl) { linkEl.className = relatesClass + " dropdown-toggle f-left relates "; }
                        leantime.toast.show({message: leantime.i18n.__("short_notifications.relates_updated")});
                    });
                }
            }
        );

    };

    /**
     * Factory: creates a domain-specific canvas controller.
     *
     * @param {string} name       Canvas domain name (e.g. 'lean', 'swot')
     * @param {object} [config]   Optional overrides
     * @param {number} [config.nbRows]          Number of numbered rows for setRowHeights
     * @param {string[]} [config.rowSelectors]   Custom row selectors (e.g. ['stakeholderRow','financialsRow'])
     * @param {object} [config.extras]           Extra methods to merge into the controller
     * @returns {object} Controller with standard canvas methods
     */
    var createController = function (name, config) {
        config = config || {};
        var _name = name;
        var _closeModal = false;

        var _canvasoptions = function () {
            return {
                sizes: { minW: 700, minH: 1000 },
                resizable: true,
                autoSizable: true,
                callbacks: {
                    beforeShowCont: function () {
                        document.querySelectorAll(".showDialogOnLoad").forEach(function (el) {
                            el.style.display = '';
                        });
                        if (_closeModal) {
                            _closeModal = false;
                            location.reload();
                        }
                    },
                    afterShowCont: function () {
                        window.htmx.process('#global-modal-content');
                        jQuery("." + _name + "CanvasModal, #commentForm, #commentForm .deleteComment, ." + _name + "CanvasMilestone .deleteMilestone").nyroModal(_canvasoptions());
                    },
                    beforeClose: function () {
                        location.reload();
                    }
                },
                titleFromIframe: true
            };
        };

        var _initModals = function () {
            jQuery("." + _name + "CanvasModal, #commentForm, #commentForm .deleteComment, ." + _name + "CanvasMilestone .deleteMilestone").nyroModal(_canvasoptions());
        };

        // Build setRowHeights based on config
        var _setRowHeights;
        if (config.rowSelectors && config.rowSelectors.length > 0) {
            // Custom named rows (e.g. sb canvas)
            _setRowHeights = function () {
                config.rowSelectors.forEach(function (selector) {
                    var maxHeight = 0;
                    document.querySelectorAll("#" + selector + " div.contentInner").forEach(function (el) {
                        if (el.offsetHeight > maxHeight) {
                            maxHeight = el.offsetHeight + 35;
                        }
                    });
                    document.querySelectorAll("#" + selector + " .column .contentInner").forEach(function (el) {
                        el.style.height = maxHeight + "px";
                    });
                });
            };
        } else if (config.nbRows && config.nbRows > 0) {
            // Standard numbered rows
            _setRowHeights = function () {
                var nbRows = config.nbRows;
                var rowNames = ['firstRow', 'secondRow', 'thirdRow', 'fourthRow'];
                var rowHeight = document.documentElement.offsetHeight - 320 - 20 * nbRows;
                if (nbRows === 2) { rowHeight -= 25; }

                for (var i = 0; i < nbRows && i < rowNames.length; i++) {
                    var rowSelector = "#" + rowNames[i];
                    var thisRowHeight = rowHeight / nbRows;
                    if (nbRows >= 3) { thisRowHeight = rowHeight * 0.333; }
                    document.querySelectorAll(rowSelector + " div.contentInner").forEach(function (el) {
                        if (el.offsetHeight > thisRowHeight) {
                            thisRowHeight = el.offsetHeight + 50;
                        }
                    });
                    document.querySelectorAll(rowSelector + " .column .contentInner").forEach(function (el) {
                        el.style.height = thisRowHeight + "px";
                    });
                }
            };
        } else {
            // No-op (sm, sq canvases)
            _setRowHeights = function () {};
        }

        var controller = {
            setCloseModal: function () { _closeModal = true; },
            toggleMilestoneSelectors: function (trigger) {
                if (trigger == 'existing') {
                    document.querySelectorAll('#newMilestone, #milestoneSelectors').forEach(function (el) {
                        el.style.display = 'none';
                    });
                    var existingEl = document.getElementById('existingMilestone');
                    if (existingEl) { existingEl.style.display = ''; }
                    _initModals();
                }
                if (trigger == 'new') {
                    var newEl = document.getElementById('newMilestone');
                    if (newEl) { newEl.style.display = ''; }
                    document.querySelectorAll('#existingMilestone, #milestoneSelectors').forEach(function (el) {
                        el.style.display = 'none';
                    });
                    _initModals();
                }
                if (trigger == 'hide') {
                    document.querySelectorAll('#newMilestone, #existingMilestone').forEach(function (el) {
                        el.style.display = 'none';
                    });
                    var selectorsEl = document.getElementById('milestoneSelectors');
                    if (selectorsEl) { selectorsEl.style.display = ''; }
                }
            },
            openModalManually: function (url) { jQuery.nmManual(url, _canvasoptions); },
            initUserDropdown: initUserDropdown,
            initStatusDropdown: initStatusDropdown,
            initRelatesDropdown: initRelatesDropdown,
            setRowHeights: _setRowHeights
        };

        // Merge any extras (e.g. goalCanvasController.initProgressChart)
        if (config.extras) {
            for (var key in config.extras) {
                if (config.extras.hasOwnProperty(key)) {
                    controller[key] = config.extras[key];
                }
            }
        }

        return controller;
    };

    // Make public what you want to have public, everything else is private
    return {
        setCanvasName:setCanvasName,
        initFilterBar:initFilterBar,
        initCanvasLinks:initCanvasLinks,
        initUserDropdown:initUserDropdown,
        initStatusDropdown:initStatusDropdown,
        initRelatesDropdown:initRelatesDropdown,
        setCloseModal:setCloseModal,
        toggleMilestoneSelectors:toggleMilestoneSelectors,
        openModalManually:openModalManually,
        createController:createController
    };

})();
