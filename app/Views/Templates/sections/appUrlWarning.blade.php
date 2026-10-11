@if(! empty($appUrlWarning))
    <x-global::elements.card tone="warning" compact class="appUrlWarning tw-m-4"
         role="alert"
         data-dismiss-key="lt-app-url-warning-{{ md5($appUrlWarning) }}">
        <div class="tw-flex tw-items-start tw-gap-2">
            <span class="fa fa-triangle-exclamation" aria-hidden="true"></span>
            <span class="tw-flex-1">{{ $appUrlWarning }}</span>
            <button type="button" class="appUrlWarningDismiss" aria-label="{{ __('buttons.close') }}"
                    style="background:none; border:0; cursor:pointer; color:inherit;">
                <span class="fa fa-xmark" aria-hidden="true"></span>
            </button>
        </div>
    </x-global::elements.card>
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
