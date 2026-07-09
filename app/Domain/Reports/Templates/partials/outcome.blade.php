{{--
    Outcome & impact block of a completed milestone: shows the narrative when present, offers
    inline capture right on the report when missing. The save posts via HTMX and this partial
    re-renders in place.

    Expects:
    $milestone: object - id, outcomeImpact
    $canEdit:   bool - show the inline add/edit affordance
    $period:    \Leantime\Domain\Reports\Models\ReportPeriod - carried through the save round-trip
--}}
<div class="milestoneOutcome" id="milestoneOutcome-{{ $milestone->id }}">

    @if (!empty($milestone->outcomeImpact))
        <div class="tw-text-sm outcomeText">
            {{ $milestone->outcomeImpact }}
            @if ($canEdit)
                <a href="javascript:void(0)" class="tw-opacity-50 hideOnPrint"
                   onclick="jQuery('#milestoneOutcome-{{ $milestone->id }} .outcomeText').hide(); jQuery('#milestoneOutcome-{{ $milestone->id }} .outcomeForm').show();">
                    <i class="fa fa-pencil"></i>
                </a>
            @endif
        </div>
    @elseif ($canEdit)
        <div class="outcomeText hideOnPrint">
            <a href="javascript:void(0)" class="tw-text-sm tw-opacity-60"
               onclick="jQuery('#milestoneOutcome-{{ $milestone->id }} .outcomeText').hide(); jQuery('#milestoneOutcome-{{ $milestone->id }} .outcomeForm').show();">
                <i class="fa fa-plus-circle"></i> {{ __('links.add_outcome_impact') }}
            </a>
        </div>
    @endif

    @if ($canEdit)
        <form class="outcomeForm tw-mt-1 hideOnPrint" style="display:none;"
              hx-post="{{ BASE_URL }}/hx/reports/outcome/save"
              hx-target="#milestoneOutcome-{{ $milestone->id }}"
              hx-swap="outerHTML">
            <input type="hidden" name="milestoneId" value="{{ $milestone->id }}" />
            <textarea name="outcomeImpact" rows="2" class="tw-w-full tw-text-sm"
                      placeholder="{{ __('input.placeholders.outcome_impact') }}">{{ $milestone->outcomeImpact }}</textarea>
            <button type="submit" class="btn btn-primary btn-xs">{{ __('buttons.save') }}</button>
            <a href="javascript:void(0)" class="btn btn-xs"
               onclick="jQuery('#milestoneOutcome-{{ $milestone->id }} .outcomeForm').hide(); jQuery('#milestoneOutcome-{{ $milestone->id }} .outcomeText').show();">
                {{ __('buttons.cancel') }}
            </a>
        </form>
    @endif
</div>
