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

<div class="showDialogOnLoad tw:hidden">

    <h4 class="widgettitle title-light tw:pb-0">
        <i class="fas {{ $canvasTypes[$canvasItem['box']]['icon'] }}"></i>
        {{ $canvasTypes[$canvasItem['box']]['title'] }}
    </h4>
    <hr class="tw:mt-1 tw:mb-4">

    <h5 class="tw:pl-10"><strong>{{ $canvasItem['description'] }}</strong></h5>

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
       leantime.authController.makeInputReadonly("#global-modal-content");

   @endif;

   @if($login::userHasRole([$roles::$commenter]))
       leantime.commentsController.enableCommenterForms();
   @endif;

})
</script>

@endsection
