import { BaseComponentManager } from './BaseComponentManager.mjs';
import TomSelect from 'tom-select/dist/esm/tom-select.complete.js';

class SelectManager extends BaseComponentManager {
    findElements(parentElement) {
        try {
            return parentElement?.querySelectorAll('select[data-component="select"]') || [];
        } catch (error) {
            console.error('Error finding select elements:', error);
            return [];
        }
    }

    createInstance(element, config = {}) {
        const defaultConfig = {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        };

        const mergedConfig = { ...defaultConfig, ...config };
        return new TomSelect(element, mergedConfig);
    }

    cleanup(instance) {
        try {
            if (instance && typeof instance.destroy === 'function') {
                instance.destroy();
            }
        } catch (error) {
            console.error('Error cleaning up select instance:', error);
        }
    }
}

export const selectManager = new SelectManager();
export default selectManager;
