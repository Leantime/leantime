@php
    /** @var \Leantime\Core\UI\Template $tpl */
    /** @var string BASE_URL */
    /** @var \Leantime\Domain\Auth\Services\Auth $login */
    /** @var \Leantime\Domain\Auth\Models\Roles $roles */
    $currentWiki = $tpl->get('wiki');
@endphp

<h4 class="widgettitle title-light"><i class="fa fa-book"></i> {{ __('label.wiki') }} {{ $tpl->escape($currentWiki->title) }}</h4>

{!! $tpl->displayNotification() !!}

@php
    $id = '';
    if (isset($currentWiki->id)) {
        $id = $currentWiki->id;
    }
@endphp

<form class="formModal" method="post" action="{{ BASE_URL }}/wiki/wikiModal/{{ $id }}">

    <label>{{ __('label.wiki_title') }}</label>
    <x-global::forms.input name="title" id="wikiTitle" value="{{ $tpl->escape($currentWiki->title) }}" placeholder="{{ __('input.placeholders.wiki_title') }}" /><br />

    <br />

    <div class="row">
        <div class="col-md-6">
            <x-global::button submit type="primary" id="saveBtn">{{ __('buttons.save') }}</x-global::button>
        </div>
        <div class="col-md-6 align-right padding-top-sm">
            @if (isset($currentWiki->id) && $currentWiki->id != '' && $login::userIsAtLeast($roles::$editor))
                <a href="{{ BASE_URL }}/wiki/delWiki/{{ $currentWiki->id }}" class="delete formModal"><i class="fa fa-trash"></i> {{ __('links.delete_wiki') }}</a>
            @endif
        </div>
    </div>

</form>

<script>
    jQuery(document).ready(function(){

        @if (isset($_GET['closeModal']))
            jQuery.nmTop().close();
        @endif

       if(jQuery("#wikiTitle").val().length >= 2) {
           jQuery("#saveBtn").removeAttr("disabled");
       }else{
           jQuery("#saveBtn").attr("disabled", "disabled");
       }

        jQuery("#wikiTitle").keypress(function(){

            if(jQuery("#wikiTitle").val().length >= 2) {
                jQuery("#saveBtn").removeAttr("disabled");
            }else{
                jQuery("#saveBtn").attr("disabled", "disabled");
            }
        })
    });
</script>
