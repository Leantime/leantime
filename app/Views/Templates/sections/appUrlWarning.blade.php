@if(! empty($appUrlWarning))
    <div class="appUrlWarning tw-flex tw-items-start tw-gap-2 tw-m-4 tw-p-3"
         role="alert"
         data-dismiss-key="lt-app-url-warning-{{ md5($appUrlWarning) }}"
         style="border:1px solid var(--yellow, #e0b000); border-radius:var(--box-radius-small); background:var(--secondary-background);">
        <span class="fa fa-triangle-exclamation" aria-hidden="true"></span>
        <span class="tw-flex-1">{{ $appUrlWarning }}</span>
        <button type="button" class="appUrlWarningDismiss" aria-label="{{ __('buttons.close') }}"
                style="background:none; border:0; cursor:pointer; color:inherit;">
            <span class="fa fa-xmark" aria-hidden="true"></span>
        </button>
    </div>
    <script>
        (function () {
            var banner = document.currentScript.previousElementSibling;
            var key = banner.getAttribute('data-dismiss-key');
            try {
                if (window.localStorage.getItem(key) === '1') {
                    banner.remove();
                    return;
                }
            } catch (e) {}
            banner.querySelector('.appUrlWarningDismiss').addEventListener('click', function () {
                try { window.localStorage.setItem(key, '1'); } catch (e) {}
                banner.remove();
            });
        })();
    </script>
@endif
