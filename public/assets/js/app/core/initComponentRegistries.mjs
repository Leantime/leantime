import { componentRegistry } from '../componentManager/ComponentRegistry.mjs';
import { selectManager } from '../componentManager/SelectManager.mjs';
import { editorManager } from '../componentManager/EditorManager.mjs';
import { datepickerManager } from '../componentManager/DatepickerManager.mjs';
import { modalManager } from '../componentManager/ModalManager.mjs';

document.addEventListener('DOMContentLoaded', function() {
    componentRegistry.register('select', selectManager);
    componentRegistry.register('editor', editorManager);
    componentRegistry.register('datepicker', datepickerManager);
    componentRegistry.register('modal', modalManager);
});
