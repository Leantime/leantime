<div class="tw-flex tw-items-start tw-gap-xs tw-mb-xs" id="commitment-{{ $c['id'] }}">
    <i class="fa {{ $c['status'] === 'done' ? 'fa-check-circle tw-text-green-600' : 'fa-circle' }} tw-mt-xs"></i>
    <div class="tw-flex-1">
        <span class="tw-text-sm {{ $c['status'] === 'done' ? 'tw-line-through' : '' }}">{{ $c['task'] }}</span>
        <small class="tw-block" style="color:var(--grey);">
            {{ $c['ownerFirstname'] ?? '' }} {{ $c['ownerLastname'] ?? '' }}
            @if(!empty($c['deadline']))
                · {{ \Carbon\Carbon::parse($c['deadline'])->format('d M Y') }}
            @endif
        </small>
    </div>
    @if(isset($isTeamLead) && $isTeamLead && $c['status'] !== 'done')
        <button class="btn btn-xs btn-link"
                hx-post="{{ BASE_URL }}/hx/weeklyplanning/planItems/markCommitmentDone?commitmentId={{ $c['id'] }}"
                hx-target="#commitment-{{ $c['id'] }}"
                hx-swap="outerHTML">
            <i class="fa fa-check"></i>
        </button>
    @endif
</div>
