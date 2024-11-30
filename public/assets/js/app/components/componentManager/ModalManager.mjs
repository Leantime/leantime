import { BaseComponentManager } from './BaseComponentManager.mjs';

class ModalManager extends BaseComponentManager {
    constructor() {
        super();
        this.modalStack = [];
        this.setupKeyboardListener();
    }

    findElements(parentElement) {
        try {
            return parentElement?.querySelectorAll('[data-component="modal"]') || [];
        } catch (error) {
            console.error('Error finding modal elements:', error);
            return [];
        }
    }

    createInstance(element, config = {}) {
        const defaultConfig = {
            closeOnEscape: true,
            closeOnBackdrop: true,
            onOpen: () => {},
            onClose: () => {},
            ...config
        };

        const modal = {
            element,
            config: defaultConfig,
            isOpen: false,
            zIndex: 1000 + this.modalStack.length,

            open: () => {
                modal.isOpen = true;
                element.style.display = 'block';
                element.style.zIndex = modal.zIndex;
                this.modalStack.push(modal);
                modal.config.onOpen();
                this.dispatchEvent('modal:opened', { modal });
            },

            close: () => {
                modal.isOpen = false;
                element.style.display = 'none';
                const index = this.modalStack.indexOf(modal);
                if (index > -1) {
                    this.modalStack.splice(index, 1);
                }
                modal.config.onClose();
                this.dispatchEvent('modal:closed', { modal });
            }
        };

        this.setupModalListeners(modal);
        return modal;
    }

    setupModalListeners(modal) {
        const closeButton = modal.element.querySelector('[data-modal-close]');
        if (closeButton) {
            closeButton.addEventListener('click', () => modal.close());
        }

        if (modal.config.closeOnBackdrop) {
            modal.element.addEventListener('click', (e) => {
                if (e.target === modal.element) {
                    modal.close();
                }
            });
        }
    }

    setupKeyboardListener() {
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const topModal = this.modalStack[this.modalStack.length - 1];
                if (topModal?.config.closeOnEscape) {
                    topModal.close();
                }
            }
        });
    }

    cleanup(instance) {
        if (!instance) {
            console.warn('Attempted to cleanup undefined modal instance');
            return;
        }
        instance.close();
    }

    closeAll() {
        [...this.modalStack].forEach(modal => modal.close());
    }
}

export const modalManager = new ModalManager();
export default modalManager;
