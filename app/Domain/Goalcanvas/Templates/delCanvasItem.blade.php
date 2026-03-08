@extends($layout)
@section('content')

<x-globals::elements.section-title icon="delete">{{ __('label.delete') }}</x-globals::elements.section-title>
<hr class="tw:mt-1 tw:mb-4">

<form method="post" action="{{ BASE_URL }}/goalcanvas/delCanvasItem/{{ $id }}">
    <input type="hidden" name="del" value="1" />
    <p>{{ __('text.confirm_board_item_deletion') }}</p><br />
    <x-globals::forms.button :submit="true" state="danger">{{ __('buttons.yes_delete') }}</x-globals::forms.button>
    <x-globals::forms.button link="{{ BASE_URL }}/goalcanvas/showCanvas" contentRole="secondary">{{ __('buttons.back') }}</x-globals::forms.button>
</form>
@endsection
