import htmx from "htmx.org";

export class BaseComponentManager {

    constructor() {
        this.instances = new Map();
        this.setupHtmxListeners();
    }

    isValidElement(element) {
        // Check for null/undefined
        if (!element) {
            return false;
        }
        // Check for empty string or whitespace-only string
        if (typeof element === 'string' && element.trim() === '') {
            return false;
        }
        // Verify it's a DOM Element
        return element instanceof Element;
    }

    setupHtmxListeners() {
        htmx.on('htmx:beforeCleanupElement', (evt) => {
            const element = evt.detail.elt;
            if (!this.isValidElement(element)) {
                console.debug(`${this.constructor.name}: Skipping cleanup for invalid element:`, element);
                return;
            }
            this.cleanupElements(element);
        });
    }

    cleanupElements(parentElement) {
        if (!this.isValidElement(parentElement)) {
            console.warn(`${this.constructor.name}: Attempted to cleanup invalid parent element:`,
                parentElement);
            return;
        }

        const elements = this.findElements(parentElement);
        elements?.forEach(element => {
            this.destroyInstance(element);
        });
    }

    initializeComponent(element, config = {}) {
        if (this.instances.has(element)) {
            console.warn(`${this.constructor.name}: Component already initialized for element:`, element);
            return this.instances.get(element);
        }

        try {
            const instance = this.createInstance(element, config);
            if (instance) {
                this.instances.set(element, instance);
                this.dispatchEvent('component:initialized', { element, instance });
            }
            return instance;
        } catch (error) {
            console.error(`${this.constructor.name}: Failed to initialize component:`, error);
            return null;
        }
    }

    destroyInstance(element) {
        if (!element) return;
        const instance = this.instances.get(element);
        if (instance) {
            this.dispatchEvent('component:beforeDestroy', { element, instance });
            this.cleanup(instance);
            this.instances.delete(element);
            this.dispatchEvent('component:destroyed', { element });
        }
    }

    getInstance(element) {
        return this.instances.get(element);
    }

    // Methods to be implemented by child classes
    findElements(parentElement) {
        throw new Error('findElements must be implemented by child class');
    }

    createInstance(element, config) {
        throw new Error('createInstance must be implemented by child class');
    }

    cleanup(instance) {
        throw new Error('cleanup must be implemented by child class');
    }

    // Event handling
    dispatchEvent(eventName, detail) {
        const event = new CustomEvent(eventName, {
            detail,
            bubbles: true,
            cancelable: true
        });
        document.dispatchEvent(event);
    }
}
