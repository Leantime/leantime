@extends($layout)
@section('content')

@php

/**
* canvasComment.inc template - Generic template for comments
*
*/



@endphp

<script type="text/javascript">
    window.onload = function() {
        if (!window.jQuery) {
            //It's not a modal
            location.href = "{{ BASE_URL }}/goalcanvas/showCanvas?showModal={{ $canvasItem['id'] }}";
        }
    }
</script>

<div class="showDialogOnLoad" style="display:none;">

    <h4 class="widgettitle title-light" style="padding-bottom: 0">
        <i class="fas {{ $canvasTypes[$canvasItem['box']]['icon'] }}"></i>
        {{ $canvasTypes[$canvasItem['box']]['title'] }}
    </h4>
    <hr style="margin-top: 5px; margin-bottom: 15px;">

    <h5 style="padding-left: 40px"><strong>{{ $canvasItem['description'] }}</strong></h5>

    @if ($id !== '')
    <br />
    <input type="hidden" name="comment" value="1" />
    <h4 class="widgettitle title-light">
        <span class="fa fa-comments"></span>{{ __('subtitles.discussion') }}
    </h4>
    @php
    $tpl->assign("formUrl", "/goalcanvas/editCanvasComment/" . $id . "");
    $tpl->displaySubmodule('comments-generalComment');
    @endphp
    @endif

</div>


<script type="text/javascript">
jQuery(document).ready(function() {

   leantime.editorController.initSimpleEditor();

   @if(!$login::userIsAtLeast($roles::$editor))
       leantime.authController.makeInputReadonly(".nyroModalCont");

   @endif;

   @if($login::userHasRole([$roles::$commenter]))
       leantime.commentsController.enableCommenterForms();
   @endif;

})
</script>

@endsection
