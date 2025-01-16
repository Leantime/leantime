import { BaseComponentManager } from './BaseComponentManager.mjs';
import dropdowns from "../components/dropdowns.module.mjs";

class DropdownManager extends BaseComponentManager {
    findElements(parentElement) {
        try {
            return parentElement?.querySelectorAll('select[data-component="dropdown"]') || [];
        } catch (error) {
            console.error('Error finding select elements:', error);
            return [];
        }
    }

    createInstance(element, config = {}) {
        // Show loading state
        const loadingState = jQuery(element).parent().parent().find('.select-loading-state');

        let componentConfig = element.dataset.componentConfig;
        const instance = dropdowns.initSelectable(element, componentConfig, function(){
            this.wrapper.classList.remove('opacity-0');

            if (loadingState) {
                loadingState.addClass('hidden');
            }
        });

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

export const selectManager = new DropdownManager();
export default selectManager;
