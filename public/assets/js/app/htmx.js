window.htmx = require('htmx.org');

function showElement(element) {
  if (element) element.style.display = 'block';
}

function hideElement(element) {
  if (element) element.style.display = 'none';
}

function toggleLoader(event, action) {
  const loader = event.target.querySelector('.htmx-loader');
  if (!loader) return;
  action === 'show'
    ? loader.style.display = 'flex'
    : hideElement(loader);
}

function toggleErrorMessage(event, action, message = '') {
  const errorElement = event.target.querySelector('.error-message');

  if (event.detail.boosted && action === 'show' && !event.detail._errorHandled) {
    jQuery.growl({ message: message, style: "error" });

    event.detail._errorHandled = true;
  } else if (errorElement) {
    action === 'show'
      ? showElement(errorElement)
      : hideElement(errorElement);
  } else if (action === 'show') {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    event.target.prepend(errorDiv);
  }
}

function initializeHtmx() {
  document.body.addEventListener('htmx:beforeRequest', event => {
    toggleLoader(event, 'show');
    toggleErrorMessage(event, 'hide');
  });

  document.body.addEventListener('htmx:afterRequest', event => {
    toggleLoader(event, 'hide');
  });

  document.body.addEventListener('htmx:responseError', event => {
    toggleLoader(event, 'hide');
    toggleErrorMessage(
      event,
      'show',
      "There is an issue loading the section. Please try again later."
    );
  });
}

jQuery(function () {
  initializeHtmx();
})
