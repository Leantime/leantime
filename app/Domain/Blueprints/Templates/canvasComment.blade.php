@php
    $canvasSlug = $canvasSlug ?? '';
    $canvasItem = $canvasItem ?? ['id' => '', 'box' => '', 'description' => ''];
    $canvasTypes = $canvasTypes ?? [];

    $id = '';
    if (isset($canvasItem['id']) && $canvasItem['id'] != '') {
        $id = $canvasItem['id'];
    }
@endphp

<script type="text/javascript">
    window.onload = function() {
        if (!window.jQuery) {
            //It's not a modal
            location.href="{{ BASE_URL }}/blueprints/{{ $canvasSlug }}/showCanvas?showModal={{ $canvasItem['id'] }}";
        }
    }
</script>

<div class="showDialogOnLoad" style="display:none;">

    <x-global::actions.modal-header>
    <x-slot:title><i class="fas {{ $canvasTypes[$canvasItem['box']]['icon'] ?? '' }}"></i> {{ $canvasTypes[$canvasItem['box']]['title'] ?? '' }}</x-slot:title>
</x-global::actions.modal-header>
<hr style="margin-top: 5px; margin-bottom: 15px;">

    {!! $tpl->displayNotification() !!}

    <h5 style="padding-left: 40px"><strong>{{ $canvasItem['description'] }}</strong></h5>

    @if($id !== '')
        <br />
        <input type="hidden" name="comment" value="1" />
        <x-global::elements.section-header icon="fa fa-comments">{!! __('subtitles.discussion') !!}</x-global::elements.section-header>
        @include('comments::submodules.generalComment', ['formUrl' => '/blueprints/' . $canvasSlug . '/editCanvasComment/' . $id])
    @endif
</div>

<script type="text/javascript">
    jQuery(document).ready(function(){

        if (window.leantime && window.leantime.tiptapController) {
            leantime.tiptapController.initSimpleEditor();
        }

        @if(! $login::userIsAtLeast($roles::$editor))
            leantime.authController.makeInputReadonly(".nyroModalCont");
        @endif

        @if($login::userHasRole([$roles::$commenter]))
            leantime.commentsController.enableCommenterForms();
        @endif

    })
</script>
