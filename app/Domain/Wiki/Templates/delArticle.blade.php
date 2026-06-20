@extends($layout)

@section('content')

@php
    $ticket = $ticket ?? null;
@endphp

<h4 class="widgettitle title-light"><i class="fa fa-trash"></i> {!! __('buttons.delete') !!}</h4>

<form method="post" action="{{ BASE_URL }}/wiki/delArticle/{{ (int) $_GET['id'] }}">
    <p>{!! __('text.are_you_sure_delete_article') !!}</p><br />
    <input type="submit" value="{{ __('buttons.yes_delete') }}" name="del" class="button" />
    <x-global::forms.button tag="a" contentRole="tertiary" link="{{ BASE_URL }}/wiki/show/">{!! __('buttons.back') !!}</x-global::forms.button>
</form>

@endsection
