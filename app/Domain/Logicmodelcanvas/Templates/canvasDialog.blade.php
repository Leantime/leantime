@php
    use Leantime\Domain\Logicmodelcanvas\Repositories\Logicmodelcanvas;

    $canvasName = 'logicmodel';
    $canvasItem = $tpl->get('canvasItem');
    $canvasTypes = $tpl->get('canvasTypes');
    $hiddenStatusLabels = $tpl->get('statusLabels');
    $statusLabels = $statusLabels ?? $hiddenStatusLabels;
    $hiddenRelatesLabels = $tpl->get('relatesLabels');
    $relatesLabels = $relatesLabels ?? $hiddenRelatesLabels;
    $dataLabels = $tpl->get('dataLabels');

    $id = '';
    if (isset($canvasItem['id']) && $canvasItem['id'] != '') {
        $id = $canvasItem['id'];
    }

    // Resolve stage color for the pill
    $stages = Logicmodelcanvas::STAGES;
    $boxKey = $canvasItem['box'] ?? '';
    $stageColor = '#888';
    $stageBg = '#f0f0f0';
    foreach ($stages as $stage) {
        if ('lm_' . $stage['key'] === $boxKey) {
            $stageColor = $stage['color'];
            $stageBg = $stage['bg'];
            break;
        }
    }
@endphp

<script type="text/javascript">
    window.onload = function() {
        if (!window.jQuery) {
            location.href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas?showModal={{ $canvasItem['id'] }}";
        }
    }
</script>

<div style="max-width:900px; width:100%; padding-bottom:20px;">

    {{-- Header: stage pill --}}
    <div style="display:flex; align-items:center; gap:12px; margin-bottom:8px;">
        <span style="display:inline-flex; align-items:center; gap:5px; padding:4px 14px; border-radius:20px; font-size:var(--font-size-s); font-weight:600; color:{{ $stageColor }}; background:{{ $stageBg }};">
            <i class="fas {{ $canvasTypes[$canvasItem['box']]['icon'] }}"></i>
            {{ $canvasTypes[$canvasItem['box']]['title'] }}
        </span>
    </div>

    {!! $tpl->displayNotification() !!}

    <form class="formModal" method="post" action="{{ BASE_URL }}/{{ $canvasName }}canvas/editCanvasItem/{{ $id }}">

        <input type="hidden" value="{{ $tpl->get('currentCanvas') }}" name="canvasId" />
        <input type="hidden" value="{{ $tpl->escape($canvasItem['box']) }}" name="box" id="box"/>
        <input type="hidden" value="{{ $id }}" name="itemId" id="itemId"/>

        <x-global::forms.input :bare="true" type="text" name="description" class="main-title-input" style="width:99%;"
            value="{{ $tpl->escape($canvasItem['description']) }}"
            placeholder="{{ $tpl->__('input.placeholders.short_name') }}" /><br />

        @if (! empty($statusLabels))
            <label>{{ $tpl->__('label.status') }}</label>
            <x-global::forms.select :bare="true" name="status" style="width:220px" id="statusCanvas">
            </x-global::forms.select><br /><br />
        @else
            <input type="hidden" name="status" value="{{ $canvasItem['status'] ?? array_key_first($hiddenStatusLabels) }}" />
        @endif

        @if (! empty($relatesLabels))
            <label>{{ $tpl->__('label.relates') }}</label>
            <x-global::forms.select :bare="true" name="relates" style="width: 50%" id="relatesCanvas">
            </x-global::forms.select><br />
        @else
            <input type="hidden" name="relates" value="{{ $canvasItem['relates'] ?? array_key_first($hiddenRelatesLabels) }}" />
        @endif

        @if ($dataLabels[1]['active'])
            <label>{{ $tpl->__($dataLabels[1]['title']) }}</label>
            <textarea style="width:100%" rows="5" cols="10" name="{{ $dataLabels[1]['field'] }}" class="modalTextArea tiptapSimple">{{ $canvasItem[$dataLabels[1]['field']] }}</textarea><br />
        @else
            <input type="hidden" name="{{ $dataLabels[1]['field'] }}" value="" />
        @endif

        <input type="hidden" name="{{ $dataLabels[2]['field'] }}" value="" />
        <input type="hidden" name="{{ $dataLabels[3]['field'] }}" value="" />

        <input type="hidden" name="milestoneId" value="{{ $canvasItem['milestoneId'] }}" />
        <input type="hidden" name="changeItem" value="1" />

        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            @if ($login::userIsAtLeast($roles::$editor))
                <x-global::button submit type="primary" id="primaryCanvasSubmitButton">{{ $tpl->__('buttons.save') }}</x-global::button>
                <x-global::button tag="button" type="secondary" id="saveAndClose" onclick="leantime.{{ $canvasName }}CanvasController.setCloseModal();">{{ $tpl->__('buttons.save_and_close') }}</x-global::button>
            @endif

            @if ($id != '')
                <a href="{{ BASE_URL }}/{{ $canvasName }}canvas/delCanvasItem/{{ $id }}" class="{{ $canvasName }}CanvasModal delete" style="margin-left:auto;"><i class="fa fa-trash-can"></i> {{ $tpl->__('links.delete') }}</a>
            @endif
        </div>

    </form>

    @if ($id !== '')
        <br />
        <input type="hidden" name="comment" value="1" />
        <h4 class="widgettitle title-light"><span class="fa fa-comments"></span>{{ $tpl->__('subtitles.discussion') }}</h4>
        @php
            $tpl->assign('formUrl', "/$canvasName" . "canvas/editCanvasItem/" . $id);
            $tpl->displaySubmodule('comments-generalComment');
        @endphp
    @endif
</div>

<script type="text/javascript">
    jQuery(document).ready(function(){

        @if (! empty($statusLabels))
            new SlimSelect({
                select: '#statusCanvas',
                showSearch: false,
                valuesUseText: false,
                data: [
                    @foreach ($statusLabels as $key => $data)
                        @if ($data['active'])
                            { innerHTML: '<i class="fas fa-fw {{ $data['icon'] }}"></i>&nbsp;{{ $data['title'] }}',
                              text: "{{ $data['title'] }}", value: "{{ $key }}", selected: {{ $canvasItem['status'] == $key ? 'true' : 'false' }} },
                        @endif
                    @endforeach
                ]
            });
        @endif

        @if (! empty($relatesLabels))
            new SlimSelect({
                select: '#relatesCanvas',
                showSearch: false,
                valuesUseText: false,
                data: [
                    @foreach ($relatesLabels as $key => $data)
                        @if ($data['active'])
                            { innerHTML: '<i class="fas fa-fw {{ $data['icon'] }}"></i>&nbsp;{{ $data['title'] }}',
                              text: "{{ $data['title'] }}", value: "{{ $key }}", selected: {{ $canvasItem['relates'] == $key ? 'true' : 'false' }} },
                        @endif
                    @endforeach
                ]
            });
        @endif

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
