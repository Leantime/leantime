leantime.ticketsController = (function () {

    //Variables

    var milestoneModalConfig = {
        sizes: {
            minW: 500,
            minH: 750
        },
        resizable: true,
        autoSizable: true,
        callbacks: {
            beforeShowCont: function() {
                jQuery(".showDialogOnLoad").show();
            },
            afterShowCont: function () {
                jQuery(".showDialogOnLoad").show();
                _initSprintDates();
                _initSimpleColorPicker();
                jQuery(".formModal, #commentForm, .deleteComment").nyroModal(milestoneModalConfig);
            },
            beforeClose: function () {

                location.reload();
            }


        },
        titleFromIframe: true
    };

    //Constructor
    (function () {
        jQuery(document).ready(
            function () {
                _initDueDateTimePickers();
                _initDates();
                _initModals();

                _initEffortDropdown();
                _initMilestoneDropdown();
                _initStatusDropdown();
                _initUserDropdown();
                _initSprintDropdown();

                _initTicketEditor();
                _initToolTips();

            }
        );

    })();

    //Functions

    var updateRemaining = function (element, id) {
        var value = jQuery(element).val();
        leantime.ticketsRepository.updateRemainingHours(
            id, value, function () {
                jQuery.jGrowl("Remaining Hours updated!");
            }
        );

    };

    var triggerMilestoneModal = function (id) {
        jQuery.nmManual('/tickets/editMilestone/'+id, milestoneModalConfig);

    };

    var openMilestoneModalManually = function (url) {
        jQuery.nmManual(url, milestoneModalConfig);
    };

    var toggleFilterBar = function () {
        jQuery(".filterBar").toggle();
    };

    var initGanttChart = function (tasks, viewMode) {

        jQuery(document).ready(
            function () {

                var gantt_chart = new Gantt(
                    "#gantt", tasks, {
                        custom_popup_html: function (task) {
                            // the task object will contain the updated
                            // dates and progress value
                            var end_date = task._end.format(leantime.i18n.__("language.momentJSDate"));
                            return '<div class="details-container"> ' +
                            '<h4><a href="/tickets/editMilestone/'+task.id+'" class="milestoneModal">'+task.name+'</a></h4><br /> ' +
                            '<p>'+leantime.i18n.__("text.expected_to_finish_by")+' <strong>'+end_date+'</strong><br /> ' +
                            ''+Math.round(task.progress)+'%</p> ' +
                            '<a href="/tickets/editMilestone/'+task.id+'" class="milestoneModal"><span class="fa fa-map"></span> '+leantime.i18n.__("links.edit_milestone") +'</a> | ' +
                            '<a href="/tickets/showKanban&milestone='+task.id+'"><span class="iconfa-pushpin"></span> '+leantime.i18n.__("links.view_todos")+'</a> ' +

                            '</div>';
                        },
                        on_click: function (task) {

                        },
                        on_date_change: function (task, start, end) {

                            leantime.ticketsRepository.updateMilestoneDates(task.id, start, end);
                            _initModals();

                        },
                        on_progress_change: function (task, progress) {

                            //_initModals();
                        },
                        on_view_change: function (mode) {

                            leantime.usersRepository.updateUserViewSettings("roadmap", mode);
                            _initModals();
                        }
                    }
                ); 

                jQuery("#ganttTimeControl").on(
                    "click", "a", function () {

                        var $btn =jQuery(this);
                        var mode = $btn.attr("data-value");
                        gantt_chart.change_view_mode(mode);
                        $btn.parent().parent().find('a').removeClass('active');
                        $btn.addClass('active');
                    }
                );

                gantt_chart.change_view_mode(viewMode);

            }
        );

    };

    var _initDates = function () {

        jQuery(".dates").datepicker(
            {
                dateFormat:  leantime.i18n.__("language.dateformat"),
                dayNames: leantime.i18n.__("language.dayNames").split(","),
                dayNamesMin:  leantime.i18n.__("language.dayNamesMin").split(","),
                dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
                monthNames: leantime.i18n.__("language.monthNames").split(","),
                currentText: leantime.i18n.__("language.currentText"),
                closeText: leantime.i18n.__("language.closeText"),
                buttonText: leantime.i18n.__("language.buttonText"),
                isRTL: leantime.i18n.__("language.isRTL"),
                nextText: leantime.i18n.__("language.nextText"),
                prevText: leantime.i18n.__("language.prevText"),
                weekHeader: leantime.i18n.__("language.weekHeader"),
            }
        );
    };

    var initModals = function () {
        _initModals();
    };

    var _initSprintDates = function () {

        Date.prototype.addDays = function (days) {
            this.setDate(this.getDate() + days);
            return this;
        };
        jQuery.datepicker.setDefaults(
            { beforeShow: function (i) {
                if (jQuery(i).attr('readonly')) { return false; } } }
        );

        var dateFormat = leantime.i18n.__("language.dateformat").split(","),
            from = jQuery("#sprintStart")
                .datepicker(
                    {
                        numberOfMonths: 1,
                        dateFormat:  leantime.i18n.__("language.dateformat"),
                        dayNames: leantime.i18n.__("language.dayNames").split(","),
                        dayNamesMin:  leantime.i18n.__("language.dayNamesMin").split(","),
                        dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
                        monthNames: leantime.i18n.__("language.monthNames").split(","),
                        currentText: leantime.i18n.__("language.currentText"),
                        closeText: leantime.i18n.__("language.closeText"),
                        buttonText: leantime.i18n.__("language.buttonText"),
                        isRTL: leantime.i18n.__("language.isRTL"),
                        nextText: leantime.i18n.__("language.nextText"),
                        prevText: leantime.i18n.__("language.prevText"),
                        weekHeader: leantime.i18n.__("language.weekHeader"),
                    }
                )
                .on(
                    "change", function () {
                        to.datepicker("option", "minDate", getDate(this));
                        var newEndDate = getDate(this).addDays(13);
                        to.datepicker('setDate', newEndDate); //set date

                    }
                ),

            to = jQuery("#sprintEnd").datepicker(
                {
                    defaultDate: "+1w",
                    numberOfMonths: 1,
                    dateFormat:  leantime.i18n.__("language.dateformat"),
                    dayNames: leantime.i18n.__("language.dayNames").split(","),
                    dayNamesMin:  leantime.i18n.__("language.dayNamesMin").split(","),
                    dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
                    monthNames: leantime.i18n.__("language.monthNames").split(","),
                    currentText: leantime.i18n.__("language.currentText"),
                    closeText: leantime.i18n.__("language.closeText"),
                    buttonText: leantime.i18n.__("language.buttonText"),
                    isRTL: leantime.i18n.__("language.isRTL"),
                    nextText: leantime.i18n.__("language.nextText"),
                    prevText: leantime.i18n.__("language.prevText"),
                    weekHeader: leantime.i18n.__("language.weekHeader"),
                }
            )
            .on(
                "change", function () {
                    from.datepicker("option", "maxDate", getDate(this));
                }
            );

        function getDate( element )
        {
            var date;
            try {
                date = jQuery.datepicker.parseDate(dateFormat, element.value);
            } catch( error ) {
                date = null;
                console.log(error);
            }
            console.log(date);
            return date;
        }
    };

    var _initMilestoneDates = function () {
        var dateFormat = "mm/dd/yy",
            from = jQuery("#milestoneEditFrom")
                .datepicker(
                    {
                        numberOfMonths: 1
                    }
                )
                .on(
                    "change", function () {
                        to.datepicker("option", "minDate", getDate(this));
                    }
                ),
            to = jQuery("#milestoneEditTo").datepicker(
                {
                    defaultDate: "+1w",
                    numberOfMonths: 1
                }
            )
                .on(
                    "change", function () {
                        from.datepicker("option", "maxDate", getDate(this));
                    }
                );

        function getDate( element )
        {
            var date;
            try {
                date = jQuery.datepicker.parseDate(dateFormat, element.value);
            } catch( error ) {
                date = null;
                console.log(error);
            }
            console.log(date);
            return date;
        }
    };

    var _initToolTips = function () {
        jQuery('[data-toggle="tooltip"]').tooltip();
    };

    var _initModals = function () {

        var regularModelConfig = {
            callbacks: {
                afterShowCont: function () {
                    jQuery(".showDialogOnLoad").show();
                    _initDates();
                    jQuery(".regularModal, .formModal").nyroModal(regularModelConfig);
                }
            }
        };

        jQuery(".regularModal").nyroModal(regularModelConfig);

        var editLabelModalConfig = {
            callbacks: {
                afterShowCont: function () {

                    jQuery(".editLabelModal").nyroModal(editLabelModalConfig);
                },
                beforeClose: function () {
                    location.reload();
                }
            }
        };

        jQuery(".editLabelModal").nyroModal(editLabelModalConfig);

        var sprintModalConfig = {
            sizes: {
                minW: 300,
                minH: 350
            },
            resizable: true,
            autoSizable: true,
            callbacks: {
                afterShowCont: function () {
                    _initSprintDates();
                    _initToolTips();

                    jQuery(".formModal").nyroModal(sprintModalConfig);
                },
                beforeClose: function () {
                    location.reload();
                }


            },
            titleFromIframe: true
        };
        jQuery(".sprintModal").nyroModal(sprintModalConfig);

        var modalConfig = {
            sizes: {
                minW: 500,
                minH: 750
            },
            resizable: true,
            autoSizable: true,
            callbacks: {
                beforeShowCont: function() {
                    jQuery(".showDialogOnLoad").show();
                },
                afterShowCont: function () {
                    jQuery(".showDialogOnLoad").show();
                    _initMilestoneDates();
                    _initSimpleColorPicker();
                    jQuery(".formModal, #commentForm, .deleteComment").nyroModal(modalConfig);
                },
                beforeClose: function () {

                    location.reload();
                }


            },
            titleFromIframe: true
        };

        jQuery(".milestoneModal").nyroModal(modalConfig);


    };

    var _initSprintPopover = function () {
        jQuery('.sprintPopover').popover(
            {
                template:'<div class="popover sprintPopoverContainer" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'

            }
        );

        jQuery("body").on(
            "click", ".sprintPopoverContainer input", function () {

                var ticket = jQuery(this).attr("name").split("_");
                var val = jQuery(this).val();

                jQuery.ajax(
                    {
                        type: 'PATCH',
                        url: '/api/tickets',
                        data:
                        {
                            id : ticket[1],
                            sprint:val
                        }
                    }
                ).done(
                    function () {

                    }
                );

            }
        );
    };

    var _initEffortDropdown = function() {

        var storyPointLabels = {
            '1': 'XS',
            '2': 'S',
            '3':"M",
            '5':"L",
            '8' : "XL",
            '13': "XXL"
        };

        jQuery("body").on("click", ".effortDropdown .dropdown-menu a", function () {

            var dataValue = jQuery(this).attr("data-value").split("_");

            if (dataValue.length === 2) {

                var ticketId = dataValue[0];
                var effortId = dataValue[1];

                jQuery.ajax(
                    {
                        type: 'PATCH',
                        url: '/api/tickets',
                        data:
                            {
                                id: ticketId,
                                storypoints: effortId
                            }
                    }
                ).done(
                    function () {
                        jQuery("#effortDropdownMenuLink" + ticketId + " span.text").text(storyPointLabels[effortId]);
                        jQuery.jGrowl(leantime.i18n.__("short_notifications.effort_updated"));

                    }
                );

            }else{
                console.log("Ticket Controller: Effort data value not set correctly");
            }
        });

    };

    var _initMilestoneDropdown = function () {

        jQuery("body").on(
            "click", ".milestoneDropdown .dropdown-menu a", function () {

                var dataValue = jQuery(this).attr("data-value").split("_");
                var dataLabel = jQuery(this).attr('data-label');

                if (dataValue.length === 3) {

                    var ticketId = dataValue[0];
                    var milestoneId = dataValue[1];
                    var color = dataValue[2];

                    jQuery("#milestoneDropdownMenuLink"+ticketId+" span.text").append(" ...");

                    jQuery.ajax(
                        {
                            type: 'PATCH',
                            url: '/api/tickets',
                            data:
                                {
                                    id : ticketId,
                                    dependingTicketId:milestoneId
                                }
                        }
                    ).done(
                        function () {
                            jQuery("#milestoneDropdownMenuLink"+ticketId+" span.text").text(dataLabel);
                            jQuery("#milestoneDropdownMenuLink"+ticketId).css("backgroundColor", color);
                            jQuery.jGrowl(leantime.i18n.__("short_notifications.milestone_updated"));
                        }
                    );

                }
            }
        );
    };

    var _initStatusDropdown = function () {

        jQuery("body").on(
            "click", ".statusDropdown .dropdown-menu a", function () {

                var dataValue = jQuery(this).attr("data-value").split("_");
                var dataLabel = jQuery(this).attr('data-label');

                if (dataValue.length == 3) {

                    var ticketId = dataValue[0];
                    var statusId = dataValue[1];
                    var className = dataValue[2];

                    jQuery.ajax(
                        {
                            type: 'PATCH',
                            url: '/api/tickets',
                            data:
                                {
                                    id : ticketId,
                                    status:statusId
                                }
                        }
                    ).done(
                        function () {
                            jQuery("#statusDropdownMenuLink"+ticketId+" span.text").text(dataLabel);
                            jQuery("#statusDropdownMenuLink"+ticketId).removeClass().addClass(className+" dropdown-toggle f-left status ");
                            jQuery.jGrowl(leantime.i18n.__("short_notifications.status_updated"));
                            leantime.ticketsController.colorTicketBoxes(ticketId);
                        }
                    );

                }
            }
        );

        leantime.ticketsController.colorTicketBoxes();
    };

    var _initUserDropdown = function () {

        jQuery("body").on(
            "click", ".userDropdown .dropdown-menu a", function () {

                var dataValue = jQuery(this).attr("data-value").split("_");
                var dataLabel = jQuery(this).attr('data-label');

                if (dataValue.length == 3) {

                    var ticketId = dataValue[0];
                    var userId = dataValue[1];
                    var profileImageId = dataValue[2];

                    jQuery.ajax(
                        {
                            type: 'PATCH',
                            url: '/api/tickets',
                            data:
                                {
                                    id : ticketId,
                                    editorId:userId
                                }
                        }
                    ).done(
                        function () {
                            jQuery("#userDropdownMenuLink"+ticketId+" span.text span#userImage"+ticketId+" img").attr("src", "/api/users?profileImage="+profileImageId);
                            jQuery("#userDropdownMenuLink"+ticketId+" span.text span#user"+ticketId).text(dataLabel);
                            jQuery.jGrowl(leantime.i18n.__("short_notifications.user_updated"));
                        }
                    );

                }
            }
        );

        leantime.ticketsController.colorTicketBoxes();
    };

    var _initSprintDropdown = function () {

        jQuery("body").on(
            "click", ".sprintDropdown .dropdown-menu a", function () {

                var dataValue = jQuery(this).attr("data-value").split("_");
                var dataLabel = jQuery(this).attr('data-label');

                if (dataValue.length == 2) {

                    var ticketId = dataValue[0];
                    var sprintId = dataValue[1];

                    jQuery.ajax(
                        {
                            type: 'PATCH',
                            url: '/api/tickets',
                            data:
                                {
                                    id : ticketId,
                                    sprint:sprintId
                                }
                        }
                    ).done(
                        function () {
                            jQuery("#sprintDropdownMenuLink"+ticketId+" span.text").text(dataLabel);
                            jQuery.jGrowl(leantime.i18n.__("short_notifications.sprint_updated"));
                        }
                    );

                }
            }
        );

        leantime.ticketsController.colorTicketBoxes();
    };

    var _initSimpleColorPicker = function () {

            var colors = ['#064779', '#1B76BB', '#00814A', '#35CB8B', '#F3B600', '#FFD042', '#BC3600', '#F34500'];
            jQuery('input.simpleColorPicker').simpleColorPicker(
                { colors: colors,
                    onChangeColor: function (color) {
                        jQuery(this).css('background', color); }
                }
            );

            var currentColor = jQuery('input.simpleColorPicker').val();

            if(currentColor != ''){
                jQuery('input.simpleColorPicker').css('background', currentColor);
            }


    };

    var _initTicketEditor = function () {

        jQuery('textarea.tinymce').tinymce(
            {
                // General options
                width: "98%",
                skin_url: '/css/tinymceSkin/oxide',
                content_css: '/css/tinymceSkin/oxide/content.css',
                height:"300",
                content_style: "img { max-width: 100%; }",
                plugins : "autolink,link,image,lists,pagebreak,table,save,insertdatetime,preview,media,searchreplace,print,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,template,advlist",
                toolbar : "bold italic strikethrough | fontsizeselect forecolor | link unlink image | bullist | fullscreen",
                branding: false,
                statusbar: false,
                convert_urls: false,
                paste_data_images: true,

                images_upload_handler: function (blobInfo, success, failure) {
                    var xhr, formData;

                    xhr = new XMLHttpRequest();
                    xhr.withCredentials = false;
                    xhr.open('POST', '/api/files');

                    xhr.onload = function () {
                        var json;

                        if (xhr.status < 200 || xhr.status >= 300) {
                            failure('HTTP Error: ' + xhr.status);
                            return;
                        }

                        success(xhr.responseText);
                    };

                    formData = new FormData();
                    formData.append('file', blobInfo.blob());

                    xhr.send(formData);
                },
                file_browser_callback: function (field_name, url, type, win) {

                    window.tinyMceUploadFieldname = field_name;

                    var shortOptions = {
                        afterShowCont: function () {
                            jQuery(".fileModal").nyroModal({callbacks:shortOptions});

                        }
                    };

                    jQuery.nmManual(
                        '/files/showAll&modalPopUp=true',
                        {
                            stack: true,
                            callbacks: shortOptions
                        }
                    );
                    jQuery.nmTop().elts.cont.css("zIndex", "1000010");
                    jQuery.nmTop().elts.bg.css("zIndex", "1000010");
                    jQuery.nmTop().elts.load.css("zIndex", "1000010");
                    jQuery.nmTop().elts.all.find('.nyroModalCloseButton').css("zIndex", "1000010");

                },

            }
        );

        jQuery("#type").change(
            function () {
                var templates = {
                    Story : "As <i>{{ TYPE OF USER }}, I would like to <i>{{ DESCRIBE THE FEATURE }}</i>, so that <i>{{ DESCRIBE THE REASON }}</i><br /><br /><strong>Acceptance Critria</strong><br /><ul><li><i>{{ DESCRIBE THE FEATURES YOU EXPECT AT A MINIMUM }}</i></li></ul>",
                    Bug : "<strong>Summary</strong><br /><i>{{ DESCRIBE WHAT IS HAPPENING }}</i><br /><br /><strong>Steps to Reproduce</strong><br /><i>{{ DESCRIBE HOW YOU GOT THERE }}</i><br /><br /><strong>Expected Results</strong><br /><i>{{ WHAT OUTCOME DID YOU EXPECT}}</i><br /><br /><strong>Actual Results</strong><br /><i>{{ WHAT WAS THE ACTUAL RESULT}}</i>",
                };

                var currentValue = jQuery('#ticketDescription').val().replace(/(\r\n\t|\n|\r\t)/gm,"");

                if(currentValue == '' || jQuery(currentValue).text() == jQuery(templates.Story).text() || jQuery(currentValue).text() ==  jQuery(templates.Bug).text()) {
                    var selection = jQuery(this).val();
                    jQuery('#ticketDescription').val(templates[selection]);
                }
            }
        );
    };

    var _initDueDateTimePickers = function () {

        jQuery(".quickDueDates").datepicker(
            {
                dateFormat: 'mm/dd/yy',
                onSelect: function(date) {

                    var dateTime = new Date(date);
                    dateTime = moment(dateTime).format("YYYY-MM-DD HH:mm:ss");

                    var id = jQuery(this).attr("data-id");
                    var newDate = dateTime;

                    leantime.ticketsRepository.updateDueDates(id, newDate, function() {
                        jQuery.jGrowl("Due date changed!");
                    });

                }
            }
        );
    };

    var initTimeSheetChart = function (labels, d2, d3, canvasId) {

        var ctx = document.getElementById(canvasId).getContext('2d');
        var stackedLine = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets:[{
                    label:"Booked Hours",
                    backgroundColor: 'rgba(201,48,44, 0.5)',
                    borderColor: 'rgb(201,48,44)',
                    data:d2
                },
                    {
                        label:"Planned Hours",
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor:'rgb(54, 162, 235)',
                        data:d3
                    }]
            },
            options: {
                scales: {
                    yAxes: [{
                        stacked: true
                    }]
                }
            }
        });

    };

    var colorTicketBoxes = function (currentBox){

        var color = "#fff";
        jQuery(".ticketBox").each(function(index){

            var value = jQuery(this).find(".statusDropdown > a").attr("class");

            if(value != undefined) {
                if (value.indexOf("important") > -1) {

                    color = "#b94a48";

                } else if (value.indexOf("warning") > -1) {

                    color = "#f89406";

                } else if (value.indexOf("success") > -1) {

                    color = "#468847";

                } else if (value.indexOf("default") > -1) {

                    color = "#999999";
                }
                jQuery(this).css("borderLeft", "5px solid " + color);

                if(currentBox != null) {
                    if (jQuery(this).attr("data-val") == currentBox) {
                        jQuery("#ticket_" + currentBox + " .ticketBox").animate({backgroundColor: color}, 'fast').animate({backgroundColor: "#fff"}, 'slow');
                    }
                }

            }
        });



    };

    var initTicketTabs = function(){

        jQuery(document).ready(function () {
            jQuery('.ticketTabs').tabs({
                create: function( event, ui ) {
                    jQuery('.ticketTabs').css("visibility", "visible");
                },
                activate: function(event, ui) {
                    console.log(ui);
                    window.location.hash = ui.newPanel.selector;
                }
            });
        });

    };

    var initTicketSearchSubmit = function (url) {

        jQuery("#ticketSearch").on('submit', function(e) {
            e.preventDefault()

            var project = jQuery("#projectIdInput").val();
            var users = jQuery("#userSelect").val();
            var milestones = jQuery("#milestoneSelect").val();
            var term = jQuery("#termInput").val();
            var sprints = jQuery("#sprintSelect").val();
            var types = jQuery("#typeSelect").val();
            var status = jQuery("#statusSelect").val();
            var sort = jQuery("#sortBySelect").val();
            var group = jQuery("#groupBySelect").val();

            var query = "?search=true";
            if(project != "" && project != undefined) {query = query + "&projectId=" + project}
            if(users != "" && users != undefined) {query = query + "&users=" + users}
            if(milestones != ""  && milestones != undefined) {query = query + "&milestone=" + milestones}
            if(term != ""  && term != undefined) {query = query + "&term=" + term;}
            if(sprints != ""  && sprints != undefined) {query = query + "&sprint=" + sprints;}
            if(types != "" && types != undefined) {query = query + "&type=" + types;}
            if(status != "" && status != undefined) {query = query + "&status=" + status;}
            if(sort != "" && sort != undefined) {query = query + "&sort=" + sort;}
            if(group != "" && group != undefined) {query = query + "&group=" + group;}

            var rediredirectUrl = url + query;

            window.location.href = rediredirectUrl;

        });
    };

    var initTicketKanban = function (ticketStatusList) {

        jQuery(window).bind("load", function () {

            jQuery(".loading").fadeOut();
            jQuery(".filterBar .row-fluid").css("opacity", "1");
            var height = jQuery("html").height()-320;
            jQuery("#sortableTicketKanban .column .contentInner").css("height", height);
            countTickets();
        });

        jQuery("#sortableTicketKanban .ticketBox").hover(function(){
            jQuery(this).css("background", "#f9f9f9");
        },function(){
            jQuery(this).css("background", "#ffffff");
        });


        jQuery("#sortableTicketKanban .contentInner").sortable({
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
            },
            update: function (event, ui) {

                countTickets();

                var statusPostData = {
                    action: "kanbanSort",
                    payload: {}
                };
                for(var i=0; i<ticketStatusList.length; i++) {

                    if(jQuery(".contentInner.status_"+ticketStatusList[i]).length) {
                        statusPostData.payload[ticketStatusList[i]] = jQuery(".contentInner.status_" + ticketStatusList[i]).sortable('serialize');
                    }
                }

                // POST to server using $.post or $.ajax
                jQuery.ajax({
                    type: 'POST',
                    url: '/api/tickets',
                    data: statusPostData

                });

            }
        });

        function countTickets () {

            jQuery("#sortableTicketKanban .column").each(function(){
                var counting= jQuery(this).find('.moveable').length;
                jQuery(this).find(' .count').text(counting);
            });

        }

        function tilt_direction(item) {
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

        jQuery( ".portlet" )
            .addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
            .find( ".portlet-header" )
            .addClass( "ui-widget-header ui-corner-all" )
            .prepend( "<span class='ui-icon ui-icon-minusthick portlet-toggle'></span>");

        jQuery( ".portlet-toggle" ).click(function() {
            var icon = jQuery( this );
            icon.toggleClass( "ui-icon-minusthick ui-icon-plusthick" );
            icon.closest( ".portlet" ).find( ".portlet-content" ).toggle();
        });

    };

    var initUserSelectBox = function () {

        jQuery(".user-select").chosen();

    };

    var initStatusSelectBox = function () {

        jQuery(".status-select").chosen();

    };

    var initTicketsTable = function (groupBy) {

        jQuery(document).ready(function() {

            var size = 100;
            var columnIndex = false;

            if(groupBy == "sprint") {
                columnIndex = 2;
            }

            if(groupBy == "milestone") {
                columnIndex = 3;
            }

            if(groupBy == "user") {
                columnIndex = 5;
            }
            var rowGroupOption = false;
            var orderFixedOption = false;

            if(columnIndex !== false) {

                rowGroupOption = {
                    startRender: function (rows, group) {

                        var sumPlanned = rows
                            .data()
                            .pluck(7)
                            .reduce(function (a, b) {
                                return parseInt(a) + parseInt(jQuery(b).val())*1;
                            }, 0);

                        var sumRemaining = rows
                            .data()
                            .pluck(8)
                            .reduce(function (a, b) {
                                return parseInt(a) + parseInt(jQuery(b).val())*1;
                            }, 0);

                        return jQuery('<tr/>')
                            .append('<td colspan="7">' + group + ' ('+rows.count()+')</td>')
                            .append('<td>' + sumPlanned + '</td>')
                            .append('<td>' + sumRemaining + '</td>');

                    },
                    dataSrc: function (row) {
                        return row[columnIndex]["@data-order"];
                    }
                };

                orderFixedOption = [[columnIndex, 'asc']]
            }

            var allTickets = jQuery("#allTicketsTable").DataTable({
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
                    "displayLength":100,
                    "order": [[6, 'asc']],
                    "orderFixed": orderFixedOption,
                    "rowGroup": rowGroupOption,

            });

            /*


             */

        });
    };

    // Make public what you want to have public, everything else is private
    return {
        toggleFilterBar: toggleFilterBar,
        triggerMilestoneModal: triggerMilestoneModal,
        initGanttChart:initGanttChart,
        updateRemaining:updateRemaining,
        initModals:initModals,
        openMilestoneModalManually:openMilestoneModalManually,
        initTimeSheetChart:initTimeSheetChart,
        colorTicketBoxes:colorTicketBoxes,
        initTicketTabs:initTicketTabs,
        initTicketSearchSubmit:initTicketSearchSubmit,
        initTicketKanban:initTicketKanban,
        initUserSelectBox:initUserSelectBox,
        initStatusSelectBox:initStatusSelectBox,
        initTicketsTable:initTicketsTable
    };
})();
