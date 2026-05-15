<script>
    jQuery(document).ready(function () {

        // First login flow — skip entirely for client portal (commenter) users
        @if(session('userdata.role') === 'commenter')
            // no onboarding modal for client portal users
        @elseif($isFirstLogin === true || $isFirstLogin === "true")
            leantime.helperController.firstLoginModal();
        @else

            // Returning user flow
            @if(($isFirstLogin === false || $isFirstLogin === "false") && $showHelperModal === true)

                // Show the appropriate helper modal for the current page
                @if(is_array($currentModal) && isset($currentModal['autoLoad']) && ($currentModal['autoLoad'] === true || $currentModal['autoLoad'] === "true"))
                    leantime.helperController.showHelperModal('{{ $currentModal['template'] }}', 500, 700);
                @elseif(is_string($currentModal))
                    leantime.helperController.showHelperModal('{{ $currentModal}}', 500, 700);
                @endif
            @endif
        @endif

    });
</script>
