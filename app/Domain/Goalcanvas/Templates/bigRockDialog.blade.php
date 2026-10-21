<x-global::actions.modal-header>
    <x-slot:title><i class="fa-solid fa-mountain"></i> {{ empty($bigRock['title']) ? __('label.create_new_goalboard') : __('label.goalboard') }} {{ $bigRock['title'] }}</x-slot:title>
</x-global::actions.modal-header>
<form class="formModal" method="post"
    action="{{ BASE_URL }}/goalcanvas/bigRock/{{ !empty($bigRock['id']) ? $bigRock['id'] : '' }}">

    <br />
    <x-global::forms.label>{{ __('label.goal_description') }}</x-global::forms.label>
    <x-global::forms.text-input width="full" name="title" id="wikiTitle" value="{{ $bigRock['title'] }}" /><br />

    <br />
    <div class="row">
        <div class="col-md-6">
            <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" id="saveBtn" />
        </div>
        <div class="col-md-6 align-right padding-top-sm">

        </div>
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
