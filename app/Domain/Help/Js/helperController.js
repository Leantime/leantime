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

    var startProjectDashboardTour = function () {

        if(jQuery.nmTop()) {
            jQuery.nmTop().close();
        }

        leantime.helperRepository.updateUserModalSettings("projectDashboard")

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

        tour.addStep({
            id: "left-nav",
            title: "🍷 Like a Fine Dining Menu",
            text: "Did you know that projects are more than just task management? Find everything related to the current project in this beautiful list of menu items.",
            attachTo: { element: '.leftmenu ul', on: 'left' },
            modalOverlayOpeningRadius: 10,
            modalOverlayOpeningPadding: 10,
            scrollTo:true,
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
        });


        tour.addStep(
            {
                id: 'project-selector',
                title: "🎸 All. The. (Small). Things.",
                text: "Use the project selector to jump between projects. 'My Work' to see everything you own and the 'Company' menu for the boring stuff. ",
                attachTo: { element: '.bigProjectSelector', on: 'bottom' },
                modalOverlayOpeningRadius: 10,
                modalOverlayOpeningPadding: 10,
                scrollTo:true,
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
            {
                id: 'header-navigation',
                title: '🪞 Mirror, mirror on the wall',
                text: "You can add your favorite profile pic here. Also settings, notifications and themes. Don't do this on your lunch break.",
                attachTo: { element: '.headmenu', on: 'bottom' },
                modalOverlayOpeningRadius: 10,
                modalOverlayOpeningPadding: 10,
                scrollTo:true,
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
            {
                id: 'project-checklist',
                title: "🌳 A walk in the park",
                text: "Not sure how to manage a project? Well here it is, this is how you manage a project. Follow this 12 (4 step) program to become a better person.",
                attachTo: { element: '#progressForm', on: 'bottom' },
                modalOverlayOpeningRadius: 10,
                modalOverlayOpeningPadding: 10,
                scrollTo:true,
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
            {
                id: 'project-status-updates',
                title: "🎢 A rollercoaster of emotions",
                text: "Keep the folks who need the extra hand holding up to date using the status updates. No more late night pings.",
                attachTo: { element: '#comments', on: 'left' },
                modalOverlayOpeningRadius: 10,
                modalOverlayOpeningPadding: 10,
                scrollTo:true,
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
            {
                id: 'team-box',
                title: "🌞 It's a beautiful day in the neighborhood",
                text: "Bring all your friends rock out to Blink 182 and work on this project together.",
                attachTo: { element: '.teamBox', on: 'top' },
                modalOverlayOpeningRadius: 10,
                modalOverlayOpeningPadding: 10,
                scrollTo:true,
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
            {
                title: "🎉 Congratulations",
                text: "Follow the steps in the project checklist to fill in the rest of the project. If you want to go through this process again you can click on 'What's on this page' under your profile menu.",
                buttons:[
                    {
                        text:leantime.i18n.__("tour.close"),
                        action: tour.cancel
                    }
                ],
                scrollTo:true,
                when: {
                    show: function() {
                        confetti({
                            spread: 70,
                            origin: { y: 1.2 },
                            disableForReducedMotion: true
                        });
                    }
                },
                advanceOn: '.headmenu click'
            }
        );

        /*
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
        );*/

        tour.start();

    };

    var startKanbanTour = function () {

        if(jQuery.nmTop()) {
            jQuery.nmTop().close();
        }

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
                attachTo: { element: '.quickAddLink', on: 'right' },
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
                leantime.appUrl + "/help/firstLogin",
                onboardingModal
            );
        });
    };

    // Make public what you want to have public, everything else is private
    return {
        showHelperModal: showHelperModal,
        hideAndKeepHidden: hideAndKeepHidden,
        startProjectDashboardTour:startProjectDashboardTour,
        startKanbanTour: startKanbanTour,
        firstLoginModal:firstLoginModal
    };
})();
