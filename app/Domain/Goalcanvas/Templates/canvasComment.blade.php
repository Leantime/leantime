@extends($layout)
@section('content')

@php

/**
* canvasComment.inc template - Generic template for comments
*
*/



@endphp

<div class="showDialogOnLoad tw:w-[800px]" style="display:none;">

    <x-globals::elements.section-title icon="{{ $canvasTypes[$canvasItem['box']]['icon'] }}" class="tw:pb-0">{{ $canvasTypes[$canvasItem['box']]['title'] }}</x-globals::elements.section-title>
    <hr class="tw:mt-1 tw:mb-4">

    <h5 class="tw:pl-10"><strong>{{ $canvasItem['description'] }}</strong></h5>

    @if ($id !== '')
    <br />
    <input type="hidden" name="comment" value="1" />
    <x-globals::elements.section-title icon="forum">{{ __('subtitles.discussion') }}</x-globals::elements.section-title>
    @php
    $tpl->assign("formUrl", "/goalcanvas/editCanvasComment/" . $id . "");
    $tpl->displaySubmodule('comments-generalComment');
    @endphp
    @endif

</div>


<script type="text/javascript">
jQuery(document).ready(function() {

   if (window.leantime && window.leantime.tiptapController) {
       leantime.tiptapController.initSimpleEditor();
   }

   @if(!$login::userIsAtLeast($roles::$editor))
       leantime.authController.makeInputReadonly("#global-modal-content");

   @endif;

   @if($login::userHasRole([$roles::$commenter]))
       leantime.commentsController.enableCommenterForms();
   @endif;

})
</script>

@endsection
