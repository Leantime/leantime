class ComponentRegistry {
    constructor() {
        this.managers = new Map();
        this.componentManifest = {};
        this.loadingPromises = new Map();
    }

    async register(componentType, manager) {
        if (this.managers.has(componentType)) {
            console.warn(`Manager for ${componentType} already registered`);
            return;
        }

        const managerInstance = manager.default || manager;
        this.managers.set(componentType, managerInstance);
        return managerInstance;
    }

    getManager(componentType) {
        return this.managers.get(componentType);
    }

    /**
     * Loads a component manager based on the component type
     * @param {string} componentType - The type of component to load
     * @returns {Promise<Object>} The loaded component manager
     * @throws {Error} If component type is not found in manifest or fails to load
     */
    async loadComponent(componentType) {
        if (!this.componentManifest[componentType]) {
            throw new Error(`Component type '${componentType}' not found in manifest`);
        }

        try {
            // Load the component module using dynamic import
            const moduleConfig = this.componentManifest[componentType];
            const componentModule = await moduleConfig.module();

            // Register the loaded component
            await this.register(componentType, componentModule);

            return this.getManager(componentType);
        } catch (error) {
            throw new Error(`Failed to load component '${componentType}': ${error.message}`);
        }
    }


    async ensureLoaded(componentType) {
        if (this.loadingPromises.has(componentType)) {
            return this.loadingPromises.get(componentType);
        }

        const loadPromise = this.loadComponent(componentType);
        this.loadingPromises.set(componentType, loadPromise);
        return loadPromise;
    }
}

export const componentRegistry = new ComponentRegistry();
export default componentRegistry;
