import { BaseComponentManager } from './BaseComponentManager.mjs';
import Flatpickr from  'flatpickr'
import datePickersModule, {initDateTimePicker} from "../components/datePickers.module.mjs";

class DatepickerManager extends BaseComponentManager {
    findElements(parentElement) {
        return parentElement.querySelectorAll('[data-component="datepicker"]');
    }

    createInstance(element, config = {}) {
        let componentConfig = element.dataset.componentConfig;
        return datePickersModule.initDateTimePicker(element, componentConfig);
    }

    cleanup(instance) {
        instance.destroy();
    }
}

export const datepickerManager = new DatepickerManager();
export default datepickerManager;
