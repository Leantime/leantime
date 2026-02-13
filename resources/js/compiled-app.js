// Core app files
import '../../public/assets/js/app/core/toastNotifier.js';
import '../../public/assets/js/app/app.js';
import '../../public/assets/js/app/core/dropdownBridge.js';
import '../../public/assets/js/app/core/editors.js';
import '../../public/assets/js/app/core/snippets.js';
import '../../public/assets/js/app/core/modalManager.js';
import '../../public/assets/js/app/core/tableHandling.js';
import '../../public/assets/js/app/core/datePickers.js';
import '../../public/assets/js/app/core/dateHelper.js';
import '../../public/assets/js/app/core/accessibility.js';

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

// Domain-specific JS — lazy loaded based on current page.
// Excludes globally-imported controllers above.
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
]);

// --- Domain JS loader (reusable for initial load + hx-boost navigation) ---

const loadedDomains = new Set();

/**
 * Load domain-specific JS modules by name.
 * Safe to call multiple times — already-loaded domains are skipped.
 *
 * @param {string} moduleName  Lowercase domain name (e.g. 'calendar', 'tickets')
 * @returns {Promise}
 */
function loadDomainJs(moduleName) {
    if (!moduleName || loadedDomains.has(moduleName)) return Promise.resolve();
    loadedDomains.add(moduleName);

    const loadPromises = [];
    for (const [path, loader] of Object.entries(domainModules)) {
        const match = path.match(/Domain\/([^/]+)\//);
        if (match && match[1].toLowerCase() === moduleName) {
            loadPromises.push(loader());
        }
    }
    return Promise.all(loadPromises);
}

/**
 * Extract the domain module name from a URL path.
 * Handles: /module/action, /hx/module/action, /module/action/id
 *
 * @param {string} url  Full URL or pathname
 * @returns {string}    Lowercase module name or empty string
 */
function getModuleFromUrl(url) {
    try {
        const path = new URL(url, window.location.origin).pathname;
        const segments = path.replace(/^\/hx\//, '/').split('/').filter(Boolean);
        return segments[0]?.toLowerCase() || '';
    } catch (e) {
        return '';
    }
}

// Initial page load — use data-module from <body> (set by server)
loadDomainJs(document.body?.dataset?.module?.toLowerCase());

// --- hx-boost navigation support ---

// Pre-load domain JS when a boosted request starts (runs in parallel with fetch)
document.addEventListener('htmx:configRequest', function (evt) {
    if (!evt.detail.boosted) return;
    const module = getModuleFromUrl(evt.detail.path || '');
    if (module) loadDomainJs(module);
});

// After boost swap: update data-module on <body> so subsequent code sees the right domain
document.addEventListener('htmx:pushedIntoHistory', function () {
    const module = getModuleFromUrl(window.location.pathname);
    if (module) document.body.dataset.module = module;
});

// Back/forward browser navigation: update data-module + load domain JS
document.addEventListener('htmx:historyRestore', function () {
    const module = getModuleFromUrl(window.location.pathname);
    if (module) {
        document.body.dataset.module = module;
        loadDomainJs(module);
    }
});
