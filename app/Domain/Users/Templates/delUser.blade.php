@extends($layout)
@section('content')

<x-global::layout.page-header icon="fa {{ $tpl->getModulePicture() }}" :eyebrow="__('label.administration')">
    {!! __('headlines.delete_user') !!}
</x-global::layout.page-header>

<div class="maincontent">
    <x-global::layout.content-box>

        {!! $tpl->displayNotification() !!}

        <x-global::elements.card>

            <x-slot:title>{!! __('subtitles.delete') !!}</x-slot:title>

            <form method="post">
                <input type="hidden" name="{{ session('formTokenName') }}" value="{{ session('formTokenValue') }}" />
                <p>{!! __('text.confirm_user_deletion') !!}</p><br />
                <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.yes_delete')" name="del" />
                <x-global::forms.button tag="a" contentRole="tertiary" link="{{ BASE_URL }}/users/showAll">{!! __('buttons.back') !!}</x-global::forms.button>
            </form>


        </x-global::elements.card>
    </x-global::layout.content-box>
</div>

@endsection
