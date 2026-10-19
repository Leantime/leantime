@extends($layout)

@section('content')

<x-global::layout.page-header icon="fa fa-trash" :eyebrow="session('currentProjectClient') . ' // ' . session('currentProjectName')">
    {!! __('headline.delete_board') !!}
</x-global::layout.page-header>

<div class="maincontent">
    <x-global::layout.content-box>
        <x-global::elements.card>
            <x-slot:title>{!! __('subtitles.delete') !!}</x-slot:title>
            <form method="post" action="{{ BASE_URL }}/ideas/delCanvas/{{ $tpl->escape($_GET['id']) }}">
                <p>{!! __('text.are_you_sure_delete_idea_board') !!}</p>
                <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.yes_delete')" name="del" />
                <x-global::forms.button tag="a" contentRole="tertiary" link="{{ BASE_URL }}/ideas/showBoards">{!! __('buttons.back') !!}</x-global::forms.button>
            </form>
        </x-global::elements.card>
    </x-global::layout.content-box>
</div>

@endsection
