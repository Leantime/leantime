@extends($layout)

@section('content')

<x-global::layout.page-header icon="fa {{ $tpl->getModulePicture() }}" :eyebrow="__('label.administration')">
    {!! sprintf(__('headlines.delete_project_x'), e($project['name'])) !!}
</x-global::layout.page-header>

<div class="maincontent">
    <x-global::layout.content-box>

        {!! $tpl->displayNotification() !!}

        <x-global::elements.card>

            <x-slot:title>{!! __('subtitles.delete') !!}</x-slot:title>

            <form method="post">
                <p>{!! __('text.confirm_project_deletion') !!}</p><br />
                <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.yes_delete')" name="del" />
                <x-global::forms.button tag="a" link="{{ BASE_URL }}/projects/showProject/{{ $project['id'] }}" contentRole="tertiary">{!! __('buttons.back') !!}</x-global::forms.button>
            </form>

        </x-global::elements.card>


    </x-global::layout.content-box>
</div>

@endsection
