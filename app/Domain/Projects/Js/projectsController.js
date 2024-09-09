import jQuery from 'jquery';
import i18n from 'i18n';
import { appUrl } from 'js/app/core/instance-info.module';
import { getFormatFromSettings } from 'js/app/core/dateHelper.module';
import { updateUserViewSettings } from 'domain/Users/Js/usersRepository';
import { updateMilestoneDates } from 'domain/Tickets/Js/ticketsRepository';
import { removeReaction, addReactions } from 'domain/Reactions/Js/reactionsController';
import Gantt from 'js/libs/simpleGantt/frappe-gantt';

export const countTickets = function ()
{
    jQuery("#sortableTicketKanban .column").each(function () {
        var counting = jQuery(this).find('.moveable').length;
        jQuery(this).find(' .count').text(counting);
    });
}

export const initDates = function () {
    jQuery(".projectDateFrom, .projectDateTo").datepicker(
        {
            dateFormat: getFormatFromSettings("dateformat", "jquery"),
            dayNames: i18n.__("language.dayNames").split(","),
            dayNamesMin:  i18n.__("language.dayNamesMin").split(","),
            dayNamesShort: i18n.__("language.dayNamesShort").split(","),
            monthNames: i18n.__("language.monthNames").split(","),
            currentText: i18n.__("language.currentText"),
            closeText: i18n.__("language.closeText"),
            buttonText: i18n.__("language.buttonText"),
            isRTL: i18n.__("language.isRTL") === "true" ? 1 : 0,
            nextText: i18n.__("language.nextText"),
            prevText: i18n.__("language.prevText"),
            weekHeader: i18n.__("language.weekHeader"),
        }
    );
};

export const initProjectTabs = function () {
    jQuery('.projectTabs').tabs();
};

export const initProgressBar = function (percentage) {

    jQuery("#progressbar").progressbar({
        value: percentage
    });

};


export const initProjectTable = function () {

    jQuery(document).ready(function () {

        var size = 100;

        var allProjects = jQuery("#allProjectsTable").DataTable({
            "language": {
                "decimal":        i18n.__("datatables.decimal"),
                "emptyTable":     i18n.__("datatables.emptyTable"),
                "info":           i18n.__("datatables.info"),
                "infoEmpty":      i18n.__("datatables.infoEmpty"),
                "infoFiltered":   i18n.__("datatables.infoFiltered"),
                "infoPostFix":    i18n.__("datatables.infoPostFix"),
                "thousands":      i18n.__("datatables.thousands"),
                "lengthMenu":     i18n.__("datatables.lengthMenu"),
                "loadingRecords": i18n.__("datatables.loadingRecords"),
                "processing":     i18n.__("datatables.processing"),
                "search":         i18n.__("datatables.search"),
                "zeroRecords":    i18n.__("datatables.zeroRecords"),
                "paginate": {
                    "first":      i18n.__("datatables.first"),
                    "last":       i18n.__("datatables.last"),
                    "next":       i18n.__("datatables.next"),
                    "previous":   i18n.__("datatables.previous"),
                },
                "aria": {
                    "sortAscending":  i18n.__("datatables.sortAscending"),
                    "sortDescending":i18n.__("datatables.sortDescending"),
                }

            },
            "dom": '<"top">rt<"bottom"ilp><"clear">',
            "searching": false,
            "displayLength":100
        });

    });

};

export const initTodoStatusSortable = function (element) {
    var sortCounter = 1;

    jQuery(element).find("input.sorter").each(function (index) {

        jQuery(this).val(sortCounter);
        sortCounter++;
    });

    jQuery(element).sortable({
        stop: function ( event, ui ) {

            sortCounter = 1;
            jQuery(element).find("input.sorter").each(function (index) {
                jQuery(this).val(sortCounter);
                sortCounter++;
            });
        }
    });
};

export const initSelectFields = function () {
    jQuery(document).ready(function () {

        jQuery("#todosettings select.colorChosen").on('chosen:ready', function (e, params) {

            var id = jQuery(this).attr('id').replace("-", "_");

            jQuery("#" + id + "_chosen a span").removeClass();
            jQuery("#" + id + "_chosen a span").addClass(params.selected);

        }).chosen({
            disable_search_threshold: 10
        });

        jQuery("#todosettings select.colorChosen").on('change', function (evt, params) {

            var id = jQuery(this).attr('id').replace("-", "_");

            jQuery("#" + id + "_chosen a span").removeClass();
            jQuery("#" + id + "_chosen a span").addClass(params.selected);

        });
    });
};

export const removeStatus = function (id) {
    jQuery("#todostatus-" + id).parent().remove();
};

