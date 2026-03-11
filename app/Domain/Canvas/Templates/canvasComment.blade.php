@php
    $canvasItem = $tpl->get('canvasItem');
    $canvasTypes = $tpl->get('canvasTypes');

    $id = '';
    if (isset($canvasItem['id']) && $canvasItem['id'] != '') {
        $id = $canvasItem['id'];
    }
@endphp

<script type="text/javascript">
    window.onload = function() {
        if (!window.jQuery) {
            location.href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas?showModal={{ $canvasItem['id'] }}";
        }
    }
</script>

<div class="showDialogOnLoad" style="display:none;">

    <x-globals::elements.section-title class="tw:pb-0">@if(!empty($canvasTypes[$canvasItem['box']]['icon']))<x-globals::elements.icon :name="$canvasTypes[$canvasItem['box']]['icon']" />@endif {{ $canvasTypes[$canvasItem['box']]['title'] }}</x-globals::elements.section-title>
    <hr class="tw:mt-1 tw:mb-4">

    {!! $tpl->displayNotification() !!}

    <h5 class="tw:pl-10"><strong>{{ $tpl->escape($canvasItem['description']) }}</strong></h5>

    @if ($id !== '')
        <br />
        <input type="hidden" name="comment" value="1" />
        <x-globals::elements.section-title icon="forum">{{ $tpl->__('subtitles.discussion') }}</x-globals::elements.section-title>
        @php
            $tpl->assign('formUrl', "/$canvasName" . "canvas/editCanvasComment/" . $id);
            $tpl->displaySubmodule('comments-generalComment');
        @endphp
    @endif
</div>

<script type="text/javascript">
    jQuery(document).ready(function(){

        if (window.leantime && window.leantime.tiptapController) {
            leantime.tiptapController.initSimpleEditor();
        }

        @if (! $login::userIsAtLeast($roles::$editor))
            leantime.authController.makeInputReadonly("#global-modal-content");
        @endif

        @if ($login::userHasRole([$roles::$commenter]))
            leantime.commentsController.enableCommenterForms();
        @endif

    })
</script>
