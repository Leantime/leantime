@php
    $ticket = $tpl->get('ticket');
@endphp

<h4 class="widgettitle title-light"><x-global::elements.icon name="delete" /> {{ __('buttons.delete') }}</h4>

<form method="post" action="{{ BASE_URL }}/wiki/delArticle/{{ (int) $_GET['id'] }}">
    <p>{{ __('text.are_you_sure_delete_article') }}</p><br />
    <x-globals::forms.button submit type="danger" name="del">{{ __('buttons.yes_delete') }}</x-globals::forms.button>
    <x-globals::forms.button link="{{ BASE_URL }}/wiki/show/" type="secondary">{{ __('buttons.back') }}</x-globals::forms.button>
</form>
