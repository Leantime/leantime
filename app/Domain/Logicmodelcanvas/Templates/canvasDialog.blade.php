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
    $currentStageTitle = '';
    foreach ($stages as $stage) {
        if ('lm_' . $stage['key'] === $boxKey) {
            $stageColor = $stage['color'];
            $stageBg = $stage['bg'];
            $currentStageTitle = $stage['title'];
            break;
        }
    }

    $currentImpact = $canvasItem['impact'] ?? '';
@endphp

<script type="text/javascript">
    window.onload = function() {
        if (!window.jQuery) {
            location.href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas?showModal={{ $canvasItem['id'] }}";
        }
    }
</script>

<div style="width:1000px; padding-bottom:20px;">

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
        <input type="hidden" value="{{ $id }}" name="itemId" id="itemId"/>
        <input type="hidden" name="milestoneId" value="{{ $canvasItem['milestoneId'] }}" />
        <input type="hidden" name="changeItem" value="1" />
        <input type="hidden" name="{{ $dataLabels[3]['field'] }}" value="" />

        <div class="row">
            {{-- ═══ Left Column: Content ═══ --}}
            <div class="col-md-8">

                {{-- Title --}}
                <x-global::forms.input :bare="true" type="text" name="description" class="main-title-input" style="width:99%;"
                    value="{{ $tpl->escape($canvasItem['description']) }}"
                    placeholder="{{ $tpl->__('input.placeholders.short_name') }}" />

                @if ($dataLabels[1]['active'])
                    <label>{{ $tpl->__($dataLabels[1]['title']) }}</label>
                    <textarea style="width:100%" rows="5" cols="10" name="{{ $dataLabels[1]['field'] }}" class="modalTextArea tiptapSimple">{{ $canvasItem[$dataLabels[1]['field']] }}</textarea><br />
                @else
                    <input type="hidden" name="{{ $dataLabels[1]['field'] }}" value="" />
                @endif

                @if ($dataLabels[2]['active'])
                    <label>{{ $tpl->__($dataLabels[2]['title']) }}</label>
                    <textarea style="width:100%" rows="3" cols="10" name="{{ $dataLabels[2]['field'] }}" class="modalTextArea tiptapSimple">{{ $canvasItem[$dataLabels[2]['field']] }}</textarea><br />
                @else
                    <input type="hidden" name="{{ $dataLabels[2]['field'] }}" value="" />
                @endif

                {{-- Comments section moved outside the form to avoid nested forms --}}

            </div>

            {{-- ═══ Right Column: Details Panel ═══ --}}
            <div class="col-md-4">
                <div class="lm-details-panel">

                    <div class="lm-details-heading">{{ $tpl->__('label.details') }}</div>

                    {{-- Status --}}
                    @if (! empty($statusLabels))
                        <div class="lm-details-row">
                            <span class="lm-details-label"><i class="fas fa-fw fa-circle-dot"></i> {{ $tpl->__('label.status') }}</span>
                            <span class="lm-details-value">
                                <x-global::forms.select :bare="true" name="status" id="statusCanvas">
                                </x-global::forms.select>
                            </span>
                        </div>
                    @else
                        <input type="hidden" name="status" value="{{ $canvasItem['status'] ?? array_key_first($hiddenStatusLabels) }}" />
                    @endif

                    {{-- Priority --}}
                    <div class="lm-details-row">
                        <span class="lm-details-label"><i class="fas fa-fw fa-flag"></i> {{ $tpl->__('logicmodel.priority.label') }}</span>
                        <span class="lm-details-value">
                            <x-global::forms.select :bare="true" name="impact" id="priorityCanvas">
                            </x-global::forms.select>
                        </span>
                    </div>

                    {{-- Stage --}}
                    <div class="lm-details-row">
                        <span class="lm-details-label"><i class="fas fa-fw fa-layer-group"></i> {{ $tpl->__('logicmodel.stage.label') }}</span>
                        <span class="lm-details-value">
                            <x-global::forms.select :bare="true" name="box" id="stageCanvas">
                            </x-global::forms.select>
                        </span>
                    </div>

                    @if (! empty($relatesLabels))
                        <div class="lm-details-row">
                            <span class="lm-details-label"><i class="fas fa-fw fa-link"></i> {{ $tpl->__('label.relates') }}</span>
                            <span class="lm-details-value">
                                <x-global::forms.select :bare="true" name="relates" id="relatesCanvas">
                                </x-global::forms.select>
                            </span>
                        </div>
                    @else
                        <input type="hidden" name="relates" value="{{ $canvasItem['relates'] ?? array_key_first($hiddenRelatesLabels) }}" />
                    @endif

                    {{-- Author (read-only) --}}
                    @if ($id !== '' && isset($canvasItem['author']))
                        <div class="lm-details-row">
                            <span class="lm-details-label"><i class="fas fa-fw fa-user"></i> {{ $tpl->__('label.author') }}</span>
                            <span class="lm-details-value lm-details-text">{{ $canvasItem['authorFirstname'] ?? '' }} {{ $canvasItem['authorLastname'] ?? '' }}</span>
                        </div>
                    @endif

                </div>

                {{-- Plugin hook for additional right-panel content (e.g. project links) --}}
                @if ($id !== '')
                    @dispatchEvent('canvas.dialog.afterDetails', [
                        'canvasItem' => $canvasItem,
                        'canvasName' => $canvasName,
                        'canvasId' => (int) session('currentLOGICMODELCanvas'),
                    ])
                @endif

            </div>
        </div>

        {{-- ═══ Bottom: Actions ═══ --}}
        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-top:16px; padding-top:16px; border-top:1px solid var(--main-border-color);">
            @if ($login::userIsAtLeast($roles::$editor))
                <x-global::button submit type="primary" id="primaryCanvasSubmitButton">{{ $tpl->__('buttons.save') }}</x-global::button>
                <x-global::button submit type="secondary" id="saveAndClose" name="save" value="closeModal">{{ $tpl->__('buttons.save_and_close') }}</x-global::button>
            @endif

            @if ($id != '')
                <a href="{{ BASE_URL }}/{{ $canvasName }}canvas/delCanvasItem/{{ $id }}" class="{{ $canvasName }}CanvasModal delete" style="margin-left:auto;"><i class="fa fa-trash-can"></i> {{ $tpl->__('links.delete') }}</a>
            @endif
        </div>

    </form>

    {{-- Comments section rendered OUTSIDE the main form to avoid nested forms.
         The comments submodule has its own <form> which would break the outer form. --}}
    @if ($id !== '')
        <div style="margin-top:16px; padding-top:16px; border-top:1px solid var(--main-border-color);">
            <h4 class="widgettitle title-light"><span class="fa fa-comments"></span>{{ $tpl->__('subtitles.discussion') }}</h4>
            @php
                $tpl->assign('formUrl', "/$canvasName" . "canvas/editCanvasItem/" . $id);
                $tpl->displaySubmodule('comments-generalComment');
            @endphp
        </div>
    @endif

