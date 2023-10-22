<script>
    jQuery(document).ready(function() {

        @if($completedOnboarding === false)
            leantime.helperController.firstLoginModal();
        @endif


        @if($completedOnboarding == true && $showHelperModal === true)
            leantime.helperController.showHelperModal('{{ $currentModal }}', 500, 700);
        @endif

    });
</script>

