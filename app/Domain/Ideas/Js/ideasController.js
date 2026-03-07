leantime.ideasController = (function () {

    var closeModal = false;

    //Variables
    var canvasoptions = function () {
        return {
            sizes: {
                minW: 700,
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

                    jQuery(".ideaModal, #commentForm, #commentForm .deleteComment, .leanCanvasMilestone .deleteMilestone").nyroModal(canvasoptions());

                }
            }
        }
    };

    //Functions

    var _initModals = function () {
        jQuery(".ideaModal, #commentForm, #commentForm .deleteComment, .leanCanvasMilestone .deleteMilestone").nyroModal(canvasoptions());
    };

    var openModalManually = function (url) {
        jQuery.nmManual(url, canvasoptions);
    };

    var initMasonryWall = function () {

        var gridEl = document.querySelector('#ideaMason');
        if (!gridEl) return;

        var pckry = new Packery(gridEl, {
            itemSelector: '.ticketBox',
            columnWidth: 260,
            isResizable: true
        });

        imagesLoaded(gridEl).on('progress', function () {
            pckry.layout();
        });

        var ticketBoxes = gridEl.querySelectorAll('.ticketBox');

        ticketBoxes.forEach(function (box) {
            var draggie = new Draggabilly(box);

            draggie.on('dragStart', function () {
                box.classList.add('tilt');
                tilt_direction(box);
            });

            draggie.on('dragEnd', function () {
                box.classList.remove('tilt');
                if (box._moveHandler) {
                    document.removeEventListener('mousemove', box._moveHandler);
                    delete box._moveHandler;
                }
            });

            pckry.bindDraggabillyEvents(draggie);
        });

        function tilt_direction(item)
        {
            var left_pos = item.getBoundingClientRect().left,
                move_handler = function (e) {
                    if (e.pageX >= left_pos) {
                        item.classList.add("right");
                        item.classList.remove("left");
                    } else {
                        item.classList.add("left");
                        item.classList.remove("right");
                    }
                    left_pos = e.pageX;
                };
            document.addEventListener("mousemove", move_handler);
            item._moveHandler = move_handler;
        }

        pckry.on('dragItemPositioned', orderItems);

        function orderItems()
        {
            var ideaSort = [];

            var itemElems = pckry.getItemElements();
            itemElems.forEach(function (itemElem, i) {
                var sortIndex = i + 1;
                var ideaId = itemElem.getAttribute("data-value");
                ideaSort.push({"id":ideaId, "sortIndex":sortIndex});
            });

            fetch(leantime.appUrl + '/api/ideas', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    action: "ideaSort",
                    payload: JSON.stringify(ideaSort)
                })
            });
        }
    };

    var initBoardControlModal = function () {


    };

    var initWallImageModals = function () {

        document.querySelectorAll('.mainIdeaContent img').forEach(function (img) {
            var link = document.createElement('a');
            link.href = img.getAttribute('src');
            link.className = 'imageModal';
            img.parentElement.insertBefore(link, img);
            link.appendChild(img);
        });

        jQuery(".imageModal").nyroModal();

    };


    var toggleMilestoneSelectors = function (trigger) {
        if (trigger == 'existing') {
            var newMs = document.querySelector('#newMilestone');
            var msSelectors = document.querySelector('#milestoneSelectors');
            var existingMs = document.querySelector('#existingMilestone');
            if (newMs) newMs.style.display = 'none';
            if (msSelectors) msSelectors.style.display = 'none';
            if (existingMs) existingMs.style.display = '';
            _initModals();
        }
        if (trigger == 'new') {
            var newMs = document.querySelector('#newMilestone');
            var existingMs = document.querySelector('#existingMilestone');
            var msSelectors = document.querySelector('#milestoneSelectors');
            if (newMs) newMs.style.display = '';
            if (existingMs) existingMs.style.display = 'none';
            if (msSelectors) msSelectors.style.display = 'none';
            _initModals();
        }

        if (trigger == 'hide') {
            var newMs = document.querySelector('#newMilestone');
            var existingMs = document.querySelector('#existingMilestone');
            var msSelectors = document.querySelector('#milestoneSelectors');
            if (newMs) newMs.style.display = 'none';
            if (existingMs) existingMs.style.display = 'none';
            if (msSelectors) msSelectors.style.display = '';
        }
    };

    var setCloseModal = function () {
        closeModal = true;
    };

    var initUserDropdown = function () {

        document.body.addEventListener(
            "click",
            function (e) {
                var link = e.target.closest(".userDropdown .dropdown-menu a");
                if (!link) return;

                var dataValue = link.getAttribute("data-value").split("_");
                var dataLabel = link.getAttribute('data-label');

                if (dataValue.length == 3) {
                    var canvasId = dataValue[0];
                    var userId = dataValue[1];
                    var profileImageId = dataValue[2];

                    fetch(leantime.appUrl + '/api/ideas', {
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
                        var imgEl = document.querySelector("#userDropdownMenuLink" + canvasId + " span.text span#userImage" + canvasId + " img");
                        if (imgEl) {
                            imgEl.setAttribute("src", leantime.appUrl + "/api/users?profileImage=" + userId);
                        }

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
                var link = e.target.closest(".statusDropdown .dropdown-menu a");
                if (!link) return;

                var dataValue = link.getAttribute("data-value").split("_");
                var dataLabel = link.getAttribute('data-label');

                if (dataValue.length == 3) {
                    var canvasItemId = dataValue[0];
                    var status = dataValue[1];
                    var statusClass = dataValue[2];

                    fetch(leantime.appUrl + '/api/ideas', {
                        method: 'PATCH',
                        credentials: 'include',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new URLSearchParams({
                            id: canvasItemId,
                            box: status
                        })
                    }).then(function () {
                        var textEl = document.querySelector("#statusDropdownMenuLink" + canvasItemId + " span.text");
                        if (textEl) {
                            textEl.textContent = dataLabel;
                        }
                        var dropdownEl = document.querySelector("#statusDropdownMenuLink" + canvasItemId);
                        if (dropdownEl) {
                            dropdownEl.className = statusClass + " dropdown-toggle f-left status";
                        }
                        leantime.toast.show({message: leantime.i18n.__("short_notifications.status_updated"), style: "success"});
                    });
                }
            }
        );

    };


    var setKanbanHeights = function () {

        var maxHeight = 0;

        var height = document.documentElement.scrollHeight - 320;
        document.querySelectorAll("#sortableIdeaKanban .column .contentInner").forEach(function (el) {
            el.style.height = height + "px";
        });

    };

    var initIdeaKanban = function (statusList) {

        document.querySelectorAll("#sortableIdeaKanban .ticketBox").forEach(function (box) {
            box.addEventListener("mouseenter", function () {
                this.style.background = "var(--kanban-card-hover)";
            });
            box.addEventListener("mouseleave", function () {
                this.style.background = "var(--kanban-card-bg)";
            });
        });

        function serializeSortable(containerEl) {
            var items = containerEl.querySelectorAll(':scope > .moveable');
            var parts = [];
            items.forEach(function (item) {
                if (item.id) {
                    var idx = item.id.indexOf('_');
                    if (idx !== -1) {
                        parts.push(item.id.substring(0, idx) + '[]=' + item.id.substring(idx + 1));
                    }
                }
            });
            return parts.join('&');
        }

        document.querySelectorAll("#sortableIdeaKanban .contentInner").forEach(function (contentInner) {
            if (contentInner._sortableInstance) contentInner._sortableInstance.destroy();
            contentInner._sortableInstance = new Sortable(contentInner, {
                group: 'idea-kanban',
                draggable: '.moveable',
                ghostClass: 'ui-state-highlight',
                filter: '.portlet-toggle',
                animation: 150,

                onStart: function (evt) {
                    evt.item.classList.add('tilt');
                    tilt_direction(evt.item);
                },

                onEnd: function (evt) {
                    evt.item.classList.remove('tilt');
                    if (evt.item._moveHandler) {
                        document.removeEventListener('mousemove', evt.item._moveHandler);
                        delete evt.item._moveHandler;
                    }

                    var statusPostData = {
                        action: "statusUpdate",
                        payload: {}
                    };

                    for (var i = 0; i < statusList.length; i++) {
                        var col = document.querySelector(".contentInner.status_" + statusList[i]);
                        if (col) {
                            statusPostData.payload[statusList[i]] = serializeSortable(col);
                        }
                    }

                    fetch(leantime.appUrl + '/api/ideas', {
                        method: 'POST',
                        credentials: 'include',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new URLSearchParams(statusPostData)
                    });
                }
            });
        });

        function tilt_direction(item)
        {
            var left_pos = item.getBoundingClientRect().left,
                move_handler = function (e) {
                    if (e.pageX >= left_pos) {
                        item.classList.add("right");
                        item.classList.remove("left");
                    } else {
                        item.classList.add("left");
                        item.classList.remove("right");
                    }
                    left_pos = e.pageX;
                };
            document.addEventListener("mousemove", move_handler);
            item._moveHandler = move_handler;
        }

        document.querySelectorAll(".portlet").forEach(function (portlet) {
            var header = portlet.querySelector(".portlet-header");
            if (header) {
                var toggleSpan = document.createElement("span");
                toggleSpan.className = "portlet-toggle";
                header.prepend(toggleSpan);
            }
        });

        document.querySelectorAll(".portlet-toggle").forEach(function (toggle) {
            toggle.addEventListener("click", function () {
                this.classList.toggle("expanded");
                var content = this.closest(".portlet").querySelector(".portlet-content");
                if (content) {
                    content.style.display = content.style.display === 'none' ? '' : 'none';
                }
            });
        });

    };

    // Make public what you want to have public, everything else is private
    return {
        setCloseModal:setCloseModal,
        toggleMilestoneSelectors: toggleMilestoneSelectors,
        openModalManually:openModalManually,
        initMasonryWall:initMasonryWall,
        initBoardControlModal:initBoardControlModal,
        initWallImageModals:initWallImageModals,
        initUserDropdown:initUserDropdown,
        initStatusDropdown:initStatusDropdown,
        setKanbanHeights:setKanbanHeights,
        initIdeaKanban:initIdeaKanban
    };
})();
