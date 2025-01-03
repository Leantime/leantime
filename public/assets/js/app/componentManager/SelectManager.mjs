import { BaseComponentManager } from './BaseComponentManager.mjs';
import selects from "../components/selects.module.mjs";

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

        let componentConfig = element.dataset.componentConfig;
        console.log(componentConfig);
        return selects.initSelect(element, componentConfig);
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
