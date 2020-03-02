leantime.ticketsController = (function () {

    //Variables

    var milestoneModalConfig = {
        sizes: {
            minW: 500,
            minH: 750,
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
                _initSprintPopover();
                _initMilestonePopover();
                _initUserPopover();
                _initEffortPopover();
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
        jQuery.nmManual(leantime.appUrl+'/tickets/editMilestone/'+id, milestoneModalConfig);

    };

    var openMilestoneModalManually = function (url) {
        jQuery.nmManual(url, milestoneModalConfig);
    };

    var toggleFilterBar = function () {
        jQuery(".filterBar").toggle("fast");
    };

    var initGanttChart = function (tasks, viewMode) {

        jQuery(document).ready(
            function () {

                var gantt_chart = new Gantt(
                    "#gantt", tasks, {
                        custom_popup_html: function (task) {
                            // the task object will contain the updated
                            // dates and progress value
                            var end_date = task._end.format('MMM D');
                            return '<div class="details-container"> ' +
                            '<h4><a href="'+leantime.appUrl+'/tickets/editMilestone/'+task.id+'" class="milestoneModal">'+task.name+'</a></h4><br /> ' +
                            '<p>Expected to finish by <strong>'+end_date+'</strong><br /> ' +
                            ''+Math.round(task.progress)+'% completed!</p> ' +
                            '<a href="'+leantime.appUrl+'/tickets/editMilestone/'+task.id+'" class="milestoneModal"><span class="fa fa-map"></span> Edit Milestone</a> | ' +
                            '<a href="'+leantime.appUrl+'/tickets/showKanban&milestone='+task.id+'"><span class="iconfa-pushpin"></span> View To-Dos</a> ' +

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

                jQuery(".btn-group").on(
                    "click", "button", function () {
                        $btn =jQuery(this);
                        var mode = $btn.text();
                        gantt_chart.change_view_mode(mode);
                        $btn.parent().find('button').removeClass('active');
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
                dateFormat: 'mm/dd/yy'
            }
        );
    };

    var initModals = function () {
        _initModals();
    }

    var _initSprintDates = function () {

        Date.prototype.addDays = function (days) {
            this.setDate(this.getDate() + days);
            return this;
        };
        jQuery.datepicker.setDefaults(
            { beforeShow: function (i) {
                if (jQuery(i).attr('readonly')) { return false; } } }
        );

        var dateFormat = "mm/dd/yy",
            from = jQuery("#sprintStart")
                .datepicker(
                    {
                        numberOfMonths: 1
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
                minH: 350,
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
                minH: 750,
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
                        url: leantime.appUrl+'/api/tickets',
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

    var _initEffortPopover = function () {
        jQuery('.effortPopover').popover(
            {
                template:'<div class="popover effortPopoverContainer" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'

            }
        );

        var storyPointLabels = {
            '1': 'XS',
            '2': 'S',
            '3':"M",
            '5':"L",
            '8' : "XL",
            '13': "XXL"
        };

        jQuery("body").on(
            "click", ".effortPopoverContainer input", function () {

                var ticket = jQuery(this).attr("name").split("_");
                var val = jQuery(this).val();

                jQuery.ajax(
                    {
                        type: 'PATCH',
                        url: leantime.appUrl+'/api/tickets',
                        data:
                        {
                            id : ticket[1],
                            storypoints:val
                        }
                    }
                ).done(
                    function () {
                        jQuery("#effort-"+ticket[1]).text(storyPointLabels[val]);
                        jQuery('.effortPopover').popover('hide');
                    }
                );

            }
        );
    };


    var _initMilestonePopover = function () {

        jQuery('.milestonePopover').popover(
            {
                template:'<div class="popover milestonePopoverContainer" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'

            }
        );

        jQuery("body").on(
            "click", ".milestonePopoverContainer input", function () {

                var ticket = jQuery(this).attr("name").split("_");
                var val = jQuery(this).val();
                var label = jQuery(this).attr('data-label');
                var color = jQuery(this).attr('data-color');

                jQuery.ajax(
                    {
                        type: 'PATCH',
                        url: leantime.appUrl+'/api/tickets',
                        data:
                        {
                            id : ticket[1],
                            dependingTicketId:val
                        }
                    }
                ).done(
                    function () {
                        jQuery("#milestone-"+ticket[1]).text(label);
                        jQuery("#milestone-"+ticket[1]).css("backgroundColor", color);
                        jQuery('.milestonePopover').popover('hide');
                    }
                );

            }
        );
    };

    var _initUserPopover = function () {

        jQuery('.userPopover').popover(
            {
                template:'<div class="popover userPopoverContainer" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'

            }
        );

        jQuery("body").on(
            "click", ".userPopoverContainer input", function () {

                var ticket = jQuery(this).attr("name").split("_");
                var val = jQuery(this).val();
                var label = jQuery(this).attr('data-label');

                jQuery.ajax(
                    {
                        type: 'PATCH',
                        url: leantime.appUrl+'/api/tickets',
                        data:
                        {
                            id : ticket[1],
                            editorId:val
                        }
                    }
                ).done(
                    function () {
                        jQuery("#user"+ticket[1]).text(label);
                        jQuery('.userPopover').popover('hide');
                    }
                );

            }
        );
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
                height:"300",
                content_style: "img { max-width: 100%; }",
                plugins : "autolink,link,textcolor,image,lists,pagebreak,table,save,insertdatetime,preview,media,searchreplace,print,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,template,advlist",
                // Theme options
                toolbar : "bold,italic,strikethrough,|,fontsizeselect,forecolor,|,link,unlink,image,|,bullist,|,fullscreen",
                branding: false,
                menubar:false,
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
                        leantime.appUrl+'/files/showAll&modalPopUp=true',
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

    // Make public what you want to have public, everything else is private
    return {
        toggleFilterBar: toggleFilterBar,
        triggerMilestoneModal: triggerMilestoneModal,
        initGanttChart:initGanttChart,
        updateRemaining:updateRemaining,
        initModals:initModals,
        openMilestoneModalManually:openMilestoneModalManually
    };
})();
