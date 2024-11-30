import { componentRegistry } from '../components/componentManager/ComponentRegistry.mjs';
import {loadComponentsForPage} from "./componentLoader.mjs";
import modals from "../components/modals.module.mjs";

export function initializeCore() {

    try {
        // Set up global event listeners
        setupGlobalEventListeners();
        loadComponentsForPage(document, 'default');

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


