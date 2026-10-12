<x-global::actions.modal-header>
    <x-slot:title>{!! __('subtitles.delete') !!}</x-slot:title>
</x-global::actions.modal-header>
<form method="post" action="{{ BASE_URL }}/blueprints/{{ $canvasSlug }}/delCanvas/{{ $id }}">
    <p>{!! __('text.confirm_board_deletion') !!}</p><br />
    <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.yes_delete')" name="del" />
    <x-global::forms.button tag="a" contentRole="tertiary"
       link="{{ BASE_URL }}/blueprints/{{ $canvasSlug }}/showCanvas">{!! __('buttons.back') !!}</x-global::forms.button>
</form>
