<?php

use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Sprints\Models\Sprints;

$currentUrlPath = BASE_URL . "/" . str_replace(".", "/", Frontcontroller::getCurrentRoute());

$currentSprintId = $tpl->get("currentSprint");
$searchCriteria = $tpl->get("searchCriteria");
$searchSprint = $searchCriteria['sprint'] ?? '';
$sprints        = $tpl->get("sprints");

$sprint = false;

$currentSprintId = $currentSprintId == '' ? "all" : $currentSprintId;
if ($currentSprintId == 'all') {
    $sprint = new Sprints();
    $sprint->id = 'all';
    $sprint->name = $tpl->__("links.all_todos");
}

if ($currentSprintId == 'backlog') {
    $sprint = new Sprints();
    $sprint->id = 'backlog';
    $sprint->name = $tpl->__("links.backlog");
}

if (is_array($tpl->get('sprints'))) {
    foreach ($tpl->get('sprints') as $sprintRow) {
        if ($sprintRow->id == $currentSprintId) {
            $sprint = $sprintRow;
            break;
        }
    }
}

?>

<?php $tpl->dispatchTplEvent('beforePageHeaderOpen'); ?>
<div class="pageheader">
    <?php $tpl->dispatchTplEvent('afterPageHeaderOpen'); ?>
    <div class="pageicon">
        <span class="fa fa-fw fa-thumb-tack"></span>
    </div>
    <div class="pagetitle">
        <h5><?php $tpl->e(session("currentProjectClient") ?? '' . " // " . session("currentProjectName") ?? ''); ?></h5>

        <?php  if (
            ($tpl->get('currentSprint') !== false)
                && ($tpl->get('currentSprint') !== null)
                && count($tpl->get('sprints'))  > 0
                && $currentSprintId != 'all'
                && $currentSprintId != 'backlog'
) {?>
            <span class="dropdown dropdownWrapper headerEditDropdown">
                @php
                // Define the button with the ellipsis icon as the labelText
                $labelText = '<a href="javascript:void(0)" class="dropdown-toggle btn btn-transparent"><i class="fa-solid fa-ellipsis-v"></i></a>';
            @endphp
            
            <x-global::content.context-menu 
                class="dropdownWrapper headerEditDropdown"
                :labelText="$labelText"
                align="start"
                contentRole="ghost"
            >
                @if ($login::userIsAtLeast($roles::$editor))
                    <x-global::actions.dropdown.item 
                        href="#/sprints/editSprint/{{ $tpl->get('currentSprint') }}"
                    >
                        {{ $tpl->__("link.edit_sprint") }}
                    </x-global::actions.dropdown.item>
            
                    <x-global::actions.dropdown.item 
                        href="#/sprints/delSprint/{{ $tpl->get('currentSprint') }}" 
                        class="delete"
                    >
                        {{ $tpl->__("links.delete_sprint") }}
                    </x-global::actions.dropdown.item>
                @endif
            </x-global::content.context-menu>
            
            </span>
        <?php } ?>

        <h1>
            <?=$tpl->__("headlines.todos"); ?>
            //
            <span class="dropdown dropdownWrapper">
                <?php
                // Determine the label text for the dropdown trigger
                $labelText = ($sprint !== false) ? $tpl->escape($sprint->name) : $tpl->__("label.select_sprint");
            ?>
            
            <x-global::actions.dropdown 
                :labelText="html_entity_decode($labelText . ' <i class=\'fa fa-caret-down\'></i>')"
                class="header-title-dropdown"
                align="start"
                contentRole="ghost"
            >
                <x-slot:menu>
                    <!-- Create Sprint Option -->
                    <x-global::actions.dropdown.item 
                        href="#/sprints/editSprint/" 
                        class="wikiModal inlineEdit"
                    >
                        <i class="fa-solid fa-plus"></i> {{ $tpl->__("links.create_sprint_no_icon") }}
                    </x-global::actions.dropdown.item>
            
                    <li class="nav-header border"></li>
            
                    <!-- Static Menu Items -->
                    <x-global::actions.dropdown.item 
                        href="javascript:void(0);" 
                        onclick="jQuery('#sprintSelect').val('all'); leantime.ticketsController.initTicketSearchUrlBuilder('{{ $currentUrlPath }}')" 
                        class="text-black"
                    >
                        {{ $tpl->__("links.all_todos") }}
                    </x-global::actions.dropdown.item>
                    
                    <x-global::actions.dropdown.item 
                        href="javascript:void(0);" 
                        onclick="jQuery('#sprintSelect').val('backlog'); leantime.ticketsController.initTicketSearchUrlBuilder('{{ $currentUrlPath }}')" 
                        class="text-black"
                    >
                        {{ $tpl->__("links.backlog") }}
                    </x-global::actions.dropdown.item>
                
                    <!-- Dynamic Sprint Items -->
                    @foreach ($tpl->get('sprints') as $sprintRow)
                        <x-global::actions.dropdown.item 
                            href="javascript:void(0);" 
                            onclick="jQuery('#sprintSelect').val({{ $sprintRow->id }}); leantime.ticketsController.initTicketSearchUrlBuilder('{{ $currentUrlPath }}')" 
                            class="text-black"
                        >
                            {{ $tpl->escape($sprintRow->name) }}<br />
                            <small>{{ sprintf($tpl->__("label.date_from_date_to"), format($sprintRow->startDate)->date(), format($sprintRow->endDate)->date()) }}</small>
                        </x-global::actions.dropdown.item>
                    @endforeach
                
                </x-slot:menu>
            </x-global::actions.dropdown>
            
            </span>

        </h1>
        <input type="hidden" name="sprintSelect" id="sprintSelect" value="<?=$currentSprintId?>" />
    </div>
    <?php $tpl->dispatchTplEvent('beforePageHeaderClose'); ?>
</div><!--pageheader-->
<?php $tpl->dispatchTplEvent('afterPageHeaderClose'); ?>
