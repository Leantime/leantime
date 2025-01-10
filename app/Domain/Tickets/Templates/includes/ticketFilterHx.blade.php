<?php

use Leantime\Core\Controller\Frontcontroller;

$currentRoute = currentRoute();

$currentUrlPath = BASE_URL . '/' . str_replace('.', '/', $currentRoute);
$groupBy = $tpl->get('groupByOptions');
$sortBy = $tpl->get('sortOptions');
$searchCriteria = $tpl->get('searchCriteria');
$statusLabels = $tpl->get('allTicketStates');
$taskToggle = $tpl->get('enableTaskTypeToggle');

?>
<form id="ticketSearch">

    <input type="hidden" value="1" name="search" />
    <input type="hidden" value="<?php echo session('currentProject'); ?>" name="projectId" id="projectIdInput" />

    <!-- Trigger for the dropdown -->
    <x-global::actions.dropdown variant="card" contentRole="ghost" cardLabel="Filter Options" scale="sm">
        <x-slot:labelText>
            {{ __('popover.filter') }}
            <span class="fa-solid fa-filter"></span>
        </x-slot:labelText>
        <x-slot:cardContent>
            <!-- Filter Bar Content -->
            {{-- @dispatchTplEvent('filters.beforeFirstBarField') --}}

            <div>
                <x-global::forms.select id="userSelect" name="users" variant='multiple' search="false"
                    content-role="ghost" label-position="top"
                    data-placeholder="{{ __('input.placeholders.filter_by_user') }}"
                    title="{{ __('input.placeholders.filter_by_user') }}">
                    <x-slot:labelText>{{ __('label.user') }}</x-slot:labelText>
                    <x-global::forms.select.select-option value="" data-placeholder="true">All
                        Users</x-global::forms.select.select-option>
                    @foreach ($tpl->get('users') as $userRow)
                        <x-global::forms.select.option value="{{ $userRow['id'] }}" :selected="in_array($userRow['id'], explode(',', $searchCriteria['users'] ?? ''))">
                            {{ sprintf(__('text.full_name'), e($userRow['firstname']), e($userRow['lastname'])) }}
                        </x-global::forms.select.option>
                    @endforeach
                </x-global::forms.select>
            </div>

            <div>
                <x-global::forms.select name="milestone" id="milestoneSelect" variant='multiple'
                    data-placeholder="{{ __('input.placeholders.filter_by_milestone') }}"
                    title="{{ __('input.placeholders.filter_by_milestone') }}">
                    <x-slot:labelText>{{ __('label.milestone') }}</x-slot:labelText>
                    <x-global::forms.select.select-option value="" data-placeholder="true">
                        {{ __('label.all_milestones') }}
                    </x-global::forms.select.select-option>
                    @foreach ($tpl->get('milestones') as $milestoneRow)
                        <x-global::forms.select.select-option value="{{ $milestoneRow->id }}" :selected="in_array($milestoneRow->id, explode(',', $searchCriteria['milestone'] ?? ''))">
                            {{ $tpl->escape($milestoneRow->headline) }}
                        </x-global::forms.select.select-option>
                    @endforeach
                </x-global::forms.select>
            </div>

            <div>
                <x-global::forms.select name="type" id="typeSelect" variant='multiple'
                    data-placeholder="{{ __('input.placeholders.filter_by_type') }}"
                    title="{{ __('input.placeholders.filter_by_type') }}">
                    <x-slot:labelText>{{ __('label.todo_type') }}</x-slot:labelText>
                    <x-global::forms.select.select-option value="" data-placeholder="true">
                        {{ __('label.all_types') }}
                    </x-global::forms.select.select-option>
                    @foreach ($tpl->get('types') as $type)
                        <x-global::forms.select.select-option value="{{ $type }}" :selected="in_array($type, explode(',', $searchCriteria['type'] ?? ''))">
                            {{ $type }}
                        </x-global::forms.select.select-option>
                    @endforeach
                </x-global::forms.select>
            </div>

            <div>
                <x-global::forms.select name="priority" id="prioritySelect" variant='multiple'
                    data-placeholder="{{ __('input.placeholders.filter_by_priority') }}"
                    title="{{ __('input.placeholders.filter_by_priority') }}">
                    <x-slot:labelText>{{ __('label.todo_priority') }}</x-slot:labelText>
                    <x-global::forms.select.select-option value="" data-placeholder="true">
                        {{ __('label.all_priorities') }}
                    </x-global::forms.select.select-option>
                    @foreach ($tpl->get('priorities') as $priorityKey => $priorityValue)
                        <x-global::forms.select.select-option value="{{ $priorityKey }}" :selected="in_array($priorityKey, explode(',', $searchCriteria['priority'] ?? ''))">
                            {{ $priorityValue }}
                        </x-global::forms.select.select-option>
                    @endforeach
                </x-global::forms.select>
            </div>

            <div>
                <x-global::forms.select name="searchStatus" id="statusSelect" variant='multiple'
                    data-placeholder="{{ __('input.placeholders.filter_by_status') }}">
                    <x-slot:labelText>{{ __('label.todo_status') }}</x-slot:labelText>
                    <x-global::forms.select.select-option value="" data-placeholder="true">
                        {{ __('label.all_statuses') }}
                    </x-global::forms.select.select-option>
                    <x-global::forms.select.select-option value="not_done" :selected="$searchCriteria['status'] && str_contains($searchCriteria['status'], 'not_done')">
                        {{ __('label.not_done') }}
                    </x-global::forms.select.select-option>
                    @foreach ($statusLabels as $key => $label)
                        <x-global::forms.select.select-option value="{{ $key }}" :selected="in_array((string) $key, explode(',', $searchCriteria['status'] ?? ''))">
                            {{ $tpl->escape($label['name']) }}
                        </x-global::forms.select.select-option>
                    @endforeach
                </x-global::forms.select>
            </div>

            <div>
                <x-global::forms.text-input name="termInput" id="termInput" :value="$searchCriteria['term']" variant="noBorder"
                    caption="{{ __('label.search_term') }}" placeholder="{{ __('label.search_term') }}"
                    class="h-8" />
            </div>

            <div style="margin-top: 15px;">
                <x-global::forms.button type="submit" name="search" class="btn btn-primary">
                    {{ __('buttons.search') }}
                </x-global::forms.button>
            </div>

        </x-slot:cardContent>
    </x-global::actions.dropdown>



    <?php if ($currentRoute !== 'tickets.roadmap' && $currentRoute != "tickets.showProjectCalendar") {?>
    <x-global::actions.dropdown contentRole="ghost" scale="sm">
        <x-slot:labelText>
            {{ __('popover.group_by') }}
            <span class="fa-solid fa-diagram-project"></span>
            @if ($searchCriteria['groupBy'] !== 'all' && $searchCriteria['groupBy'] !== '')
                <span class="badge badge-primary">1</span>
            @endif
        </x-slot:labelText>

        <x-slot:menu>
            @foreach ($groupBy as $input)
                <x-global::actions.dropdown.item>
                    <span>
                        <input type="radio" name="groupBy" @if ($searchCriteria['groupBy'] == $input['field']) checked='checked' @endif
                            value="{{ $input['field'] }}" id="{{ $input['id'] }}"
                            onclick="leantime.ticketsController.initTicketSearchUrlBuilder('{{ $currentUrlPath }}')" />
                        <label for="{{ $input['id'] }}">{{ __('label.' . $input['label']) }}</label>
                    </span>
                </x-global::actions.dropdown.item>
            @endforeach
        </x-slot:menu>
    </x-global::actions.dropdown>
    <?php } ?>

    {{-- @if ($currentRoute !== 'tickets.roadmap' && $currentRoute != 'tickets.showProjectCalendar')
        <x-global::actions.dropdown contentRole="ghost">
            <x-slot:labelText>
                {{ __('popover.group_by') }}
                <span class="fa-solid fa-diagram-project"></span>
                @if ($searchCriteria['groupBy'] !== 'all' && $searchCriteria['groupBy'] !== '')
                    <span class="badge badge-primary">1</span>
                @endif
                </button>
            </x-slot:labelText>

            <x-slot:menu>
                @foreach ($groupBy as $input)
                    <x-global::actions.dropdown.item>
                        <span>
                            <input type="radio" name="groupBy" @if ($searchCriteria['groupBy'] == $input['field']) checked='checked' @endif
                                value="{{ $input['field'] }}" id="{{ $input['id'] }}"
                                onclick="leantime.ticketsController.initTicketSearchUrlBuilder('{{ $currentUrlPath }}')" />
                            <label for="{{ $input['id'] }}">{{ __('label.' . $input['label']) }}</label>
                        </span>
                    </x-global::actions.dropdown.item>
                @endforeach
            </x-slot:menu>
        </x-global::actions.dropdown>
    @endif --}}

    @if (isset($taskToggle) && $taskToggle === true)
        <div class="" style="float:right; margin-left:5px;">
            <x-global::forms.checkbox labelText="Show Tasks" labelPosition="right" name="showTasks" value="true"
                :checked="($tpl->get('showTasks') === 'true')" id="taskTypeToggle" class="toggle" onchange="jQuery('#ticketSearch').submit();" />
        </div>
    @endif


    <div class="clearall"></div>

    @php
        $tpl->dispatchTplEvent('filters.beforeBar');
        $tpl->dispatchTplEvent('filters.beforeFormClose');
    @endphp

