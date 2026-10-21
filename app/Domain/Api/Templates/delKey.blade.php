@extends($layout)

@section('content')

<x-global::layout.page-header icon="fa-solid fa-key" :eyebrow="__('label.administration')">
    {!! __('headlines.delete_key') !!}
</x-global::layout.page-header>

<div class="maincontent">
    <x-global::layout.content-box>

        {!! $tpl->displayNotification() !!}

        <x-global::elements.section-header as="h5">{!! __('subtitles.delete_key') !!}</x-global::elements.section-header>

            <form method="post">
                <input type="hidden" name="{{ session('formTokenName') }}" value="{{ session('formTokenValue') }}" />
                <p>{!! __('text.confirm_key_deletion') !!}</p><br />
                <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.yes_delete')" name="del" />
                <x-global::forms.button tag="a" link="{{ BASE_URL }}/setting/editCompanySettings/#apiKeys" contentRole="tertiary">{!! __('buttons.back') !!}</x-global::forms.button>
            </form>

    </x-global::layout.content-box>
</div>

@endsection
