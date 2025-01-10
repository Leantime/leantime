import { componentRegistry } from '../componentManager/ComponentRegistry.mjs';
import { componentManifest, getPageComponents } from './componentConfig.mjs';
import { handleLoadingError } from './errorHandler.mjs';

/** @export */
export async function loadComponentsForPage(container = document, pageName = 'default') {
    const components = new Set();
    const loadedComponents = new Set();

    // Get required components for the page
    const pageComponents = getPageComponents(pageName);
    pageComponents.forEach(component => components.add(component));

    // Small delay to ensure DOM is fully updated
    if (container !== document) {
        await new Promise(resolve => setTimeout(resolve, 50));
    }

    // Add any additional components found in the DOM
    container.querySelectorAll('[data-component]').forEach(element => {
        components.add(element.dataset.component);
    });

    // Clean up old components first
    components.forEach(componentType => {
        const manager = componentRegistry.getManager(componentType);
        if (manager) manager.cleanupElements(container);
    });

    // Load required component managers
    for (const componentType of components) {
        if (!componentRegistry.getManager(componentType)) {

            try {
                const manager = await componentManifest[componentType].module();
                if (manager) {
                    // // Check if dependencies are loaded
                    // if (!await checkDependencies(componentType)) {
                    //     throw new Error(`Dependencies not loaded for ${componentType}`);
                    // }

                    componentRegistry.register(componentType, manager);
                }
            } catch (error) {
                handleLoadingError(error, {
                    component: componentType,
                    container: container
                });
                console.error(`Failed to load component manager for ${componentType}:`, error);
            }
            loadedComponents.add(componentType);
        }
    }

    // Initialize components
    components.forEach(componentType => {
        const manager = componentRegistry.getManager(componentType);
        if (!manager) {
            console.warn(`No manager found for component type: ${componentType}`);
            return;
        }

        const elements = container.querySelectorAll(`[data-component="${componentType}"]`);
        elements.forEach(element => {

            if (!element.hasAttribute('data-component-initialized')) {
                manager.initializeComponent(element);
                element.setAttribute('data-component-initialized', 'true');
            }
        });
    });

    window.addEventListener('unload', () => {
        components.forEach(componentType => {
            const manager = componentRegistry.getManager(componentType);
            if (manager) manager.cleanupElements(document);
        });
    });
}

// Default export for backward compatibility
export default {
    loadComponentsForPage
};
