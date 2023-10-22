leantime.helperController = (function () {


    //Functions
    var showHelperModal = function (module, minW, minH) {

        jQuery(document).ready(function () {
            jQuery.nmManual(
                leantime.appUrl + "/help/showOnboardingDialog?module=" + module,
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
        });

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
                useModalOverlay: true,
                defaults: {
                    classes: 'shepherd-theme-arrows',
                    showCancelLink: true,
                    scrollTo: true,
                }
            }
        );

        tour.addStep(
            'Left Nav',
            {
                title: leantime.i18n.__("tour.left_navigation"),
                text: leantime.i18n.__("tour.left_nav_text"),
                attachTo: { element: '.leftmenu ul', on: 'left' },
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
            'Project Selection',
            {
                title: leantime.i18n.__("tour.project_selection"),
                text: leantime.i18n.__("tour.project_selection_text"),
                attachTo: { element: '.project-selector', on: 'bottom' },
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
            'Header Navigation',
            {
                title: leantime.i18n.__("tour.top_navigation"),
                text: leantime.i18n.__("tour.top_navigation_text"),
                attachTo: { element: '.headmenu', on: 'bottom' },
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
            'Your projects',
            {
                title: leantime.i18n.__("tour.project_progress"),
                text: "These are the projects currently assigned to you. You can click on the headlines to get to those project quickly." ,
                attachTo: { element: '#projectProgressContainer', on: 'left' },
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
            'Your Todos',
            {
                title: leantime.i18n.__("tour.your_todos"),
                text: leantime.i18n.__("tour.your_todos_text"),
                attachTo: { element: '#yourToDoContainer', on: 'top' },
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
            'Your Todos',
            {
                title: leantime.i18n.__("tour.congratulations"),
                text: leantime.i18n.__("tour.congratulations_dashboard_text"),
                buttons:[
                {
                    text:leantime.i18n.__("tour.close"),
                    action:tour.cancel
                },
                {
                    text: "Go to the welcome content",
                    events: {
                        'click': function () {
                            leantime.helperController.showHelperModal('dashboard', 300, 500);
                        }
                    }
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
            'Left Nav',
            {
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
            'Drag & Drop',
            {
                title: leantime.i18n.__("tour.drag_drop"),
                text: leantime.i18n.__("tour.drag_drop_text"),
                attachTo: { element: '.ticketBox h4 ', on: 'right' },
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
            'Change Views',
            {
                title: leantime.i18n.__("tour.change_views"),
                text: "You can visualize your To-Dos in different ways. With these tabs you can switch between Kanban, Table, Timeline and Calendar views.",
                attachTo: { element: '.maincontentinner.tabs ul li', on: 'bottom' },
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
            'Filters And Groups',
            {
                title: "Filters & Grouping",
                text: "You can use filters and grouping to organize your To-Dos in a way that makes sense to you and your team",
                attachTo: '.filterWrapper bottom',
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
            'Your Todos',
            {
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
            'complete',
            function () {
                leantime.helperRepository.stopTour();
            }
        );

        tour.start();

    };

    var firstLoginModal = function () {

        jQuery(document).ready(function () {

            var onboardingModal = {
                sizes: {
                    minW: 700,
                    minH: 250
                },
                resizable: true,
                autoSizable: true,
                callbacks: {
                    afterShowCont: function () {
                        jQuery(".showDialogOnLoad").show();
                        jQuery(".onboardingModal").nyroModal(onboardingModal);
                    },
                    beforeClose: function () {

                        location.reload();
                    },
                }
            };

            jQuery(".onboardingModal").nyroModal(onboardingModal);

            jQuery.nmManual(
                leantime.appUrl + "/help/firstLogin?step=project",
                onboardingModal
            );
        });
    };

    // Make public what you want to have public, everything else is private
    return {
        showHelperModal: showHelperModal,
        hideAndKeepHidden: hideAndKeepHidden,
        startDashboardTour:startDashboardTour,
        startKanbanTour: startKanbanTour,
        firstLoginModal:firstLoginModal
    };
})();
