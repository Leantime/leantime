import { componentRegistry } from '../componentManager/ComponentRegistry.mjs';
import {loadComponentsForPage} from "./componentLoader.mjs";
import modals from "../components/modals.module.mjs";
import onDocumentReady from "./on-document-ready.module.mjs";

export function initializeCore() {

    try {

        onDocumentReady();

        //Pageload compent loader
        loadComponentsForPage(document, 'default');

        // Set up global event listeners for component loader
        setupGlobalEventListeners();

        console.log('Core initialization complete');

    } catch (error) {

        console.error('Core initialization failed:', error);
        throw error;
    }
}

// Setup global event listeners
function setupGlobalEventListeners() {

    // HTMX content load handler
    document.addEventListener('htmx:afterSettle', async (event) => {
        loadComponentsForPage(event.detail.target);
    });

    modals.initModalLoader();
}


