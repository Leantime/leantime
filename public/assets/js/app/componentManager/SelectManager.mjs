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
        // Show loading state
        const loadingState = element.parentElement.querySelector('.select-loading-state');
        if (loadingState) {
            loadingState.classList.remove('hidden');
        }

        let componentConfig = element.dataset.componentConfig;
        const instance = selects.initSelect(element, componentConfig, function(){
            this.wrapper.classList.remove('opacity-0');
        });

        // Hide loading state and show select
        if (loadingState) {
            loadingState.classList.add('hidden');
        }



        return instance;
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