</form>

<script type="module">
    import "@mix('/js/Domain/Tickets/Js/ticketsController.js')"

    jQuery(document).ready(function() {
        console.log('ready');

        jQuery('#ticketSearch').on('submit', function(e) {
            e.preventDefault();

            var formData = new URLSearchParams(jQuery(this).serialize());

            jQuery('.ticketColumn').each(function() {
                var column = jQuery(this);
                var status = column.attr('id').replace('ticketColumn_', '');

                // Construct new URL with search parameters
                var newUrl = `{{ BASE_URL }}/hx/tickets/ticketColumn/get?status=${status}`;
                formData.forEach((value, key) => {
                    if (value) { // Only add non-empty values
                        newUrl += `&${key}=${encodeURIComponent(value)}`;
                    }
                });

                column.attr('hx-get', newUrl);
                htmx.process(column[0]); // Tell HTMX to process the updated attributes

                htmx.trigger(column[0], 'reload');
            });
        });

        new SlimSelect({
            select: '#userSelect',
            settings: {
                placeholderText: 'All Users',
            },
        });
        new SlimSelect({
            select: '#milestoneSelect',
            settings: {
                placeholderText: 'All Milestones',
            },
        });
        new SlimSelect({
            select: '#prioritySelect',
            settings: {
                placeholderText: 'All Priorities',
            },
        });
        new SlimSelect({
            select: '#typeSelect',
            settings: {
                placeholderText: 'All Types',
            },
        });
        new SlimSelect({
            select: '#statusSelect',
            settings: {
                placeholderText: 'All Statuses',
            },
        });

        ticketsController.initTicketSearchSubmit('<?= $currentUrlPath ?>');


    })
</script>
