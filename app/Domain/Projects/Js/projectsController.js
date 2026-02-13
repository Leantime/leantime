leantime.projectsController = (function () {


    function countTickets()
    {

        document.querySelectorAll("#sortableTicketKanban .column").forEach(function (column) {
            var counting = column.querySelectorAll('.moveable').length;
            var countEl = column.querySelector('.count');
            if (countEl) countEl.textContent = counting;
        });

    }

    //Functions

    var initDates = function () {

        jQuery(".projectDateFrom, .projectDateTo").datepicker(
            {
                dateFormat: leantime.dateHelper.getFormatFromSettings("dateformat", "jquery"),
                dayNames: leantime.i18n.__("language.dayNames").split(","),
                dayNamesMin:  leantime.i18n.__("language.dayNamesMin").split(","),
                dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
                monthNames: leantime.i18n.__("language.monthNames").split(","),
                currentText: leantime.i18n.__("language.currentText"),
                closeText: leantime.i18n.__("language.closeText"),
                buttonText: leantime.i18n.__("language.buttonText"),
                isRTL: leantime.i18n.__("language.isRTL") === "true" ? 1 : 0,
                nextText: leantime.i18n.__("language.nextText"),
                prevText: leantime.i18n.__("language.prevText"),
                weekHeader: leantime.i18n.__("language.weekHeader"),
            }
        );
    };

    var initProjectTabs = function () {
        jQuery('.projectTabs').tabs();
    };

    var initDuplicateProjectModal = function () {

        var regularModelConfig = {
            sizes: {
                minW: 450,
                minH: 350
            },
            resizable: true,
            autoSizable: true,
            callbacks: {
                afterShowCont: function () {
                    document.querySelectorAll(".showDialogOnLoad").forEach(function (el) {
                        el.style.display = '';
                    });
                    initDates();
                    jQuery(".duplicateProjectModal, .formModal").nyroModal(regularModelConfig);
                },
                beforeClose: function () {
                    location.reload();
                }
            }
        };

        jQuery(".duplicateProjectModal").nyroModal(regularModelConfig);

    };

    var initProgressBar = function (percentage) {

        jQuery("#progressbar").progressbar({
            value: percentage
        });

    };


    var initProjectTable = function () {

        document.addEventListener('DOMContentLoaded', function () {

            var size = 100;

            var allProjects = jQuery("#allProjectsTable").DataTable({
                "language": {
                    "decimal":        leantime.i18n.__("datatables.decimal"),
                    "emptyTable":     leantime.i18n.__("datatables.emptyTable"),
                    "info":           leantime.i18n.__("datatables.info"),
                    "infoEmpty":      leantime.i18n.__("datatables.infoEmpty"),
                    "infoFiltered":   leantime.i18n.__("datatables.infoFiltered"),
                    "infoPostFix":    leantime.i18n.__("datatables.infoPostFix"),
                    "thousands":      leantime.i18n.__("datatables.thousands"),
                    "lengthMenu":     leantime.i18n.__("datatables.lengthMenu"),
                    "loadingRecords": leantime.i18n.__("datatables.loadingRecords"),
                    "processing":     leantime.i18n.__("datatables.processing"),
                    "search":         leantime.i18n.__("datatables.search"),
                    "zeroRecords":    leantime.i18n.__("datatables.zeroRecords"),
                    "paginate": {
                        "first":      leantime.i18n.__("datatables.first"),
                        "last":       leantime.i18n.__("datatables.last"),
                        "next":       leantime.i18n.__("datatables.next"),
                        "previous":   leantime.i18n.__("datatables.previous"),
                    },
                    "aria": {
                        "sortAscending":  leantime.i18n.__("datatables.sortAscending"),
                        "sortDescending":leantime.i18n.__("datatables.sortDescending"),
                    }

                },
                "dom": '<"top">rt<"bottom"ilp><"clear">',
                "searching": false,
                "displayLength":100
            });

        });

    };

    var initTodoStatusSortable = function (element) {
        var sortCounter = 1;
        var el = typeof element === 'string' ? document.querySelector(element) : element;

        el.querySelectorAll("input.sorter").forEach(function (input) {
            input.value = sortCounter;
            sortCounter++;
        });

        if (el._sortableInstance) el._sortableInstance.destroy();
        el._sortableInstance = new Sortable(el, {
            animation: 150,
            onEnd: function () {
                sortCounter = 1;
                el.querySelectorAll("input.sorter").forEach(function (input) {
                    input.value = sortCounter;
                    sortCounter++;
                });
            }
        });

    };

    var initSelectFields = function () {

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll("#todosettings select.colorChosen").forEach(function (el) {
                new SlimSelect({
                    select: el,
                    settings: { searchHighlight: false }
                });
            });
        });
    };

    var removeStatus = function (id) {

        var statusEl = document.querySelector("#todostatus-" + id);
        if (statusEl && statusEl.parentElement) {
            statusEl.parentElement.remove();
        }

    };

    var addToDoStatus = function (id) {

        var highestKey = -1;

        document.querySelectorAll("#todosettings ul .statusList").forEach(function (statusItem) {

            var labelKeyEl = statusItem.querySelector('.labelKey');
            if (labelKeyEl) {
                var keyInt = labelKeyEl.value;

                if (parseInt(keyInt) >= parseInt(highestKey)) {
                    highestKey = keyInt;
                }
            }

        });

        var newKey = parseInt(highestKey) + 1;

        var statusCopyEl = document.querySelector(".newStatusTpl");
        var statusCopyHTML = statusCopyEl.innerHTML.replaceAll('XXNEWKEYXX', newKey);

        var todoStatusList = document.querySelector('#todoStatusList');
        todoStatusList.insertAdjacentHTML('beforeend', "<li>" + statusCopyHTML + "</li>");

        document.querySelectorAll("#todosettings select.colorChosen").forEach(function (el) {
            if (el.slim) el.slim.destroy();
        });
        leantime.projectsController.initSelectFields();
        var todoList = document.querySelector("#todoStatusList");
        if (todoList && todoList._sortableInstance) todoList._sortableInstance.destroy();
        leantime.projectsController.initTodoStatusSortable("#todoStatusList");

    };

    var _croppieInstance = null;

    var readURL = function (input) {

        clearCroppie();

        if (input.files && input.files[0]) {
            var reader = new FileReader();

            var profileImgEl = document.querySelector('#projectAvatar');
            reader.onload = function (e) {

                _croppieInstance = new Croppie(profileImgEl, {
                    enableExif: true,
                    viewport: {
                        width: 200,
                        height: 200,
                        type: 'rectangle'
                    },
                    boundary: {
                        width: 250,
                        height: 250
                    }
                });

                _croppieInstance.bind({
                    url: e.currentTarget.result
                });

                var previousImage = document.querySelector("#previousImage");
                if (previousImage) previousImage.style.display = 'none';
            };

            reader.readAsDataURL(input.files[0]);
        }
    };

    var clearCroppie = function () {
        var profileImgEl = document.querySelector('#profileImg');
        if (_croppieInstance) {
            _croppieInstance.destroy();
            _croppieInstance = null;
        }
        var previousImage = document.querySelector("#previousImage");
        if (previousImage) previousImage.style.display = '';
    };

    var saveCroppie = function () {

        var savePictureBtn = document.querySelector('#save-picture');
        if (savePictureBtn) savePictureBtn.classList.add('running');

        var profileImgEl = document.querySelector('#profileImg');
        if (profileImgEl) profileImgEl.setAttribute('src', leantime.appUrl + '/images/loaders/loader28.gif');

        _croppieInstance.result({
            type: "blob",
            circle: false
        }).then(
            function (result) {
                var formData = new FormData();
                formData.append('file', result);
                fetch(leantime.appUrl + '/api/projects', {
                    method: 'POST',
                    body: formData,
                    credentials: 'include',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(function (resp) {
                    if (savePictureBtn) savePictureBtn.classList.remove('running');
                    location.reload();
                }).catch(function (err) {
                    console.log(err);
                });
            }
        );
    };


    var initGanttChart = function (projects, viewMode, readonly) {

        function htmlEntities(str)
        {
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        document.addEventListener('DOMContentLoaded',
            function () {

                if (readonly === false) {
                    var gantt_chart = new Gantt(
                        "#gantt",
                        projects,
                        {
                            header_height: 55,
                            column_width: 20,
                            step: 24,
                            view_modes: ['Day', 'Week', 'Month'],
                            bar_height: 40,
                            static_progress_indicator: true,
                            bar_corner_radius: 10,
                            arrow_curve: 10,
                            padding:20,
                            view_mode: 'Month',
                            date_format: leantime.i18n.__("language.momentJSDate"),
                            language: 'en', // or 'es', 'it', 'ru', 'ptBr', 'fr', 'tr', 'zh'
                            additional_rows: 5,
                            custom_popup_html: function (project) {

                                // the task object will contain the updated
                                // dates and progress value
                                var end_date = project._end;

                                var popUpHTML = '<div class="details-container" style="min-width:600px;"> ';

                                if (project.projectName !== undefined) {
                                    popUpHTML +=  '<h3><b>' + project.name + '</b></h3>';
                                }

                                popUpHTML += '<h4>' + htmlEntities(project.name) + '</a></h4><br /> ';

                                popUpHTML += '</div>';

                                return popUpHTML;
                            },
                            on_click: function (project) {
                                //_initModals();
                            },
                            on_date_change: function (project, start, end) {

                                var idParts = project.id.split("-");

                                let entityId = 0;
                                let entityType = "";

                                if (idParts.length > 1) {
                                    if (idParts[0] == "ticket") {
                                        entityId = idParts[1];
                                        entityType = "ticket"
                                    } else if (idParts[0] == "pgm") {
                                        entityId = idParts[1];
                                        entityType = "project"
                                    }
                                } else {
                                    entityId = idParts;
                                }


                                if (entityType == "ticket") {
                                    leantime.ticketsRepository.updateMilestoneDates(entityId, start, end, project._index+1);
                                } else {
                                    fetch(leantime.appUrl + '/api/projects', {
                                        method: 'PATCH',
                                        body: new URLSearchParams({
                                            id: entityId,
                                            start: start,
                                            end: end,
                                            sortIndex: project._index + 1,
                                        }),
                                        credentials: 'include',
                                        headers: {
                                            'Content-Type': 'application/x-www-form-urlencoded',
                                            'X-Requested-With': 'XMLHttpRequest'
                                        }
                                    });
                                }

                                //leantime.ticketsRepository.updateMilestoneDates(task.id, start, end, task._index);
                                //_initModals();

                            },
                            on_sort_change: function (projects) {

                                var statusPostData = {
                                    action: "ganttSort",
                                    payload: {}
                                };

                                for (var i = 0; i < projects.length; i++) {
                                    statusPostData.payload[projects[i].id] = projects[i]._index+1;
                                }

                                fetch(leantime.appUrl + '/api/projects', {
                                    method: 'POST',
                                    body: new URLSearchParams(flattenForURLSearchParams(statusPostData)),
                                    credentials: 'include',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded',
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                });

                            },
                            on_progress_change: function (project, progress) {

                                //_initModals();
                            },
                            on_view_change: function (mode) {

                                leantime.usersRepository.updateUserViewSettings("projectGantt", mode);

                            },
                            on_popup_show: function (project) {

                            }
                        }
                    );
                } else {
                    var gantt_chart = new Gantt(
                        "#gantt",
                        projects,
                        {
                            readonlyGantt: true,
                            resizing: false,
                            progress: false,
                            is_draggable: false,
                            custom_popup_html: function (project) {
                                // the task object will contain the updated
                                // dates and progress value
                                var end_date = task._end;

                                var popUpHTML = '<div class="details-container" style="min-width:600px;"> ';

                                if (task.projectName !== undefined) {
                                    popUpHTML +=  '<h3><b>' + project.name + '</b></h3>';
                                }

                                popUpHTML += '<h4>' + htmlEntities(project.name) + '</a></h4><br /> ';

                                popUpHTML += '</div>';

                                return popUpHTML;
                            },
                            on_click: function (project) {

                            },
                            on_date_change: function (project, start, end) {


                            },
                            on_progress_change: function (project, progress) {

                                //_initModals();
                            },
                            on_view_change: function (mode) {

                                leantime.usersRepository.updateUserViewSettings("projectGantt", mode);

                            }
                        }
                    );
                }

                var ganttTimeControl = document.querySelector("#ganttTimeControl");
                if (ganttTimeControl) {
                    ganttTimeControl.addEventListener("click", function (e) {
                        var link = e.target.closest("a");
                        if (!link) return;

                        var mode = link.getAttribute("data-value");
                        gantt_chart.change_view_mode(mode);
                        ganttTimeControl.querySelectorAll('a').forEach(function (a) {
                            a.classList.remove('active');
                        });
                        link.classList.add('active');
                        var label = link.textContent;
                        document.querySelectorAll(".viewText").forEach(function (el) {
                            el.textContent = label;
                        });
                    });
                }

                gantt_chart.change_view_mode(viewMode);

            }
        );

    };

    /**
     * Flatten a nested object into key-value pairs suitable for URLSearchParams.
     * E.g. { action: "ganttSort", payload: { "pgm-1": 1 } }
     * becomes { "action": "ganttSort", "payload[pgm-1]": "1" }
     */
    function flattenForURLSearchParams(obj, prefix) {
        var params = {};
        for (var key in obj) {
            if (!obj.hasOwnProperty(key)) continue;
            var fullKey = prefix ? prefix + '[' + key + ']' : key;
            if (typeof obj[key] === 'object' && obj[key] !== null) {
                Object.assign(params, flattenForURLSearchParams(obj[key], fullKey));
            } else {
                params[fullKey] = obj[key];
            }
        }
        return params;
    }

    var setUpKanbanColumns = function () {

        document.addEventListener('DOMContentLoaded', function () {

            countTickets();
            var filterBarRow = document.querySelector(".filterBar .row-fluid");
            if (filterBarRow) filterBarRow.style.opacity = "1";

            var height = document.documentElement.scrollHeight - 250;

            document.querySelectorAll("#sortableProjectKanban .column .contentInner").forEach(function (innerEl) {
                if (innerEl.offsetHeight > height) {
                    height = innerEl.offsetHeight;
                }
            });
            height = height + 50;
            document.querySelectorAll("#sortableProjectKanban .column .contentInner").forEach(function (innerEl) {
                innerEl.style.minHeight = height + "px";
            });

        });

    };

    var initProjectsKanban = function (statusList) {

        document.querySelectorAll("#sortableProjectKanban .projectBox").forEach(function (box) {
            box.addEventListener("mouseenter", function () {
                this.style.background = "var(--kanban-card-hover)";
            });
            box.addEventListener("mouseleave", function () {
                this.style.background = "var(--kanban-card-bg)";
            });
        });

        var position_updated = false;

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

        document.querySelectorAll("#sortableProjectKanban .contentInner").forEach(function (contentInner) {
            if (contentInner._sortableInstance) contentInner._sortableInstance.destroy();
            contentInner._sortableInstance = new Sortable(contentInner, {
                group: 'project-kanban',
                draggable: '.moveable',
                ghostClass: 'ui-state-highlight',
                filter: '.portlet-toggle, input, a, select, textarea',
                preventOnFilter: false,
                animation: 150,
                delay: 25,
                delayOnTouchOnly: true,

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

                    countTickets();

                    var statusPostData = {
                        action: "sortIndex",
                        payload: {},
                        handler: evt.item.id
                    };

                    for (var i = 0; i < statusList.length; i++) {
                        var col = document.querySelector(".contentInner.status_" + statusList[i]);
                        if (col) {
                            statusPostData.payload[statusList[i]] = serializeSortable(col);
                        }
                    }

                    fetch(leantime.appUrl + '/api/projects', {
                        method: 'POST',
                        body: new URLSearchParams(flattenForURLSearchParams(statusPostData)),
                        credentials: 'include',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
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
            portlet.classList.add("ui-widget", "ui-widget-content", "ui-helper-clearfix", "ui-corner-all");
            var header = portlet.querySelector(".portlet-header");
            if (header) {
                header.classList.add("ui-widget-header", "ui-corner-all");
                header.insertAdjacentHTML("afterbegin", "<span class='ui-icon ui-icon-minusthick portlet-toggle'></span>");
            }
        });

        document.querySelectorAll(".portlet-toggle").forEach(function (toggle) {
            toggle.addEventListener("click", function () {
                this.classList.toggle("ui-icon-minusthick");
                this.classList.toggle("ui-icon-plusthick");
                var portletContent = this.closest(".portlet").querySelector(".portlet-content");
                if (portletContent) {
                    portletContent.style.display = portletContent.style.display === 'none' ? '' : 'none';
                }
            });
        });

    };

    var favoriteProject = function(id, element) {

        element.classList.add("go");
        if (element.classList.contains("isFavorite")) {
            leantime.reactionsController.removeReaction(
                'project',
                id,
            'favorite',
                function() {
                    var icon = element.querySelector("i");
                    if (icon) {
                        icon.classList.remove("fa-solid");
                        icon.classList.add("fa-regular");
                    }
                    element.classList.remove("isFavorite");
                }
        );
        } else {
            leantime.reactionsController.addReactions(
                'project',
                id,
            'favorite',
                function() {
                    var icon = element.querySelector("i");
                    if (icon) {
                        icon.classList.remove("fa-regular");
                        icon.classList.add("fa-solid");
                    }
                    element.classList.add("isFavorite");
                }
            );
        }

    }

    // Make public what you want to have public, everything else is private
    return {
        initDates:initDates,
        initProjectTabs:initProjectTabs,
        initProgressBar:initProgressBar,
        initProjectTable:initProjectTable,
        initDuplicateProjectModal:initDuplicateProjectModal,
        initTodoStatusSortable:initTodoStatusSortable,
        initSelectFields:initSelectFields,
        removeStatus:removeStatus,
        addToDoStatus:addToDoStatus,
        saveCroppie:saveCroppie,
        clearCroppie:clearCroppie,
        readURL:readURL,
        initGanttChart:initGanttChart,
        setUpKanbanColumns:setUpKanbanColumns,
        initProjectsKanban:initProjectsKanban,
        favoriteProject:favoriteProject

    };
})();
