// Component manifest for lazy loading
export const componentManifest = {
    'datepicker': {
        module: () => import('../componentManager/DatepickerManager.mjs'),
        dependencies: ['flatpickr']
    },
    'editor': {
        module: () => import('../componentManager/EditorManager.mjs'),
        dependencies: ['tinymce']
    },
    'modal': {
        module: () => import('../componentManager/ModalManager.mjs'),
        dependencies: []
    },
    'select': {
        module: () => import('../componentManager/SelectManager.mjs'),
        dependencies: ['select']
    }
};

// Page component configurations
export const pageConfigs = {
    'login': ['modal'],
    'dashboard': ['datepicker', 'editor', 'select'],
    'default': ['modal', 'select']
};

export const getPageComponents = (pageName) => {
    return pageConfigs[pageName] || pageConfigs.default;
};
