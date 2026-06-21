@extends($layout)

@section('content')

<h4 class="widgettitle title-light"><i class="fa fa-trash"></i> {!! __('buttons.delete') !!}</h4>

<form method="post" action="{{ BASE_URL }}/wiki/delWiki/{{ $tpl->escape($_GET['id']) }}">
    <p>{!! __('text.are_you_sure_delete_wiki') !!}</p>
    <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.yes_delete')" name="del" />
    <x-global::forms.button tag="a" contentRole="tertiary" link="{{ BASE_URL }}/wiki/show">{!! __('buttons.back') !!}</x-global::forms.button>
</form>

@endsection
