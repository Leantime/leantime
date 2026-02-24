@extends($layout)
@section('content')

<h4 class="widgettitle title-light">{!! __("subtitles.delete") !!}</h4>
<hr class="tw:mt-1 tw:mb-4">

<form method="post" action="{{ BASE_URL }}/goalcanvas/delCanvasItem/{{ $id }}">
    <p>{{ __('text.confirm_board_item_deletion') }}</p><br />
    <x-globals::forms.button submit type="danger" name="del">{{ __('buttons.yes_delete') }}</x-globals::forms.button>
    <x-globals::forms.button link="{{ BASE_URL }}/goalcanvas/showCanvas" type="secondary">{{ __('buttons.back') }}</x-globals::forms.button>
</form>
@endsection
