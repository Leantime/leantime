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

    <h4 class="widgettitle title-light" style="padding-bottom: 0"><i class="fas {{ $canvasTypes[$canvasItem['box']]['icon'] }}"></i> {{ $canvasTypes[$canvasItem['box']]['title'] }}</h4>
    <hr style="margin-top: 5px; margin-bottom: 15px;">

    {!! $tpl->displayNotification() !!}

    <h5 style="padding-left: 40px"><strong>{{ $tpl->escape($canvasItem['description']) }}</strong></h5>

    @if ($id !== '')
        <br />
        <input type="hidden" name="comment" value="1" />
        <h4 class="widgettitle title-light"><x-global::elements.icon name="forum" />{{ $tpl->__('subtitles.discussion') }}</h4>
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
