export function handleLoadingError(error, context) {
    const { component, container } = context;

    // Log error to console with context
    console.error(`Failed to load component: ${component}`, error);

    // Find all instances of the failed component
    const elements = container.querySelectorAll(`[data-component="${component}"]`);

    elements.forEach(element => {
        // Add error class
        element.classList.add('component-load-error');

        // Show fallback content if available
        const fallback = element.querySelector('[data-fallback]');
        if (fallback) {
            fallback.style.display = 'block';
        }

        // Dispatch error event
        element.dispatchEvent(new CustomEvent('component:loadError', {
            bubbles: true,
            detail: {
                error,
                component
            }
        }));
    });

    // Report error to monitoring service if available
    if (window.Sentry) {
        window.Sentry.captureException(error, {
            tags: {
                component,
                type: 'componentLoadError'
            }
        });
    }
}

export default {
    handleLoadingError
};
