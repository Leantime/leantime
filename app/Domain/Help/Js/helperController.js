leantime.helperController = (function () {

    var setDontShowAgain = function(module, dontShow) {
        if (dontShow) {
            leantime.helperRepository.updateUserModalSettings(module, true);
        } else {
            leantime.helperRepository.updateUserModalSettings(module, false);
        }
    };

    var startMyWorkDashboardTour = function () {
        leantime.modals.setCustomModalCallback(function(){});
        leantime.modals.closeModal();

        // Use the tour factory instead
        return leantime.tourFactory.startTour('myWorkDashboard');
    };

    var closeModal = function() {
        leantime.modals.setCustomModalCallback(function(){});
        leantime.modals.closeModal();

    }

    var startProjectDashboardTour = function () {
        leantime.modals.setCustomModalCallback(function(){});
        leantime.modals.closeModal();

        leantime.helperRepository.updateUserModalSettings("projectDashboard");
        return leantime.tourFactory.startTour('projectDashboard');
    };

    var startKanbanTour = function () {
        if(jQuery.nmTop()) {
            jQuery.nmTop().close();
        }

        leantime.helperRepository.updateUserModalSettings("kanbanBoard");
        return leantime.tourFactory.startTour('kanbanBoard');
    };

    var startMilestoneTour = function () {
        if(jQuery.nmTop()) {
            jQuery.nmTop().close();
        }

        leantime.helperRepository.updateUserModalSettings("milestoneView");
        return leantime.tourFactory.startTour('milestoneView');
    };

    var startGoalTour = function () {
        leantime.modals.setCustomModalCallback(function(){});
        leantime.modals.closeModal();

        leantime.helperRepository.updateUserModalSettings("goals");
        return leantime.tourFactory.startTour('goalsView');
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
                        },
                        afterShowCont: function() {
                            htmx.process(".nyroModalCont");
                        }
                    }
                }
            );
        });

    };

    var hideAndKeepHidden = function (module) {

        leantime.helperRepository.updateUserModalSettings(module);
        leantime.modals.setCustomModalCallback(function(){});
        leantime.modals.closeModal();

    };

    return {
        showHelperModal: showHelperModal,
        hideAndKeepHidden: hideAndKeepHidden,
        setDontShowAgain: setDontShowAgain,
        closeModal:closeModal,
        startMyWorkDashboardTour: startMyWorkDashboardTour,
        startProjectDashboardTour:startProjectDashboardTour,
        startKanbanTour: startKanbanTour,
        startMilestoneTour: startMilestoneTour,
        startGoalTour:startGoalTour,
        firstLoginModal:firstLoginModal
    };
})();
