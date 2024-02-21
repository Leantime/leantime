<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'includeTitle' => true,
    'tickets' => [],
    'onTheClock' => false,
    'groupBy' => '',
    'allProjects' => [],
    'allAssignedprojects' => [],
    'projectFilter' => '',

]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'includeTitle' => true,
    'tickets' => [],
    'onTheClock' => false,
    'groupBy' => '',
    'allProjects' => [],
    'allAssignedprojects' => [],
    'projectFilter' => '',

]); ?>
<?php foreach (array_filter(([
    'includeTitle' => true,
    'tickets' => [],
    'onTheClock' => false,
    'groupBy' => '',
    'allProjects' => [],
    'allAssignedprojects' => [],
    'projectFilter' => '',

]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div class="clear">
    <div class="row" id="yourToDoContainer">
        <div class="col-md-12">
            <div class="tw-mb-l">

                <form method="get">
                    <?php $tpl->dispatchTplEvent("beforeTodoWidgetGroupByDropdown"); ?>

                    <a href="javascript:void(0);"
                       id="ticket_new_link"
                        class="btn btn-primary"
                       onclick="jQuery('#ticket_new').toggle('fast', function() { jQuery(this).find('input[name=headline]').focus(); });">
                        <i class="fa fa-plus"></i> Add To-Do
                    </a>
                    <div class="btn-group left">
                        <button class="btn btn-link dropdown-toggle f-right" type="button" data-toggle="dropdown"><?php echo __("links.group_by"); ?>: <?php echo e(__('groupByLabel.'.$groupBy)); ?></button>
                        <ul class="dropdown-menu">
                            <li><span class="radio">
                                    <input type="radio" name="groupBy"
                                           <?php if($groupBy == "time"): ?> checked='checked' <?php endif; ?>
                                           value="time" id="groupByDate"
                                           hx-get="<?php echo e(BASE_URL); ?>/widgets/myToDos/get"
                                           hx-trigger="click"
                                           hx-target="#yourToDoContainer"
                                           hx-swap="outerHTML"
                                           hx-indicator="#todos .htmx-indicator"
                                           hx-vals='{"projectFilter": <?php echo e($projectFilter); ?>, "groupBy": "time" }'
                                        />
                                    <label for="groupByDate"><?php echo __("label.dates"); ?></label></span></li>
                            <li>
                                <span class="radio">
                                    <input type="radio"
                                           name="groupBy"
                                           <?php if($groupBy == "project"): ?> checked='checked' <?php endif; ?>
                                           value="project" id="groupByProject"
                                           hx-get="<?php echo e(BASE_URL); ?>/widgets/myToDos/get"
                                           hx-trigger="click"
                                           hx-target="#yourToDoContainer"
                                           hx-swap="outerHTML"
                                           hx-indicator="#todos .htmx-indicator"
                                           hx-vals='{"projectFilter": <?php echo e($projectFilter); ?>, "groupBy": "project" }'
                                    />
                                    <label for="groupByProject"><?php echo __("label.project"); ?></label>
                                </span>
                            </li>

                        </ul>
                    </div>
                    <div class="btn-group left ">
                        <button class="btn btn-link dropdown-toggle f-right" type="button" data-toggle="dropdown">
                            <?php echo __("links.filter"); ?>

                            <?php if($projectFilter != ''): ?>
                                <span class='badge badge-primary'>1</span>
                            <?php endif; ?>
                        </button>
                        <ul class="dropdown-menu">
                            <li
                                <?php if($projectFilter == ''): ?>
                                    class='active'
                                <?php endif; ?>
                            ><a href=""
                                hx-get="<?php echo e(BASE_URL); ?>/widgets/myToDos/get"
                                hx-trigger="click"
                                hx-target="#yourToDoContainer"
                                hx-swap="outerHTML"
                                hx-indicator="#todos .htmx-indicator"
                                hx-vals='{"projectFilter": "", "groupBy": "<?php echo e($groupBy); ?>" }'

                                ><?php echo e(__('labels.all_projects')); ?>


                                </a></li>

                            <?php if($allAssignedprojects): ?>
                                <?php $__currentLoopData = $allAssignedprojects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li
                                        <?php if($projectFilter == $project['id']): ?>
                                            class='active'
                                        <?php endif; ?>
                                    ><a href=""
                                        hx-get="<?php echo e(BASE_URL); ?>/widgets/myToDos/get"
                                        hx-trigger="click"
                                        hx-target="#yourToDoContainer"
                                        hx-swap="outerHTML"
                                        hx-indicator="#todos .htmx-indicator"
                                        hx-vals='{"projectFilter": "<?php echo e($project['id']); ?>", "groupBy": "<?php echo e($groupBy); ?>" }'
                                        ><?php echo e($project['name']); ?></a></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>

                        </ul>
                    </div>

                    <?php $tpl->dispatchTplEvent("afterTodoWidgetGroupByDropdown"); ?>
                    <div class="clearall"></div>
                </form>
            </div>
            <div class="htmx-indicator">
                <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'global::components.loadingText','data' => ['type' => 'card','count' => '5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('global::loadingText'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'card','count' => '5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
            </div>
            <div class="htmx-indicator htmx-loaded-content">
                <?php if($tickets !== null && count($tickets) == 0): ?>
                <div class='center'>
                    <div  style='width:30%' class='svgContainer'>
                        <?php echo file_get_contents(ROOT . "/dist/images/svg/undraw_a_moment_to_relax_bbpa.svg"); ?>

                    </div>
                    <br />
                    <h4><?php echo e(__("headlines.no_todos_this_week")); ?></h4>
                    <?php echo e(__("text.take_the_day_off")); ?>

                    <a href='<?php echo e(BASE_URL); ?>/tickets/showAll'><?php echo e(__("links.goto_backlog")); ?></a><br/><br/>
                </div>
                <?php endif; ?>
                <?php $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticketGroup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                    <?php
                        //Get first duedate if exist
                        $ticketCreationDueDate = '';
                        if (isset($ticketGroup['tickets'][0]) && $ticketGroup['tickets'][0]['dateToFinish'] != "0000-00-00 00:00:00" && $ticketGroup['tickets'][0]['dateToFinish'] != "1969-12-31 00:00:00") {
                            //Use the first due date as the new due date
                            $ticketCreationDueDate = $ticketGroup['tickets'][0]['dateToFinish'];
                        }

                        $groupProjectId = $_SESSION['currentProject'];

                        if ($groupBy == 'project' && isset($ticketGroup['tickets'][0])) {
                            $groupProjectId = $ticketGroup['tickets'][0]['projectId'];
                        }

                    ?>

                    <a class="anchor" id="accordion_anchor_<?php echo e($loop->index); ?>"></a>



                        <div class="hideOnLoad " id="ticket_new" style="padding-top:5px; padding-bottom:15px;">

                            <form method="post"
                                  hx-post="<?php echo e(BASE_URL); ?>//widgets/myToDos/addTodo"
                                  hx-target="#yourToDoContainer"
                                  hx-swap="outerHTML"
                                  hx-indicator="#ticket_new .htmx-indicator-small"
                            >
                                <input type="hidden" name="quickadd" value="1"/>
                                <div class="flex" style="display:flex; column-gap: 10px;">
                                    <input type="text" name="headline" style="width:100%;" placeholder="Enter To-Do Title" title="<?=$tpl->__("label.headline") ?>"/><br />

                                    <label style="padding-top: 8px;">Project</label>
                                    <select name="projectId">
                                        <?php $__currentLoopData = $allAssignedprojects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($project['id']); ?>"
                                            <?php if($groupBy == 'sprint'): ?>
                                                <?php echo e(explode("-", $ticketGroup["groupValue"])[1] == $project['id'] ? 'selected' : ''); ?>

                                                <?php else: ?>
                                                <?php echo e($_SESSION['currentProject'] == $project['id'] ? 'selected' : ''); ?>

                                                <?php endif; ?>
                                            ><?php echo e($project["name"]); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <input type="submit" value="Save" name="quickadd" />
                                <a href="javascript:void(0);" class="btn btn-default" onclick="jQuery('#ticket_new').toggle('fast');">
                                        <?=$tpl->__("links.cancel") ?>
                                </a>
                                <div class="htmx-indicator-small">
                                    <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'global::components.loader','data' => ['id' => 'loadingthis','size' => '25px']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('global::loader'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'loadingthis','size' => '25px']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                                </div>
                            </form>

                            <div class="clearfix"></div>
                        </div>



                    <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'global::components.accordion','data' => ['id' => 'ticketBox1-'.e($loop->index).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('global::accordion'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'ticketBox1-'.e($loop->index).'']); ?>
                         <?php $__env->slot('title', null, []); ?> 


                            <?php echo e(__($ticketGroup["labelName"])); ?> (<?php echo e(count($ticketGroup["tickets"])); ?>)

                         <?php $__env->endSlot(); ?>
                         <?php $__env->slot('content', null, []); ?> 

                            <ul class="sortableTicketList <?php echo e($ticketGroup["extraClass"] ?? ''); ?>">

                                <?php if(count($ticketGroup['tickets']) == 0): ?>
                                    <em>Nothing to see here. Move on.</em><br /><br />
                                <?php endif; ?>

                                <?php $__currentLoopData = $ticketGroup['tickets']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                    <li class="ui-state-default" id="ticket_<?php echo e($row['id']); ?>" >
                                        <div class="ticketBox fixed priority-border-<?php echo e($row['priority']); ?>" data-val="<?php echo e($row['id']); ?>">
                                            <div class="row">
                                                <div class="col-md-8 titleContainer">
                                                    <small><?php echo e($row['projectName']); ?></small><br />
                                                    <?php if($row['dependingTicketId'] > 0): ?>
                                                        <a href="#/tickets/showTicket/<?php echo e($row['dependingTicketId']); ?>"><?php echo e($row['parentHeadline']); ?></a> //
                                                    <?php endif; ?>
                                                    <strong><a href="#/tickets/showTicket/<?php echo e($row['id']); ?>" ><?php echo e($row['headline']); ?></a></strong>

                                                </div>
                                                <div class="col-md-4 timerContainer" style="padding:5px 15px;" id="timerContainer-<?php echo e($row['id']); ?>">

                                                    <?php echo $__env->make("tickets::partials.ticketsubmenu", ["ticket" => $row, "onTheClock" => $onTheClock], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                                                    <div class="scheduler pull-right">
                                                        <?php if( $row['editFrom'] != "0000-00-00 00:00:00" && $row['editFrom'] != "1969-12-31 00:00:00"): ?>
                                                            <i class="fa-solid fa-calendar-check infoIcon tw-mr-xs" style="color:var(--accent2)" data-tippy-content="<?php echo e(__('text.schedule_to_start_on')); ?> <?php echo e(format($row['editFrom'])->date()); ?>"></i>
                                                        <?php else: ?>
                                                            <i class="fa-regular fa-calendar-xmark infoIcon tw-mr-xs" data-tippy-content="<?php echo e(__('text.not_scheduled_drag_ai')); ?>"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4" style="padding:0 15px;">

                                                   <i class="fa-solid fa-business-time infoIcon" data-tippy-content=" <?php echo e(__("label.due")); ?>"></i>
                                                   <input type="text" title="<?php echo e(__("label.due")); ?>" value="<?php echo e(format($row['dateToFinish'])->date(__("text.anytime"))); ?>" class="duedates secretInput" style="margin-left:0px;" data-id="<?php echo e($row['id']); ?>" name="date" />
                                                </div>
                                                <div class="col-md-8 dropdownContainer" style="padding-top:5px;">
                                                    <div class="dropdown ticketDropdown statusDropdown colorized show right ">
                                                        <a class="dropdown-toggle f-left status <?php echo e($statusLabels[$row['projectId']][$row['status']]["class"]); ?>" href="javascript:void(0);" role="button" id="statusDropdownMenuLink<?php echo e($row['id']); ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <span class="text">
                                                                <?php if(isset($statusLabels[$row['projectId']][$row['status']])): ?>
                                                                    <?php echo e($statusLabels[$row['projectId']][$row['status']]["name"]); ?>

                                                                <?php else: ?>
                                                                    unknown
                                                                <?php endif; ?>
                                                            </span>
                                                            &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                                        </a>
                                                        <ul class="dropdown-menu pull-right" aria-labelledby="statusDropdownMenuLink<?php echo e($row['id']); ?>">
                                                            <li class="nav-header border"><?php echo e(__("dropdown.choose_status")); ?></li>

                                                            <?php $__currentLoopData = $statusLabels[$row['projectId']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <li class='dropdown-item'>
                                                                    <a href='javascript:void(0);'
                                                                       class='<?php echo e($label["class"]); ?>'
                                                                       data-label='<?php echo e($label["name"]); ?>'
                                                                       data-value='<?php echo e($row['id']); ?>_<?php echo e($key); ?>_<?php echo e($label["class"]); ?>'
                                                                       id='ticketStatusChange<?php echo e($row['id'] . $key); ?>'>
                                                                        <?php echo e($label["name"]); ?>

                                                                    </a>
                                                                </li>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </ul>
                                                    </div>

                                                    <?php /*
                                                        <div class="dropdown ticketDropdown effortDropdown show right">
                                                            <a class="dropdown-toggle f-left  label-default effort" href="javascript:void(0);" role="button" id="effortDropdownMenuLink{{ $row['id'] }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <span class="text">
                                                                @if ($row['storypoints'] != '' && $row['storypoints'] > 0)
                                                                    {{ $efforts["" . $row['storypoints']] ?? $row['storypoints'] }}
                                                                @else
                                                                    {{ __("label.story_points_unkown") }}
                                                                @endif
                                                            </span>
                                                                &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                                            </a>
                                                            <ul class="dropdown-menu" aria-labelledby="effortDropdownMenuLink{{ $row['id'] }}">
                                                                <li class="nav-header border">{{ __("dropdown.how_big_todo") }}</li>
                                                                @foreach($efforts as $effortKey => $effortValue)
                                                                    <li class='dropdown-item'>
                                                                        <a href='javascript:void(0);'
                                                                           data-value='{{ $row['id'] . "_" . $effortKey }}'
                                                                           id='ticketEffortChange{{ $row['id'] . $effortKey }}'>
                                                                            {{ $effortValue }}
                                                                        </a>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    */ ?>

                                                    <div class="dropdown ticketDropdown milestoneDropdown colorized show right tw-mr-sm">
                                                            <a style="background-color:<?php echo e($row['milestoneColor']); ?>"
                                                               class="dropdown-toggle f-left  label-default milestone"
                                                               href="javascript:void(0);"
                                                               role="button" id="milestoneDropdownMenuLink<?php echo e($row['id']); ?>"
                                                               data-toggle="dropdown"
                                                               aria-haspopup="true"
                                                               aria-expanded="false">
                                                                <span class="text">
                                                                    <?php if($row['milestoneid'] != "" && $row['milestoneid'] != 0): ?>
                                                                        <?php echo e($row['milestoneHeadline']); ?>

                                                                    <?php else: ?>
                                                                        <?php echo e(__("label.no_milestone")); ?>

                                                                    <?php endif; ?>
                                                                </span>
                                                                &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                                                            </a>
                                                            <ul class="dropdown-menu pull-right" aria-labelledby="milestoneDropdownMenuLink<?php echo e($row['id']); ?>">
                                                                <li class="nav-header border"><?php echo e(__("dropdown.choose_milestone")); ?></li>
                                                                <li class='dropdown-item'>
                                                                    <a style='background-color:#b0b0b0'
                                                                       href='javascript:void(0);'
                                                                       data-label="<?php echo e(__("label.no_milestone")); ?>"
                                                                       data-value='<?php echo e($row['id']); ?>_0_#b0b0b0'>
                                                                        <?php echo e(__("label.no_milestone")); ?>

                                                                    </a>
                                                                </li>
                                                                <?php if(isset($milestones[$row['projectId']])): ?>
                                                                    <?php $__currentLoopData = $milestones[$row['projectId']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $milestone): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                        <?php if(is_object($milestone)): ?>
                                                                        <li class='dropdown-item'>
                                                                            <a href='javascript:void(0);'
                                                                               data-label='<?php echo e($milestone->headline); ?>'
                                                                               data-value='<?php echo e($row['id']); ?>_<?php echo e($milestone->id); ?>_<?php echo e($milestone->tags); ?>'
                                                                               id='ticketMilestoneChange<?php echo e($row['id'] . $milestone->id); ?>'
                                                                               style='background-color:<?php echo e($milestone->tags); ?>'>
                                                                                <?php echo e($milestone->headline); ?>

                                                                            </a>
                                                                        </li>
                                                                        <?php endif; ?>
                                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                <?php endif; ?>
                                                            </ul>
                                                        </div>

                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                         <?php $__env->endSlot(); ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php $tpl->dispatchTplEvent("afterTodoListWidgetBox"); ?>
            </div>
        </div>
    </div>
</div>




<script type="text/javascript">

    <?php $tpl->dispatchTplEvent('scripts.afterOpen'); ?>;

    jQuery('.todaysDate').text(moment().format('LLLL'));

    jQuery(document).ready(function(){
        tippy('[data-tippy-content]');
        <?php if($login::userIsAtLeast(\Leantime\Domain\Auth\Models\Roles::$editor)): ?>
            leantime.dashboardController.prepareHiddenDueDate();
            leantime.ticketsController.initEffortDropdown();
            leantime.ticketsController.initMilestoneDropdown();
            leantime.ticketsController.initStatusDropdown();
            leantime.ticketsController.initDueDateTimePickers();
        <?php else: ?>
            leantime.authController.makeInputReadonly(".maincontentinner");
        <?php endif; ?>

    });

</script>


<?php /**PATH /home/lucas/code/leantime/app/Domain/Widgets/Templates/partials/myToDos.blade.php ENDPATH**/ ?>