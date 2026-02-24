// Core app files
import '../../public/assets/js/app/core/toastNotifier.js';
import '../../public/assets/js/app/app.js';
import '../../public/assets/js/app/core/dropdownBridge.js';
import '../../public/assets/js/app/core/tabsController.js';
import '../../public/assets/js/app/core/snippets.js';
import '../../public/assets/js/app/core/modalManager.js';
import '../../public/assets/js/app/core/datePickers.js';
import '../../public/assets/js/app/core/dateHelper.js';

// Cross-domain controllers — needed on every page
import '../../app/Domain/Auth/Js/authController.js';
import '../../app/Domain/Comments/Js/commentsController.js';
import '../../app/Domain/Tickets/Js/ticketsController.js';
import '../../app/Domain/Tickets/Js/ticketsRepository.js';
import '../../app/Domain/Dashboard/Js/dashboardController.js';
import '../../app/Domain/Users/Js/usersController.js';
import '../../app/Domain/Users/Js/usersRepository.js';
import '../../app/Domain/Users/Js/usersService.js';
import '../../app/Domain/Canvas/Js/canvasController.js';
import '../../app/Domain/Reactions/Js/reactionsController.js';
import '../../app/Domain/Help/Js/helperController.js';
import '../../app/Domain/Help/Js/helperRepository.js';
import '../../app/Domain/Help/Js/tourFactory.js';
import '../../app/Domain/Help/Js/confettiHelper.js';
import '../../app/Domain/Help/Js/firstTaskController.js';
import '../../app/Domain/Menu/Js/menuController.js';
import '../../app/Domain/Menu/Js/menuRepository.js';
import '../../app/Domain/Setting/Js/settingController.js';
import '../../app/Domain/Setting/Js/settingRepository.js';
import '../../app/Domain/Setting/Js/settingService.js';
import '../../app/Domain/Widgets/Js/Widgetcontroller.js';
import '../../app/Domain/Calendar/Js/calendarController.js';

// Domain-specific JS — lazy-loaded based on the current module.
// Only the JS for the active page's domain is fetched, reducing initial
// payload. Top-level await ensures modules are registered before
// DOMContentLoaded (and thus before jQuery.ready callbacks fire).
// Excludes globally-imported controllers above to avoid double-registration.
const domainModules = import.meta.glob([
    '../../app/Domain/**/*.js',
    '!../../app/Domain/Auth/**',
    '!../../app/Domain/Comments/**',
    '!../../app/Domain/Tickets/Js/ticketsController.js',
    '!../../app/Domain/Tickets/Js/ticketsRepository.js',
    '!../../app/Domain/Dashboard/**',
    '!../../app/Domain/Users/**',
    '!../../app/Domain/Canvas/Js/canvasController.js',
    '!../../app/Domain/Reactions/**',
    '!../../app/Domain/Help/**',
    '!../../app/Domain/Menu/**',
    '!../../app/Domain/Setting/**',
    '!../../app/Domain/Widgets/**',
    '!../../app/Domain/Calendar/**',
]);

const currentModule = (document.body?.dataset?.module || '').toLowerCase();
if (currentModule) {
    const loads = [];
    for (const [path, loader] of Object.entries(domainModules)) {
        if (path.toLowerCase().includes(`/${currentModule}/`)) {
            loads.push(loader());
        }
    }
    await Promise.all(loads);
}
