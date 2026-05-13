{{--
    Rendered by PlanItems::renderCommitmentsList().
    Variables: $commitments (array), $isTeamLead (bool)
--}}
@if(count($commitments) === 0)
    <p class="tw-text-sm" style="color:var(--grey);">{{ __('weeklyplanning.text.no_commitments') }}</p>
@else
    @foreach($commitments as $c)
        @include('weeklyplanning::partials.commitment', ['c' => $c, 'isTeamLead' => $isTeamLead])
    @endforeach
@endif
