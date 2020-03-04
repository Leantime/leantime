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
                title: 'Left Navigation',
                text: 'The left side bar represents your product development life cycle.<br />Everything in this menu belongs to the project selected above',
                attachTo: '.leftmenu ul right',
                advanceOn: '.headmenu click',
                buttons: [
                {
                    text: 'Cancel',
                    classes: 'shepherd-button-secondary',
                    action: tour.cancel
                },
                {
                    text: 'Next',
                    action: tour.next
                }
                ]
            }
        );

        tour.addStep(
            'Project Selection', {
                title: 'Project Selection',
                text: 'This is your main project selector. Click here to change in between projects. <br />Everything you do below is part of the current project.',
                attachTo: '.project-selector right',
                buttons: [
                {
                    text: 'Back',
                    classes: 'shepherd-button-secondary',
                    action: tour.back
                },
                {
                    text: 'Next',
                    action: tour.next
                }
                ]
            }
        );

        tour.addStep(
            'Header Navigation', {
                title: 'Top Navigation',
                text: 'This is where you\'ll find your personal links.',
                attachTo: '.headmenu bottom',
                advanceOn: '#sprintBurndownChart click',
                buttons: [
                {
                    text: 'Back',
                    classes: 'shepherd-button-secondary',
                    action: tour.back
                },
                {
                    text: 'Next',
                    action: tour.next
                }
                ]
            }
        );

        tour.addStep(
            'Sprint Burndown', {
                title: 'Sprint Burndown',
                text: 'This burn down chart shows tasks versus time.<br /> You can sort by effort, by hours, and by # of To Dos.<br />To learn more about agile sprints and burn down charts <a href="http://help.leantime.io/knowledge-base/what-is-a-sprint" target="_blank">click here</a>',
                attachTo: '#sprintBurndownChart bottom',
                advanceOn: '.headmenu click',
                buttons: [
                {
                    text: 'Back',
                    classes: 'shepherd-button-secondary',
                    action: tour.back
                },
                {
                    text: 'Next',
                    action: tour.next
                }
                ]
            }
        );

        tour.addStep(
            'Project Status', {
                title: 'Project Progress',
                text: 'Using items such as Effort, Hours, and # of To Dos, we\'ll<br />help predict estimated project completion.<br />',
                attachTo: '#projectProgressContainer left',
                advanceOn: '.headmenu click',
                buttons: [
                {
                    text: 'Back',
                    classes: 'shepherd-button-secondary',
                    action: tour.back
                }, {
                    text: 'Next',
                    action: tour.next
                }
                ]
            }
        );

        tour.addStep(
            'Your Todos', {
                title: 'Milestone Progress',
                text: 'This progress bar chart shows Milestone completion with their anticipated end dates.',
                attachTo: '#milestoneProgressContainer left',
                advanceOn: '.headmenu click',
                buttons: [
                {
                    text: 'Back',
                    classes: 'shepherd-button-secondary',
                    action: tour.back
                }, {
                    text: 'Next',
                    action: tour.next
                }
                ]
            }
        );

        tour.addStep(
            'Your Todos', {
                title: 'Your ToDos',
                text: 'Here are the To - Dos currently assigned to you. <br />You\'ll see which To-Dos are due this week and which ones are due at a later time.',
                attachTo: '#yourToDoContainer top',
                advanceOn: '.headmenu click',
                buttons: [
                {
                    text: 'Back',
                    classes: 'shepherd-button-secondary',
                    action: tour.back
                }, {
                    text: 'Next',
                    action: tour.next
                }
                ]
            }
        );

        tour.addStep(
            'Your Todos', {
                title: 'Congratulations!',
                text: 'That completes the Dashboard overview!  Great work!<br /> Next Stop: Create your first project!',
                buttons:[
                {
                    text:"Close",
                    action:tour.cancel
                },
                {
                    text:"Go to Projects",
                    events: {
                        'click':function () {
                            window.location.href = leantime.appUrl+"/projects/newProject/";
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
            'Left Nav', {
                title: 'Kanban',
                text: 'These lanes represent where you and your team are with the To Dos.  <br /> New, blocked, In Progress, Needs Approval, Done.',
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
                title: 'Drag & Drop',
                text: 'You can drag and drop these cards from one state to the next. <br />A click on the headline will bring you to the details of this ToDo.',
                attachTo: '.ticketBox h4 right',
                advanceOn: '.ticketBox click',
                buttons: [
                {
                    text: 'Back',
                    classes: 'shepherd-button-secondary',
                    action: tour.back
                }, {
                    text: 'Next',
                    action: tour.next
                }
                ]
            }
        );

        tour.addStep(
            'More buttons', {
                title: 'Sprint',
                text: 'Shows your current sprint or lets you create a new one.',
                attachTo: '.currentSprint bottom',
                advanceOn: '.ticketBox click',
                buttons: [
                {
                    text: 'Back',
                    classes: 'shepherd-button-secondary',
                    action: tour.back
                }, {
                    text: 'Next',
                    action: tour.next
                }
                ]
            }
        );

        tour.addStep(
            'Change Views', {
                title: 'Change Views',
                text: 'These buttons let you switch between Backlog and Kanban View',
                attachTo: '.btn-group .fa-columns left',
                advanceOn: '.ticketBox click',
                buttons: [
                {
                    text: 'Back',
                    classes: 'shepherd-button-secondary',
                    action: tour.back
                }, {
                    text: 'Next',
                    action: tour.next
                }
                ]
            }
        );

        tour.addStep(
            'Your Todos', {
                title: 'Congratulations!',
                text: 'You have completed the tour!<br />If you need more help. Click the "Need Help" Button on the bottom of the page. <br /> <br />Don\'t hesitate to reach out to our awesome support team for any additional help! <br />We are here to make your life easier.',
                buttons:[
                {
                    text:"Close",
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


    var startBacklogTour = function () {
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
                title: 'Drag & Drop',
                text: 'Drag and drop To Dos from one state to the next.<br />Click on the headline to see the details of the To Do.',
                attachTo: '.ticketBox h3 top',
                advanceOn: '.ticketBox click',
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
                title: 'Milestones',
                text: 'Already created Milestones? <br/>Click to assign it to the To Do.',
                attachTo: '.milestonePopover .f-left right',
                advanceOn: '.ticketBox click',
                buttons: [
                {
                    text: 'Back',
                    classes: 'shepherd-button-secondary',
                    action: tour.back
                }, {
                    text: 'Next',
                    action: tour.next
                }
                ]
            }
        );

        tour.addStep(
            'Left Nav', {
                title: 'Efforts',
                text: 'Select To Do effort here.<br/>We measure effort in T-shirt sizes.',
                attachTo: '.effortPopover .f-left  right',
                advanceOn: '.ticketBox click',
                buttons: [
                {
                    text: 'Back',
                    classes: 'shepherd-button-secondary',
                    action: tour.back
                }, {
                    text: 'Next',
                    action: tour.next
                }
                ]
            }
        );

        tour.addStep(
            'Left Nav', {
                title: 'Status',
                text: 'This box shows where you are with a task.<br/>Click to see more.',
                attachTo: '.popoverbtn right',
                advanceOn: '.ticketBox click',
                buttons: [
                {
                    text: 'Back',
                    classes: 'shepherd-button-secondary',
                    action: tour.back
                }, {
                    text: 'Next',
                    action: tour.next
                }
                ]
            }
        );

        tour.addStep(
            'Left Nav', {
                title: 'User',
                text: 'Click here to assign a To Do and quickly see who\'s working on it.',
                attachTo: '.userPopover .author right',
                advanceOn: '.ticketBox click',
                buttons: [
                {
                    text: 'Back',
                    classes: 'shepherd-button-secondary',
                    action: tour.back
                }, {
                    text: 'Next',
                    action: tour.next
                }
                ]
            }
        );

        tour.addStep(
            'Left Nav', {
                title: 'Quick Add',
                text: 'Click here to fast add a to-do item to the backlog.',
                attachTo: '.quickAddLink bottom',
                advanceOn: '.ticketBox click',
                buttons: [
                {
                    text: 'Back',
                    classes: 'shepherd-button-secondary',
                    action: tour.back
                }, {
                    text: 'Next',
                    action: tour.next
                }
                ]
            }
        );

        tour.addStep(
            'Sprint Drag', {
                title: 'Sprint',
                text: 'Drag and drop items from the backlog and place them into your current Sprint.',
                attachTo: '#sortableSprint bottom',
                advanceOn: '.ticketBox click',
                buttons: [
                {
                    text: 'Back',
                    classes: 'shepherd-button-secondary',
                    action: tour.back
                }, {
                    text: 'Next',
                    action: tour.next
                }
                ]
            }
        );


        tour.addStep(
            'More buttons', {
                title: 'Add or Filter',
                text: 'Add more or sort your To-Dos by filter.',
                attachTo: '.btn-group button bottom',
                advanceOn: '.ticketBox click',
                buttons: [
                {
                    text: 'Back',
                    classes: 'shepherd-button-secondary',
                    action: tour.back
                }, {
                    text: 'Next',
                    action: tour.next
                }
                ]
            }
        );

        tour.addStep(
            'Change Views', {
                title: 'User',
                text: 'This tab will take you to the Kanban view of your To Dos.',
                attachTo: '.btn-group .fa-columns left',
                advanceOn: '.ticketBox click',
                buttons: [
                {
                    text: 'Back',
                    classes: 'shepherd-button-secondary',
                    action: tour.back
                }, {
                    text: 'Next',
                    action: tour.next
                }
                ]
            }
        );


        tour.addStep(
            'Your Todos', {
                title: 'Congratulations!',
                text: 'It\'s now time to create your first Sprint!<br />Click the Create New Sprint button get started.',
                buttons:[
                {
                    text:"Close",
                    action:tour.cancel
                },
                ],
                advanceOn: '.headmenu click'
            }
        );

        tour.start();

    };



    // Make public what you want to have public, everything else is private
    return {
        showHelperModal: showHelperModal,
        hideAndKeepHidden: hideAndKeepHidden,
        startDashboardTour:startDashboardTour,
        startKanbanTour: startKanbanTour,
        startBacklogTour:startBacklogTour
    };
})();
