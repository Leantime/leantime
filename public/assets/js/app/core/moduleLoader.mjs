class ModuleLoader {
    constructor() {
        this.loadedModules = new Map();
        this.loadingPromises = new Map();
    }

    async load(modulePath) {
        console.log("loading:" + modulePath);

        const formattedPath = modulePath;
        console.debug("Formatted module path:", formattedPath);

        if (this.loadedModules.has(modulePath)) {
            return this.loadedModules.get(modulePath);
        }

        // Ensure proper handling of already loading modules
        if (this.loadingPromises.has(modulePath)) {
            return this.loadingPromises.get(modulePath);
        }

        try {
            const loadPromise = this.importModule(formattedPath);
            this.loadingPromises.set(modulePath, loadPromise);

            const module = await loadPromise;

            // Properly handle both default and named exports
            // Ensure compatibility with ES modules and UMD
            let processedModule;
            if (module.default) {
                processedModule = module.default;
            } else {
                processedModule = module;
            }
            // Debugging loaded module exports
            console.debug('Loaded module exports:', {
                path: modulePath,
                exports: Object.keys(module), processed: processedModule
            });

            this.loadedModules.set(modulePath, processedModule);
            this.loadingPromises.delete(modulePath);
            return processedModule;
        } catch (error) {
            this.loadingPromises.delete(modulePath);
            console.error('Error loading module:', modulePath, error);
            throw error;
        }
    }

    // Ensure dynamic imports work with proper paths
    async importModule(modulePath) {
        console.log("Import:"+ modulePath);
        await import(
            /* webpackExports: ["default", "named"] */
            `${modulePath}`).then((module) => {
                console.log(module);

                return module;
        });




        // Handle both ECMAScript Module and Universal Module Definition modules
        if (module.__esModule) {
            return module.default || module;
        } else {
            console.debug("Fallback to UMD or global namespace for:", modulePath);
        }

        // For Universal Module Definition modules, check the global namespace
        const parts = modulePath.split('/');
        const domainName = parts[2];
        const controllerName = parts[parts.length - 1];

        console.log(parts);
        console.log(domainName);
        console.log(controllerName);

        return window?.[domainName]?.[controllerName] || module;
        // Add fallback for debugging
        console.warn("Module not found in global namespace:", modulePath);
    }

    // Helper method to format the module path
    formatModulePath(modulePath) {
        // Remove leading slash and .js extension if present, and convert to lowercase
        return modulePath.replace(/^\//, '').replace(/\.js$/, '');
    }

    debug(modulePath) {
        console.debug(`Attempting to load module: ${modulePath}`);
    }

    preload(modulePath) {
        const link = document.createElement('link');
        link.rel = 'modulepreload';
        link.href = modulePath;
        document.head.appendChild(link);
    }
}

export const moduleLoader = new ModuleLoader();
export default moduleLoader;
