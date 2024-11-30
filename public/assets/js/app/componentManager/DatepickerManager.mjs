import { BaseComponentManager } from './BaseComponentManager.mjs';
import Flatpickr from  'flatpickr'

class DatepickerManager extends BaseComponentManager {
    findElements(parentElement) {
        return parentElement.querySelectorAll('[data-component="datepicker"]');
    }

    createInstance(element, config = {}) {
        const defaultConfig = {
            format: 'yyyy-mm-dd',
            autohide: true,
            todayHighlight: true,
        };

        return new Flatpickr(element, defaultConfig);
    }

    cleanup(instance) {
        instance.destroy();
    }
}

export const datepickerManager = new DatepickerManager();
export default datepickerManager;
