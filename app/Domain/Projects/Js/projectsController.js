leantime.projectsController = (function () {


    function countTickets()
    {

        jQuery("#sortableTicketKanban .column").each(function () {
            var counting = jQuery(this).find('.moveable').length;
            jQuery(this).find(' .count').text(counting);
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
                    jQuery(".showDialogOnLoad").show();
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

        jQuery(document).ready(function () {

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

        jQuery(el).find("input.sorter").each(function (index) {

            jQuery(this).val(sortCounter);
            sortCounter++;
        });

        if (el._sortableInstance) el._sortableInstance.destroy();
        el._sortableInstance = new Sortable(el, {
            animation: 150,
            onEnd: function () {
                sortCounter = 1;
                jQuery(el).find("input.sorter").each(function (index) {
                    jQuery(this).val(sortCounter);
                    sortCounter++;
                });
            }
        });

    };

    var initSelectFields = function () {

        jQuery(document).ready(function () {
            document.querySelectorAll("#todosettings select.colorChosen").forEach(function (el) {
                new SlimSelect({
                    select: el,
                    settings: { searchHighlight: false }
                });
            });
        });
    };

    var removeStatus = function (id) {

        jQuery("#todostatus-" + id).parent().remove();

    };

    var addToDoStatus = function (id) {

        var highestKey = -1;

        jQuery("#todosettings ul .statusList").each(function () {

            var keyInt = jQuery(this).find('.labelKey').val();

            if (parseInt(keyInt) >= parseInt(highestKey)) {
                highestKey = keyInt;
            }

        });

        var newKey = parseInt(highestKey) + 1;

        var statusCopy = jQuery(".newStatusTpl").clone();

        statusCopy.html(function (i, oldHTML) {
            return updatedContent = oldHTML.replaceAll('XXNEWKEYXX', newKey);
        });

        jQuery('#todoStatusList').append("<li>" + statusCopy.html() + "</li>");

        document.querySelectorAll("#todosettings select.colorChosen").forEach(function (el) {
            if (el.slim) el.slim.destroy();
        });
        leantime.projectsController.initSelectFields();
        var todoList = document.querySelector("#todoStatusList");
        if (todoList && todoList._sortableInstance) todoList._sortableInstance.destroy();
        leantime.projectsController.initTodoStatusSortable("#todoStatusList");

    };

    var readURL = function (input) {

        clearCroppie();

        if (input.files && input.files[0]) {
            var reader = new FileReader();

            var profileImg = jQuery('#projectAvatar');
            reader.onload = function (e) {
                //profileImg.attr('src', e.currentTarget.result);

                _uploadResult = profileImg
                    .croppie(
                        {
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
                        }
                    );

                _uploadResult.croppie(
                    'bind',
                    {
                        url: e.currentTarget.result
                    }
                );

                jQuery("#previousImage").hide();
            };

            reader.readAsDataURL(input.files[0]);
        }
    };

    var clearCroppie = function () {
        jQuery('#profileImg').croppie('destroy');
        jQuery("#previousImage").show();
    };

    var saveCroppie = function () {

        jQuery('#save-picture').addClass('running');

        jQuery('#profileImg').attr('src', leantime.appUrl + '/images/loaders/loader28.gif');
        _uploadResult.croppie(
            'result',
            {
                type: "blob",
                circle: false
            }
        ).then(
            function (result) {
                var formData = new FormData();
                formData.append('file', result);
                jQuery.ajax(
                    {
                        type: 'POST',
                        url: leantime.appUrl + '/api/projects',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (resp) {

                            jQuery('#save-picture').removeClass('running');

                            location.reload();
                        },
                        error:  function (err) {
                            console.log(err);
                        }
                    }
                );
            }
        );
    };


    var initGanttChart = function (projects, viewMode, readonly) {

        function htmlEntities(str)
        {
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        jQuery(document).ready(
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
                                    jQuery.ajax(
                                        {
                                            type: 'PATCH',
                                            url: leantime.appUrl + '/api/projects',
                                            data:
                                                {
                                                    id : entityId,
                                                    start:start,
                                                    end:end,
                                                    sortIndex: project._index+1,
                                            }
                                        }
                                    ).done(
                                        function () {
                                            //This is easier for now and MVP. Later this needs to be refactored to reload the list of tickets async

                                        }
                                    );
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

                                // POST to server using $.post or $.ajax
                                jQuery.ajax({
                                    type: 'POST',
                                    url: leantime.appUrl + '/api/projects',
                                    data: statusPostData

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

                jQuery("#ganttTimeControl").on(
                    "click",
                    "a",
                    function () {

                        var $btn = jQuery(this);
                        var mode = $btn.attr("data-value");
                        gantt_chart.change_view_mode(mode);
                        $btn.parent().parent().find('a').removeClass('active');
                        $btn.addClass('active');
                        var label = $btn.text();
                        jQuery(".viewText").text(label);
                    }
                );

                gantt_chart.change_view_mode(viewMode);

            }
        );

    };

    var setUpKanbanColumns = function () {

        jQuery(document).ready(function () {

            countTickets();
            jQuery(".filterBar .row-fluid").css("opacity", "1");

            var height = jQuery("html").height() - 250;

            jQuery("#sortableProjectKanban .column .contentInner").each(function () {
                if (jQuery(this).height() > height) {
                    height = jQuery(this).height();
                }
            });
            height = height + 50;
            jQuery("#sortableProjectKanban .column .contentInner").css("min-height", height);

        });

    };

    var initProjectsKanban = function (statusList) {

        jQuery("#sortableProjectKanban .projectBox").hover(function () {
            jQuery(this).css("background", "var(--kanban-card-hover)");
        },function () {
            jQuery(this).css("background", "var(--kanban-card-bg)");
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
                    tilt_direction(jQuery(evt.item));
                },

                onEnd: function (evt) {
                    evt.item.classList.remove('tilt');
                    jQuery("html").unbind('mousemove', jQuery(evt.item).data("move_handler"));
                    jQuery(evt.item).removeData("move_handler");

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

                    jQuery.ajax({
                        type: 'POST',
                        url: leantime.appUrl + '/api/projects',
                        data: statusPostData
                    });
                }
            });
        });

        function tilt_direction(item)
        {
            var left_pos = item.position().left,
                move_handler = function (e) {
                    if (e.pageX >= left_pos) {
                        item.addClass("right");
                        item.removeClass("left");
                    } else {
                        item.addClass("left");
                        item.removeClass("right");
                    }
                    left_pos = e.pageX;
                };
            jQuery("html").bind("mousemove", move_handler);
            item.data("move_handler", move_handler);
        }

        jQuery(".portlet")
            .addClass("ui-widget ui-widget-content ui-helper-clearfix ui-corner-all")
            .find(".portlet-header")
            .addClass("ui-widget-header ui-corner-all")
            .prepend("<span class='ui-icon ui-icon-minusthick portlet-toggle'></span>");

        jQuery(".portlet-toggle").click(function () {
            var icon = jQuery(this);
            icon.toggleClass("ui-icon-minusthick ui-icon-plusthick");
            icon.closest(".portlet").find(".portlet-content").toggle();
        });

    };

    var favoriteProject = function(id, element) {

        jQuery(element).addClass("go");
        if (jQuery(element).hasClass("isFavorite")) {
            leantime.reactionsController.removeReaction(
                'project',
                id,
            'favorite',
                function() {
                    jQuery(element).find("i").removeClass("fa-solid").addClass("fa-regular");
                    jQuery(element).removeClass("isFavorite");
                }
        );
        } else {
            leantime.reactionsController.addReactions(
                'project',
                id,
            'favorite',
                function() {
                    jQuery(element).find("i").removeClass("fa-regular").addClass("fa-solid");
                    jQuery(element).addClass("isFavorite");
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
