@extends($layout)
@section('content')

<x-global::layout.page-header icon="fa {{ $tpl->getModulePicture() }}" :eyebrow="__('label.administration')">
    {!! sprintf(__('headline.delete_client'), e($client['name'])) !!}
</x-global::layout.page-header>

<div class="maincontent">
    <x-global::layout.content-box>

        {!! $tpl->displayNotification() !!}

        <x-global::elements.card>

            <x-slot:title>{!! __('subtitles.delete') !!}</x-slot:title>

            <form method="post">

                @dispatchEvent('afterFormOpen')

                <p>{!! __('text.confirm_client_deletion') !!}<br /></p>
                <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.yes_delete')" name="del" />
                <x-global::forms.button tag="a" link="/clients/showClient/{{ $client['id'] }}" contentRole="tertiary">{!! __('buttons.back') !!}</x-global::forms.button>

                @dispatchEvent('beforeFormClose')

            </form>
        </x-global::elements.card>

    </x-global::layout.content-box>
</div>

@endsection
