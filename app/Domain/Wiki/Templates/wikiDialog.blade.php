@extends($layout)

@section('content')

@php
    $currentWiki = $wiki ?? null;
@endphp

<x-global::actions.modal-header>
    <x-slot:title><i class="fa fa-book"></i> {!! __('label.wiki') !!} {{ $tpl->escape($currentWiki->title) }}</x-slot:title>
    @if (isset($currentWiki->id) && $currentWiki->id != '' && $login::userIsAtLeast($roles::$editor))
        <x-slot:actions>
            <li><a href="#/wiki/delWiki/{{ $currentWiki->id }}" class="delete"><i class="fa fa-trash-can"></i> {!! __('links.delete_wiki') !!}</a></li>
        </x-slot:actions>
    @endif
</x-global::actions.modal-header>{!! $tpl->displayNotification() !!}

@php
    $id = '';
    if (isset($currentWiki->id)) {
        $id = $currentWiki->id;
    }
@endphp

<form class="formModal" method="post" action="{{ BASE_URL }}/wiki/wikiModal/{{ $id }}">

    <x-global::forms.label>{!! __('label.wiki_title') !!}</x-global::forms.label>
    <x-global::forms.text-input name="title" id="wikiTitle" value="{{ $tpl->escape($currentWiki->title) }}" placeholder="{{ __('input.placeholders.wiki_title') }}" /><br />

    <br />

    <div class="row">
        <div class="col-md-6">
            <x-global::forms.button tag="input" inputType="submit" contentRole="primary" :labelText="__('buttons.save')" id="saveBtn" />
        </div>
        <div class="col-md-6 align-right padding-top-sm">
        </div>
    </div>

</form>

@once
@push('scripts')
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
@endpush
@endonce

@endsection
