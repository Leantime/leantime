leantime.helperController = (function () {

    //Variables

    //Constructor
    (function () {
        jQuery(document).ready(
            function () {

            }
        );

    })();

    //Functions
    var showHelperModal = function (module, minW, minH) {

        jQuery.nmManual(
            leantime.appUrl+"/help/showOnboardingDialog?module="+module,
            {sizes: {
                minW: minW || 200,
                minH: minH || 500,
                },
                resizable: true,
                autoSizable: true,
                callbacks: {
                    beforeShowCont: function () {
                        leantime.replaceSVGColors();
                    }
                }
            }
        );
    };

    //Functions
    var hideAndKeepHidden = function (module) {

        leantime.helperRepository.updateUserModalSettings(module);
        jQuery.nmTop().close();

    };

    var startDashboardTour = function () {

        leantime.helperRepository.startingTour();

        jQuery.nmTop().close();

        var tour = new Shepherd.Tour(
            {
                defaults: {
                    classes: 'shepherd-theme-arrows',
                    showCancelLink: true,
                    scrollTo: true,
                }
            }
        );

        tour.addStep(
            'Left Nav', {
                title: leantime.i18n.__("tour.left_navigation"),
                text: leantime.i18n.__("tour.left_nav_text"),
                attachTo: '.leftmenu ul right',
                advanceOn: '.headmenu click',
                buttons: [
                {
                    text: leantime.i18n.__("tour.cancel"),
                    classes: 'shepherd-button-secondary',
                    action: tour.cancel
                },
                {
                    text: leantime.i18n.__("tour.next"),
                    action: tour.next
                }
                ]
            }
        );

        tour.addStep(
            'Project Selection', {
                title: leantime.i18n.__("tour.project_selection"),
                text: leantime.i18n.__("tour.project_selection_text"),
                attachTo: '.project-selector right',
                buttons: [
                {
                    text: leantime.i18n.__("tour.back"),
                    classes: 'shepherd-button-secondary',
                    action: tour.back
                },
                {
                    text: leantime.i18n.__("tour.next"),
                    action: tour.next
                }
                ]
            }
        );

        tour.addStep(
            'Header Navigation', {
                title: leantime.i18n.__("tour.top_navigation"),
                text: leantime.i18n.__("tour.top_navigation_text"),
                attachTo: '.headmenu bottom',
                advanceOn: '#sprintBurndownChart click',
                buttons: [
                    {
                        text: leantime.i18n.__("tour.back"),
                        classes: 'shepherd-button-secondary',
                        action: tour.back
                    },
                    {
                        text: leantime.i18n.__("tour.next"),
                        action: tour.next
                    }
                ]
            }
        );

        tour.addStep(
            'Project Status', {
                title: leantime.i18n.__("tour.project_progress"),
                text: leantime.i18n.__("tour.project_progress_text"),
                attachTo: '#projectProgressContainer left',
                advanceOn: '.headmenu click',
                buttons: [
                    {
                        text: leantime.i18n.__("tour.back"),
                        classes: 'shepherd-button-secondary',
                        action: tour.back
                    },
                    {
                        text: leantime.i18n.__("tour.next"),
                        action: tour.next
                    }
                ]
            }
        );

        tour.addStep(
            'Your Todos', {
                title: leantime.i18n.__("tour.milestone_progress"),
                text: leantime.i18n.__("tour.milestone_progress_text"),
                attachTo: '#milestoneProgressContainer left',
                advanceOn: '.headmenu click',
                buttons: [
                    {
                        text: leantime.i18n.__("tour.back"),
                        classes: 'shepherd-button-secondary',
                        action: tour.back
                    },
                    {
                        text: leantime.i18n.__("tour.next"),
                        action: tour.next
                    }
                ]
            }
        );

        tour.addStep(
            'Your Todos', {
                title: leantime.i18n.__("tour.your_todos"),
                text: leantime.i18n.__("tour.your_todos_text"),
                attachTo: '#yourToDoContainer top',
                advanceOn: '.headmenu click',
                buttons: [
                    {
                        text: leantime.i18n.__("tour.back"),
                        classes: 'shepherd-button-secondary',
                        action: tour.back
                    },
                    {
                        text: leantime.i18n.__("tour.next"),
                        action: tour.next
                    }
                ]
            }
        );

        tour.addStep(
            'Your Todos', {
                title: leantime.i18n.__("tour.congratulations"),
                text: leantime.i18n.__("tour.congratulations_dashboard_text"),
                buttons:[
                {
                    text:leantime.i18n.__("tour.close"),
                    action:tour.cancel
                }
                ],
                advanceOn: '.headmenu click'
            }
        );

        tour.start();

    };

    var startKanbanTour = function () {
        jQuery.nmTop().close();
        var tour = new Shepherd.Tour(
            {
                defaults: {
                    classes: 'shepherd-theme-arrows',
                    showCancelLink: true,
                    scrollTo: true,
                }
            }
        );

        tour.addStep(
            'Left Nav', {
                title: leantime.i18n.__("tour.kanban"),
                text: leantime.i18n.__("tour.kanban_text"),
                attachTo: '.column right',
                advanceOn: '.headmenu click',
                buttons: [
                {
                    text: 'Cancel',
                    classes: 'shepherd-button-secondary',
                    action: tour.cancel
                }, {
                    text: 'Next',
                    action: tour.next
                }
                ]
            }
        );

        tour.addStep(
            'Left Nav', {
                title: leantime.i18n.__("tour.drag_drop"),
                text: leantime.i18n.__("tour.drag_drop_text"),
                attachTo: '.ticketBox h4 right',
                advanceOn: '.ticketBox click',
                buttons: [
                    {
                        text: leantime.i18n.__("tour.back"),
                        classes: 'shepherd-button-secondary',
                        action: tour.back
                    },
                    {
                        text: leantime.i18n.__("tour.next"),
                        action: tour.next
                    }
                ]
            }
        );

        tour.addStep(
            'Change Views', {
                title: leantime.i18n.__("tour.change_views"),
                text: leantime.i18n.__("tour.change_views_text"),
                attachTo: '.btn-group .fa-columns left',
                advanceOn: '.ticketBox click',
                buttons: [
                    {
                        text: leantime.i18n.__("tour.back"),
                        classes: 'shepherd-button-secondary',
                        action: tour.back
                    },
                    {
                        text: leantime.i18n.__("tour.next"),
                        action: tour.next
                    }
                ]
            }
        );

        tour.addStep(
            'Your Todos', {
                title: leantime.i18n.__("tour.congratulations"),
                text: leantime.i18n.__("tour.congratulations_kanban_text"),
                buttons:[
                {
                    text:leantime.i18n.__("tour.close"),
                    action:tour.complete
                }
                ],
                advanceOn: '.headmenu click'
            }
        );

        tour.on(
            'complete', function () {
                leantime.helperRepository.stopTour();
            }
        );

        tour.start();

    };

    // Make public what you want to have public, everything else is private
    return {
        showHelperModal: showHelperModal,
        hideAndKeepHidden: hideAndKeepHidden,
        startDashboardTour:startDashboardTour,
        startKanbanTour: startKanbanTour
    };
})();
