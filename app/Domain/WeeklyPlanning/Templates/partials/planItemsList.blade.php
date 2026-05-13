{{--
    Rendered by PlanItems::renderItemsList().
    Variables: $planId (int), $items (array), $itemStatuses (array), $isTeamLead (bool)
--}}
@if(count($items) === 0)
    <tr>
        <td colspan="{{ $isTeamLead ? 3 : 2 }}" class="tw-text-center" style="color:var(--grey);">
            {{ __('weeklyplanning.text.no_tasks_in_plan') }}
        </td>
    </tr>
@else
    @foreach($items as $item)
        @include('weeklyplanning::partials.planItem', ['item' => $item, 'isTeamLead' => $isTeamLead])
    @endforeach
@endif
