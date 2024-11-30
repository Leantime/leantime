import { componentRegistry } from '../components/componentManager/ComponentRegistry.mjs';
import { selectManager } from '../components/componentManager/SelectManager.mjs';
import { editorManager } from '../components/componentManager/EditorManager.mjs';
import { datepickerManager } from '../components/componentManager/DatepickerManager.mjs';
import { modalManager } from '../components/componentManager/ModalManager.mjs';

document.addEventListener('DOMContentLoaded', function() {
    componentRegistry.register('select', selectManager);
    componentRegistry.register('editor', editorManager);
    componentRegistry.register('datepicker', datepickerManager);
    componentRegistry.register('modal', modalManager);
});
