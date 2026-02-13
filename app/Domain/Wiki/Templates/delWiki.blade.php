<h4 class="widgettitle title-light"><i class="fa fa-trash"></i> {{ __('buttons.delete') }}</h4>

<form method="post" action="{{ BASE_URL }}/wiki/delWiki/{{ $tpl->escape($_GET['id']) }}">
    <p>{{ __('text.are_you_sure_delete_wiki') }}</p>
    <input type="submit" value="{{ __('buttons.yes_delete') }}" name="del" class="button" />
    <a class="btn btn-secondary" href="{{ BASE_URL }}/wiki/show">{{ __('buttons.back') }}</a>
</form>
