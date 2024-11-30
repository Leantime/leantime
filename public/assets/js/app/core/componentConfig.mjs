// Component manifest for lazy loading
export const componentManifest = {
    'datepicker': {
        module: () => import('../components/componentManager/DatepickerManager.mjs'),
        dependencies: ['flatpickr']
    },
    'editor': {
        module: () => import('../components/componentManager/EditorManager.mjs'),
        dependencies: ['tinymce']
    },
    'modal': {
        module: () => import('../components/componentManager/ModalManager.mjs'),
        dependencies: []
    },
    'select': {
        module: () => import('../components/componentManager/SelectManager.mjs'),
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