export const addToDoStatus = function (id) {
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

    jQuery("#todosettings select.colorChosen").chosen("destroy");
    initSelectFields();
    jQuery("#todoStatusList").sortable("destroy");
    initTodoStatusSortable("#todoStatusList");

};

export const readURL = function (input) {
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

export const clearCroppie = function () {
    jQuery('#profileImg').croppie('destroy');
    jQuery("#previousImage").show();
};

export const saveCroppie = function () {
    jQuery('#save-picture').addClass('running');

    jQuery('#profileImg').attr('src', appUrl + '/images/loaders/loader28.gif');
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
                    url: appUrl + '/api/projects',
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

export const initGanttChart = function (projects, viewMode, readonly) {
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
                        bar_corner_radius: 5,
                        arrow_curve: 5,
                        padding:20,
                        view_mode: 'Month',
                        date_format: i18n.__("language.momentJSDate"),
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
                                updateMilestoneDates(entityId, start, end, project._index);
                            } else {
                                jQuery.ajax(
                                    {
                                        type: 'PATCH',
                                        url: appUrl + '/api/projects',
                                        data:
                                            {
                                                id : entityId,
                                                start:start,
                                                end:end,
                                                sortIndex: project._index
                                        }
                                    }
                                ).done(
                                    function () {
                                        //This is easier for now and MVP. Later this needs to be refactored to reload the list of tickets async

                                    }
                                );
                            }

                        },
                        on_sort_change: function (projects) {

                            var statusPostData = {
                                action: "ganttSort",
                                payload: {}
                            };

                            for (var i = 0; i < projects.length; i++) {
                                statusPostData.payload[projects[i].id] = projects[i]._index;
                            }

                            // POST to server using $.post or $.ajax
                            jQuery.ajax({
                                type: 'POST',
                                url: appUrl + '/api/projects',
                                data: statusPostData

                            });

                        },
                        on_progress_change: function (project, progress) {

                        },
                        on_view_change: function (mode) {

                            updateUserViewSettings("projectGantt", mode);

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

                        },
                        on_view_change: function (mode) {

                            updateUserViewSettings("projectGantt", mode);

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

export const setUpKanbanColumns = function () {
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

export const initProjectsKanban = function (statusList) {
    jQuery("#sortableProjectKanban .projectBox").hover(function () {
        jQuery(this).css("background", "var(--kanban-card-hover)");
    },function () {
        jQuery(this).css("background", "var(--kanban-card-bg)");
    });

    var position_updated = false;

    jQuery("#sortableProjectKanban .contentInner").sortable({
        connectWith: ".contentInner",
        items: "> .moveable",
        tolerance: 'intersect',
        placeholder: "ui-state-highlight",
        forcePlaceholderSize: true,
        cancel: ".portlet-toggle,:input,a,input",
        distance: 25,

        start: function (event, ui) {
            ui.item.addClass('tilt');
            tilt_direction(ui.item);
        },
        stop: function (event, ui) {
            ui.item.removeClass("tilt");
            jQuery("html").unbind('mousemove', ui.item.data("move_handler"));
            ui.item.removeData("move_handler");

            countTickets();

            var statusPostData = {
                action: "sortIndex",
                payload: {},
                handler: ui.item[0].id
            };

            for (var i = 0; i < statusList.length; i++) {
                if (jQuery(".contentInner.status_" + statusList[i]).length) {
                    statusPostData.payload[statusList[i]] = jQuery(".contentInner.status_" + statusList[i]).sortable('serialize');
                }
            }

            // POST to server using $.post or $.ajax
            jQuery.ajax({
                type: 'POST',
                url: appUrl + '/api/projects',
                data: statusPostData

            });

        }
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

export const favoriteProject = function(id, element) {
    jQuery(element).addClass("go");
    if (jQuery(element).hasClass("isFavorite")) {
        removeReaction(
            'project',
            id,
        'favorite',
            function() {
                jQuery(element).find("i").removeClass("fa-solid").addClass("fa-regular");
                jQuery(element).removeClass("isFavorite");
            }
    );
    } else {
        addReactions(
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
export default {
    initDates: initDates,
    initProjectTabs: initProjectTabs,
    initProgressBar: initProgressBar,
    initProjectTable: initProjectTable,
    initTodoStatusSortable: initTodoStatusSortable,
    initSelectFields: initSelectFields,
    removeStatus: removeStatus,
    addToDoStatus: addToDoStatus,
    saveCroppie: saveCroppie,
    clearCroppie: clearCroppie,
    readURL: readURL,
    initGanttChart: initGanttChart,
    setUpKanbanColumns: setUpKanbanColumns,
    initProjectsKanban: initProjectsKanban,
    favoriteProject: favoriteProject
};
