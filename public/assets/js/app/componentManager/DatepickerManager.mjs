import { BaseComponentManager } from './BaseComponentManager.mjs';
import Flatpickr from  'flatpickr'
import datePickersModule, {initDateTimePicker} from "../components/datePickers.module.mjs";

class DatepickerManager extends BaseComponentManager {
    findElements(parentElement) {
        return parentElement.querySelectorAll('[data-component="datepicker"]');
    }

    createInstance(element, config = {}) {

        return datePickersModule.initDateTimePicker(element);
    }

    cleanup(instance) {
        instance.destroy();
    }
}

export const datepickerManager = new DatepickerManager();
export default datepickerManager;
