<x-global::content.modal.modal-buttons />


<h4 class="widgettitle title-light">
    <i class="fa-solid fa-mountain"></i>
    {{ empty($bigRock->title) ? __('label.create_new_goalboard') : __('label.goalboard') }} {{ $bigRock->title }}
</h4>

<x-global::content.modal.form
    action="{{ BASE_URL }}/goalcanvas/bigRock/{{ !empty($bigRock->id) ? $bigRock->id : '' }}">

    <br />
    <x-global::forms.text-input placeholder="{{ __('label.goal_description') }}" type="text" name="title"
        id="wikiTitle" value="{{ $bigRock->title }}" variant='title' /><br />

    <br />
    <div class="row">
        <div class="col-md-6">
            <x-global::forms.button scale="sm" type="submit" id="saveBtn">
                {{ __('buttons.save') }}
            </x-global::forms.button>
        </div>
        <div class="col-md-6 align-right padding-top-sm">

        </div>
    </div>

</x-global::content.modal.form>

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
