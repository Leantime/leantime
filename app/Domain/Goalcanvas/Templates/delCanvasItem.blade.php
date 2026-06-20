@extends($layout)
@section('content')

<h4 class="widgettitle title-light">{!! __("subtitles.delete") !!}</h4>
<hr style="margin-top: 5px; margin-bottom: 15px;">

<form method="post" action="{{ BASE_URL }}/goalcanvas/delCanvasItem/{{ $id }}">
    <p>{{ __('text.confirm_board_item_deletion') }}</p><br />
    <input type="submit" value="{{ __('buttons.yes_delete') }}" name="del" class="button" />
    <x-global::forms.button tag="a" contentRole="tertiary" link="{{ BASE_URL }}/goalcanvas/showCanvas">{{ __('buttons.back') }}</x-global::forms.button>
</form>
@endsection
