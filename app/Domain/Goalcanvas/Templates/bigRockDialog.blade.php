<h4 class="widgettitle title-light">
    <i class="fa-solid fa-mountain"></i>
    {{ empty($bigRock['title']) ? __('label.create_new_goalboard') : __('label.goalboard') }} {{ $bigRock['title'] }}
</h4>

<form class="formModal" method="post"
    action="{{ BASE_URL }}/goalcanvas/bigRock/{{ !empty($bigRock['id']) ? $bigRock['id'] : '' }}">

    <br />
    <label>{{ __('label.goal_description') }}</label>
    <input type="text" name="title" id="wikiTitle" value="{{ $bigRock['title'] }}" class="tw:w-full" /><br />

    <br />
    <div class="tw:flex tw:justify-between tw:items-center">
        <input type="submit" value="{{ __('buttons.save') }}" id="saveBtn" />
    </div>

</form>

<script>
    jQuery(document).ready(function() {

        @if (isset($_GET['closeModal']))
            jQuery.nmTop().close();
        @endif

        if (jQuery("#wikiTitle").val().length >= 2) {
            jQuery("#saveBtn").removeAttr("disabled");
        } else {
            jQuery("#saveBtn").attr("disabled", "disabled");
        }

        jQuery("#wikiTitle").keypress(function() {

            if (jQuery("#wikiTitle").val().length >= 2) {
                jQuery("#saveBtn").removeAttr("disabled");
            } else {
                jQuery("#saveBtn").attr("disabled", "disabled");
            }
        })
    });
</script>