</div>

<style>
    .lm-details-panel {
        border-left: 1px solid var(--main-border-color);
        padding-left: 20px;
        margin-left: 5px;
    }
    .lm-details-heading {
        font-size: var(--font-size-s);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--primary-font-color);
        padding-bottom: 10px;
        margin-bottom: 6px;
        border-bottom: 1px solid var(--main-border-color);
    }
    .lm-details-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 0;
        min-height: 40px;
    }
    .lm-details-label {
        font-size: var(--font-size-s);
        font-weight: 500;
        color: var(--primary-font-color);
        white-space: nowrap;
    }
    .lm-details-label i {
        color: var(--secondary-font-color);
        margin-right: 4px;
    }
    .lm-details-value {
        text-align: right;
    }
    .lm-details-value .ss-main {
        min-width: 120px;
        border: none !important;
        background: transparent !important;
        box-shadow: none !important;
    }
    .lm-details-value .ss-main .ss-single-selected {
        border: none !important;
        background: transparent !important;
        padding-right: 0;
        justify-content: flex-end;
    }
    .lm-details-text {
        font-size: var(--font-size-s);
        color: var(--primary-font-color);
    }
</style>

<script type="text/javascript">
    jQuery(document).ready(function(){

        @if (! empty($statusLabels))
            @php $statusColorMap = ['blue' => '#1B75BB', 'orange' => '#fdab3d', 'green' => '#75BB1B', 'red' => '#BB1B25', 'grey' => '#c3ccd4']; @endphp
            new SlimSelect({
                select: '#statusCanvas',
                showSearch: false,
                valuesUseText: false,
                data: [
                    @foreach ($statusLabels as $key => $data)
                        @if ($data['active'])
                            @php $sColor = $statusColorMap[$data['color']] ?? '#666'; @endphp
                            { innerHTML: '<i class="fas fa-fw {{ $data['icon'] }}" style="color:{{ $sColor }}"></i>&nbsp;{{ $data['title'] }}',
                              text: "{{ $data['title'] }}", value: "{{ $key }}", selected: {{ $canvasItem['status'] == $key ? 'true' : 'false' }} },
                        @endif
                    @endforeach
                ]
            });
        @endif

        // Priority dropdown (matches to-do priority structure)
        new SlimSelect({
            select: '#priorityCanvas',
            showSearch: false,
            valuesUseText: false,
            data: [
                { text: "{{ $tpl->__('logicmodel.priority.none') }}", value: "", selected: {{ $currentImpact === '' ? 'true' : 'false' }} },
                { innerHTML: '<i class="fas fa-fw fa-thermometer-full" style="color:#C73E5C"></i>&nbsp;{{ $tpl->__('label.critical') }}',
                  text: "{{ $tpl->__('label.critical') }}", value: "1", selected: {{ $currentImpact === '1' ? 'true' : 'false' }} },
                { innerHTML: '<i class="fas fa-fw fa-thermometer-three-quarters" style="color:#E85A5A"></i>&nbsp;{{ $tpl->__('label.high') }}',
                  text: "{{ $tpl->__('label.high') }}", value: "2", selected: {{ $currentImpact === '2' ? 'true' : 'false' }} },
                { innerHTML: '<i class="fas fa-fw fa-thermometer-half" style="color:#F5A623"></i>&nbsp;{{ $tpl->__('label.medium') }}',
                  text: "{{ $tpl->__('label.medium') }}", value: "3", selected: {{ $currentImpact === '3' ? 'true' : 'false' }} },
                { innerHTML: '<i class="fas fa-fw fa-thermometer-quarter" style="color:#2ECC71"></i>&nbsp;{{ $tpl->__('label.low') }}',
                  text: "{{ $tpl->__('label.low') }}", value: "4", selected: {{ $currentImpact === '4' ? 'true' : 'false' }} },
            ]
        });

        // Stage dropdown
        new SlimSelect({
            select: '#stageCanvas',
            showSearch: false,
            valuesUseText: false,
            data: [
                @foreach ($stages as $num => $stage)
                    @php $stageBoxKey = 'lm_' . $stage['key']; @endphp
                    { innerHTML: '<i class="fas fa-fw {{ $stage['icon'] }}" style="color:{{ $stage['color'] }}"></i>&nbsp;{{ $tpl->__($stage['title']) }}',
                      text: "{{ $tpl->__($stage['title']) }}", value: "{{ $stageBoxKey }}", selected: {{ $boxKey === $stageBoxKey ? 'true' : 'false' }} },
                @endforeach
            ]
        });

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
