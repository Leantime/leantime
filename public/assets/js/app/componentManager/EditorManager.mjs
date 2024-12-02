import { BaseComponentManager } from './BaseComponentManager.mjs';
import tinymce from 'tinymce';
import editors, {initSimpleEditor} from "../components/editors.module.mjs";

class EditorManager extends BaseComponentManager {
    findElements(parentElement) {
        return parentElement.querySelectorAll('[data-component="editor"]');
    }

    createInstance(element, config = {}) {

        editors.initSimpleEditor(element);

        return editors.initSimpleEditor(element);
    }

    cleanup(instance) {
        instance.remove();
    }
}

export const editorManager = new EditorManager();
export default editorManager;
