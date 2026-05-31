<?php

namespace Leantime\Core\Events\Htmx;

/**
 * Canonical client UI command events (the `lt:ui:*` plane).
 *
 * These are imperative commands to the JS layer (show a toast, close the modal, refresh the page),
 * as opposed to domain data events ("entity X changed") which live in per-domain Htmx{Domain}Events
 * enums. There is exactly one home for UI commands so casing/naming never drifts again — plugins
 * reuse these cases rather than minting their own `lt:ui:*` strings.
 *
 * Legacy string equivalents (e.g. HTMX.ShowNotification, closeModal) are dual-emitted during the
 * migration window via {@see HtmxEvents::expand()} so existing listeners keep working.
 */
enum HtmxUiEvents: string implements HtmxEvent
{
    use InteractsWithHtmxEvents;

    /** Fetch + show the latest growl notification. Replaces 'HTMX.ShowNotification'. */
    case Notify = 'lt:ui:notify';

    /** Close the top-most modal. Replaces 'closeModal' / 'HTMX.closemodal' / 'Htmx.CloseModal'. */
    case ModalClose = 'lt:ui:modal.close';

    /** Open a modal for the current url hash. */
    case ModalOpen = 'lt:ui:modal.open';

    /** Refresh the main page url in the background. */
    case UrlRefresh = 'lt:ui:url.refresh';
}
