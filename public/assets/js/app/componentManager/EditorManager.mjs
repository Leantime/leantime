import { BaseComponentManager } from './BaseComponentManager.mjs';
import tinymce from 'tinymce';
import editors, {initEditor, initSimpleEditor} from "../components/editors.module.mjs";
import dropdowns from "../components/dropdowns.module.mjs";

class EditorManager extends BaseComponentManager {
    findElements(parentElement) {
        return parentElement.querySelectorAll('[data-component="editor"]');
    }

    createInstance(element, config = {}) {

        const loadingState = jQuery(element).parent().parent().find('.editor-loading-state');

        let componentConfig = element.dataset.componentConfig;
        const instance = editors.initEditor(element, componentConfig, function(){
        });

        if (loadingState) {
            loadingState.addClass('hidden');
        }

        return instance;
    }

    cleanup(instance) {
        instance.remove();
    }
}

export const editorManager = new EditorManager();
export default editorManager;
