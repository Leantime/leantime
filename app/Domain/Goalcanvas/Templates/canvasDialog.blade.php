@extends($layout)
@section('content')

@php

$canvasName = 'goal';

$canvasItem = $tpl->get('canvasItem');
$canvasTypes = $tpl->get('canvasTypes');
$hiddenStatusLabels = $tpl->get('statusLabels');
$statusLabels = $statusLabels ?? $hiddenStatusLabels;
$hiddenRelatesLabels = $tpl->get('relatesLabels');
$relatesLabels = $relatesLabels ?? $hiddenRelatesLabels;
$dataLabels = $tpl->get('dataLabels');

$id = "";
if (isset($canvasItem['id']) && $canvasItem['id'] != '') {
$id = $canvasItem['id'];
}

$currentCanvas = $tpl->get('currentCanvas');

if (isset($_GET['canvasId'])) {
$currentCanvas = (int)$_GET['canvasId'];
}

@endphp

<script type="text/javascript">
    window.onload = function() {
        if (!window.jQuery) {
            //It's not a modal
            location.href = "<?= BASE_URL ?>/<?= $canvasName ?>canvas/showCanvas?showModal=<?php echo $canvasItem['id']; ?>";
        }
    }
</script>

<div style="width:1000px">

    <h1><i class="fas <?= $canvasTypes[$canvasItem['box']]['icon']; ?>"></i> <?= $canvasTypes[$canvasItem['box']]['title']; ?></h1>

    {{ $tpl->displayNotification() }}

    <form class="formModal" method="post" action="{{ BASE_URL."/$canvasName/canvas/editCanvasItem/$id" }}">

        <input type="hidden" value="{{ $currentCanvas }}" name="canvasId">
        <input type="hidden" value="{{ $canvasItem['box'] }}" name="box" id="box">
        <input type="hidden" value="{{ $id }}" name="itemId" id="itemId">
        <input type="hidden" name="milestoneId" value="{{ $canvasItem['milestoneId'] ?? '' }}">
        <input type="hidden" name="changeItem" value="1">

        <div class="col-md-8">
            <label>{{ __("label.what_is_your_goal") }}</label>
            <input type="text" name="title" value="{{ $canvasItem['title'] }}" style="width:100%"><br>

            @if (!empty($relatesLabels))
            <label>{{ __("label.relates") }}</label>
            <select name="relates" style="width: 50%" id="relatesCanvas">
            </select><br>
            @else
            <input type="hidden" name="relates" value="{{ $canvasItem['relates'] ?? array_key_first($hiddenRelatesLabels) }}">
            @endif
            <br>
            <h4 class="widgettitle title-light" style="margin-bottom:0px;"><i class="fa-solid fa-ranking-star"></i> {{ __("Metrics") }}</h4>

            @php

            $tpl->dispatchTplEvent('beforeMeasureGoalContainer', $canvasItem); @endphp
            <div id="measureGoalContainer">
                <label>How will you measure this objective. What metric will you be using.</label>
                <input type="text" name="description" value="{{ $canvasItem['description'] }}" style="width:100%"><br>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <label>Starting Value</label>
                    <input type="number" step="0.01" name="startValue" value="{{ $canvasItem['startValue'] }}" style="width:105px">
                </div>
                <div class="col-md-3">
                    <label>Current Value</label>
                    <input type="number" step="0.01" name="currentValue" id="currentValueField" value="{{ $canvasItem['currentValue'] }}" @if ($canvasItem['setting']=='linkAndReport' ) readonly data-tippy-content="Current value calculated from child goals" @endif style="width:105px">
                </div>
                <div class="col-md-3">
                    <label>Goal Value</label>
                    <input type="number" step="0.01" name="endValue" value="{{ $canvasItem['endValue'] }}" style="width:105px">
                </div>
                <div class="col-md-3">
                    <label>Type</label>
                    <select name="metricType">
                        <option value="number" @if ($canvasItem['metricType']=='number' ) selected @endif>Number</option>
                        <option value="percent" @if ($canvasItem['metricType']=='percent' ) selected @endif>% Percent</option>
                        <option value="currency" @if ($canvasItem['metricType']=='currency' ) selected @endif>{{ __('language.currency') }}</option>
                    </select>
                </div>
            </div>

            <br>
            @if ($login::userIsAtLeast($roles::$editor))
            <input type="submit" value="{{ __("buttons.save") }}" id="primaryCanvasSubmitButton">
            <button type="submit" class="btn btn-primary" id="saveAndClose" value="closeModal" onclick="leantime.goalCanvasController.setCloseModal();">{{ __("buttons.save_and_close") }}</button>
            @endif

            @if ($id !== '')
            <br><br><br>
            <input type="hidden" name="comment" value="1">
            <h4 class="widgettitle title-light"><span class="fa fa-comments"></span>{{ __('subtitles.discussion') }}</h4>
            @php
            $formUrl = "/strategyPro/editCanvasItem/" . $id;
            @endphp
            @include('comments.generalComment', ['formUrl' => $formUrl])
            @endif
        </div>

        @if ($id != '')
        <a href="{{ url("/$canvasName/canvas/delCanvasItem/$id") }}" class="formModal delete right">
            <i class='fa fa-trash-can'></i> {{ __("links.delete") }}
        </a>
        @endif

    </form>


</div>

<script type="text/javascript">
    jQuery(document).ready(function() {

        leantime.dateController.initDateRangePicker(".startDate", ".endDate");

        <?php if (!empty($statusLabels)) { ?>
            new SlimSelect({
                select: '#statusCanvas',
                showSearch: false,
                valuesUseText: false,
                data: [
                    <?php foreach ($statusLabels as $key => $data) { ?>
                        <?php if ($data['active']) { ?> {
                                innerHTML: '<?php echo "<i class=\"fas fa-fw " . $data["icon"] . "\"></i>&nbsp;" . $data['title']; ?>',
                                text: "<?= $data['title'] ?>",
                                value: "<?= $key ?>",
                                selected: <?php echo $canvasItem['status'] == $key ? 'true' : 'false'; ?>
                            },
                        <?php } ?>
                    <?php } ?>
                ]
            });
        <?php } ?>

        <?php if (!empty($relatesLabels)) { ?>
            new SlimSelect({
                select: '#relatesCanvas',
                showSearch: false,
                valuesUseText: false,
                data: [
                    <?php foreach ($relatesLabels as $key => $data) { ?>
                        <?php if ($data['active']) { ?> {
                                innerHTML: '<?php echo "<i class=\"fas fa-fw " . $data["icon"] . "\"></i>&nbsp;" . $data['title']; ?>',
                                text: "<?= $data['title'] ?>",
                                value: "<?= $key ?>",
                                selected: <?php echo $canvasItem['relates'] == $key ? 'true' : 'false'; ?>
                            },
                        <?php } ?>
                    <?php } ?>
                ]
            });
        <?php } ?>

        leantime.editorController.initSimpleEditor();

        <?php if (!$login::userIsAtLeast($roles::$editor)) { ?>
            leantime.authController.makeInputReadonly(".nyroModalCont");

        <?php } ?>

        <?php if ($login::userHasRole([$roles::$commenter])) { ?>
            leantime.commentsController.enableCommenterForms();
        <?php } ?>

    })
</script>

@endsection