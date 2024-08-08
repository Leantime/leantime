@extends($layout) 
@section('content')


@php
/**
 * delCanvas.inc template - Generic template for deleting canvas
 *
 * Required variables:
 * - $canvasName     Name of current canvas
 * - $csrf_token     CSRF token (if used in your application)
 */

$id = filter_var($_GET['id'] ?? '', FILTER_SANITIZE_NUMBER_INT);
$canvasName = 'goal'
@endphp

<h4 class="widgettitle title-light">{{ __("subtitles.delete") }}</h4>

<form method="post" action="{{ BASE_URL."/$canvasName/canvas/delCanvas/$id" }}">
    @if(isset($csrf_token))
        <input type="hidden" name="csrf_token" value="{{ $csrf_token }}">
    @endif
    <p>{{ __('text.confirm_board_deletion') }}</p><br />
    <input type="submit" value="{{ __('buttons.yes_delete') }}" name="del" class="button" />
    <a class="btn btn-secondary" href="{{ BASE_URL."/$canvasName/canvas/showCanvas" }}">{{ __('buttons.back') }}</a>
</form>

@endsection