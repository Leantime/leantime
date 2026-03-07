leantime.tourFactory = (function () {

    /**
     * Create a new tour with default settings
     * @param {string} tourName - The name of the tour
     * @returns {Object} - Shepherd tour object
     */
    var createTour = function(tourName) {
        return new Shepherd.Tour({
            useModalOverlay: true,
            defaultStepOptions: {
                classes: 'shepherd-theme-arrows',
                scrollTo: false,
                cancelIcon: {
                    enabled: true
                }
            },
            tourName: tourName
        });
    };

    /**
     * Register a tour completion
     * @param {string} tourName - The name of the tour that was completed
     */
    var registerTourCompletion = function(tourName) {
        leantime.helperRepository.updateUserModalSettings(tourName);

        // Track tour completion for analytics
        if (typeof _paq !== 'undefined') {
            _paq.push(['trackEvent', 'Tour', 'Completed', tourName]);
        }
    };

    /**
     * Get tour definitions for a specific tour
     * @param {string} tourName - The name of the tour
     * @returns {Array} - Array of tour step definitions
     */
    var getTourDefinition = function(tourName) {
        const tourDefinitions = {
            'myWorkDashboard': [
                {
                    id: "welcome-step",
                    title: "👋 Welcome to the Dashboard Tour",
                    text: "Let's start with a few basics to get you up to speed on the navigation and different elements inside Leantime.",
                },
                {
                    id: "top-nav-step",
                    title: "Work Modes",
                    text: "The top navigation shows you the current 'work mode' you're in. You can be inside a project, your personal work area, or in the company mode.",
                    attachTo: { element: '.work-modes', on: 'bottom' }
                },
                {
                    id: "menu-step",
                    title: "Left Navigation",
                    text: "The left navigation breaks down the current work mode and gives you access to different areas. Everything presented here is part of the currently selected work mode above.",
                    attachTo: { element: '.leftpanel', on: 'right' }
                },
                {
                    id: "my-menu",
                    title: "My Profile Bar",
                    text: "Here you'll find links to your account as well as notifications and latest news about Leantime.",
                    attachTo: { element: '.headmenu.pull-right', on: 'bottom' }
                },
                {
                    id: "dashboard-widgets",
                    title: "Your Dashboard Widgets",
                    text: "Your dashboard is broken up into various widgets showing you focused information about your work.",
                    attachTo: { element: '.primaryContent', on: 'top' }
                },
                {
                    id: "dashboard-widgets-dandd",
                    title: "Customize Your Dashboard",
                    text: "Widgets can be resized and moved around using the drag and drop functionality.",
                    attachTo: { element: '#widget_wrapper_todos .grid-handler-top', on: 'bottom' }
                },
                {
                    id: "my-todo-widget",
                    title: "My To-Dos",
                    text: "The My To-Do widget shows you all the tasks that are currently assigned to you.",
                    attachTo: { element: '#widget_wrapper_todos', on: 'top' }
                },
                {
                    id: "my-todo-widget2",
                    title: "Group To-Dos",
                    text: "You can use the dropdowns here to filter your tasks by project or group them by priority, status, project, or dates.",
                    attachTo: { element: '#yourToDoContainer > .clear', on: 'bottom' }
                },
                {
                    id: "my-todo-widget4",
                    title: "Sort To-Dos",
                    text: "Each To-Do can be dragged and dropped to change its order. You can also drag and drop a task to the calendar to schedule it.",
                    attachTo: { element: '#yourToDoContainer .sortable-item:first-child', on: 'bottom' }
                },
                {
                    id: "my-todo-widget-timer",
                    title: "Start the Timer",
                    text: "When you're ready to start working on a task, just click the start timer button.",
                    attachTo: { element: '#yourToDoContainer .sortable-item:first-child .timerContainer', on: 'bottom' }
                },
                {
                    id: "my-todo-widget-complete",
                    title: "Complete a Task",
                    text: "Once a task is complete, you can mark it as done by clicking the status dropdown.",
                    attachTo: { element: '#yourToDoContainer .sortable-item:first-child .statusDropdown', on: 'bottom' }
                },
                {
                    id: "my-todo-widget-add",
                    title: "Add More Tasks",
                    text: "You can add more tasks by clicking the plus button in each section, or by using the 3-dot menu next to each task.",
                    attachTo: { element: '#yourToDoContainer .add-task-button', on: 'bottom' }
                },
                {
                    id: "finish",
                    title: "Congratulations!",
                    text: "You've completed the My Work dashboard tour. Head to <a href='"+leantime.appUrl+"/dashboard/show'>your project</a> to learn more about project management in Leantime.",
                },
            ],
            'projectDashboard': [
                {
                    id: "left-nav",
                    title: "Project Menu",
                    text: "The project menu organizes your project into different sections: <strong>Data Room</strong> - to host all your files and information, <strong>Think</strong> - to strategize, ideate and define, <strong>Make</strong> - to manage goals, milestones and tasks.",
                    attachTo: { element: '.leftmenu ul', on: 'left' }
                },
                {
                    id: 'project-selector',
                    title: "Project Selector",
                    text: "Use the project selector to jump between projects. The left menu shows everything related to your current project.",
                    attachTo: { element: '.bigProjectSelector', on: 'bottom' }
                },
                {
                    id: 'project-checklist',
                    title: "Checklist",
                    text: "The project checklist is a quick reference to see if your project contains all the necessary information for successful execution.",
                    attachTo: { element: '#progressForm', on: 'bottom' }
                },
                {
                    id: 'project-status',
                    title: "Quick Status Updates",
                    text: "You can use status updates to quickly share the progress of your project with your team using red, yellow and green colors. The status will be visible across the application.",
                    attachTo: { element: '.project-updates', on: 'left' }
                },
                {
                    id: 'project-progress',
                    title: "Progress",
                    text: "The project progress indicator shows you how much work you have completed. It accounts for various task sizes, team velocity, and project milestones.",
                    attachTo: { element: '.project-progress', on: 'left' }
                },
                {
                    id: 'latest-tasks',
                    title: "Latest Tasks",
                    text: "The list of latest tasks shows you the most recent tasks added to your project and can be used as a project inbox.",
                    attachTo: { element: '.latest-todos', on: 'left' }
                },
                {
                    id: 'teams',
                    title: "Team",
                    text: "The team box shows you the members of this project.",
                    attachTo: { element: '.team-container', on: 'top' }
                },
                {
                    id: 'finished',
                    title: "Congratulations!",
                    text: "You've completed the project dashboard tour. Head to the <a href='"+leantime.appUrl+"/tickets/showKanban'>To-Dos</a> to learn more about the various ways to manage your tasks in Leantime.",
                }
            ],
            'kanbanBoard': [
                {
                    id: 'kanban-overview',
                    title: "Your Kanban Board",
                    text: "This is your Kanban board. It helps you visualize your work and limit work-in-progress.",
                    attachTo: { element: '.kanban-board-wrapper', on: 'top' }
                },
                {
                    id: 'kanban-columns',
                    title: "Work Flow",
                    text: "Tasks move from left to right as they progress. Drag and drop cards to update their status.",
                    attachTo: { element: '.column', on: 'right' }
                },
                {
                    id: 'kanban-columns2',
                    title: "Flexible Columns",
                    text: "Using the 3-dot menu, you can add or remove columns and rename them.",
                    attachTo: { element: '.column .widgettitle .inlineDropDownContainer', on: 'right' }
                },
                {
                    id: 'kanban-filter',
                    title: "Filter Tasks",
                    text: "You can filter your tasks by various fields like priority, status, project, or dates.",
                    attachTo: { element: '.filterWrapper > .btn', on: 'bottom' }
                },
                {
                    id: 'kanban-group',
                    title: "Swimlanes",
                    text: "Additionally, you can group your tasks to create Kanban swimlanes. This helps you visualize your work by team members, priority, or milestones.",
                    attachTo: { element: '.filterWrapper > .btn-group', on: 'bottom' }
                },
                {
                    id: 'kanban-sprints',
                    title: "Sprints",
                    text: "If you manage your work in sprints, you can use the dropdown here to select, create, and update sprints.",
                    attachTo: { element: '.pageheader .dropdown', on: 'bottom' }
                },
                {
                    id: 'kanban-congrats',
                    title: "Congratulations!",
                    text: "This concludes the Kanban tour. Head to the <a href='"+leantime.appUrl+"/tickets/showKanban'>Milestones</a> to learn how to create and manage milestones in Leantime.",
                }
            ],
            'milestoneView': [
                {
                    id: 'milestone-overview',
                    title: "Milestones",
                    text: "Milestones help you track major outcomes and project phases.",
                    attachTo: { element: '.gantt-wrapper', on: 'top' }
                },
                {
                    id: 'milestone-drag',
                    title: "Drag & Sort",
                    text: "Each bar represents one milestone. You can drag them along the timeline, reorder, and resize them. Everything you do on this screen updates the timing of your milestones.",
                    attachTo: { element: '.gantt-wrapper', on: 'top' }
                },
                {
                    id: 'milestone-filter',
                    title: "Filter",
                    text: "You can filter your milestones and also view tasks that are part of them.",
                    attachTo: { element: '.filterWrapper > .btn', on: 'bottom' }
                },
                {
                    id: 'milestone-timeframes',
                    title: "Timeframes",
                    text: "You can change the timeframe of the timeline view to see more of the year or dive deep into a daily breakdown.",
                    attachTo: { element: '.col-md-4 .pull-right', on: 'bottom' }
                },
                {
                    id: 'milestone-congrats',
                    title: "Congratulations!",
                    text: "This concludes the milestone tour. Head to the <a href='"+leantime.appUrl+"/goalcanvas/dashboard'>Goals</a> to learn how to create and manage goals in Leantime.",
                },
            ],
            'goalsView': [
                {
                    id: 'goals-overview',
                    title: "Goals",
                    text: "Goals help you track measurable impact on your projects.",
                },
                {
                    id: 'goal-parts',
                    title: "Objectives & Metrics",
                    text: "Each goal is made up of an Objective (what you're trying to accomplish) and a Metric (how you'll measure it).",
                    attachTo: { element: '.ticketBox', on: 'top' }
                },
                {
                    id: 'goal-progress',
                    title: "Goal Progress",
                    text: "As you update your goal metrics, the progress bar will show you how far along you are.",
                    attachTo: { element: '.ticketBox > .row > .col-md-12 .progress', on: 'bottom' }
                },
                {
                    id: 'milestone-connection',
                    title: "Milestones & Goals",
                    text: "You can connect goals to milestones to track the task-level progress of your goals. This helps identify gaps in your project plan.",
                    attachTo: { element: '.ticketBox.fixed', on: 'bottom' }
                },
                {
                    id: 'milestone-congrats',
                    title: "Congratulations!",
                    text: "This concludes the goals tour. Milestones, Goals, and To-Dos are the basic building blocks in Leantime. Use them to break down your work into manageable chunks. Head to the <a href='"+leantime.appUrl+"/tickets/showKanban'>Kanban Board</a> to review your tasks.",
                },
            ]
        };

        return tourDefinitions[tourName] || [];
    };

    /**
     * Build a tour from a definition
     * @param {string} tourName - The name of the tour to build
     * @returns {Object} - Configured Shepherd tour object
     */
    var buildTour = function(tourName) {
        const tour = createTour(tourName);
        const steps = getTourDefinition(tourName);

        steps.forEach((step, index) => {
            const isFirst = index === 0;
            const isLast = index === steps.length - 1;

            // Configure buttons based on position in tour
            const buttons = [];

            if (!isFirst) {
                buttons.push({
                    text: leantime.i18n.__("tour.back"),
                    classes: 'shepherd-button-secondary',
                    action: tour.back
                });
            }

            if (isLast) {
                buttons.push({
                    text: leantime.i18n.__("tour.finish"),
                    action: function() {
                        registerTourCompletion(tourName);
                        confetti();
                        tour.complete();
                    }
                });
            } else {
                buttons.push({
                    text: leantime.i18n.__("tour.next"),
                    action: tour.next
                });
            }

            // Add cancel button for all steps
            if (!isLast) {
                buttons.unshift({
                    text: leantime.i18n.__("tour.cancel"),
                    classes: 'shepherd-button-secondary',
                    action: tour.cancel
                });
            }

            // Add the step to the tour
            tour.addStep({
                ...step,
                buttons: buttons
            });
        });

        // Add event handlers
        tour.on('complete', function() {
            registerTourCompletion(tourName);
        });

        return tour;
    };

    /**
     * Start a specific tour
     * @param {string} tourName - The name of the tour to start
     */
    var startTour = function(tourName) {
        const tour = buildTour(tourName);
        tour.start();
        return tour;
    };

    return {
        createTour: createTour,
        buildTour: buildTour,
        startTour: startTour,
        getTourDefinition: getTourDefinition
    };
})();
