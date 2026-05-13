{{--
    Rendered by PlanItems::editSection().
    Variables: $planId (int), $field (string), $currentValue (string)
    Replaces the outer section div identified by #section-{field} in showPlan.blade.php.
--}}
<div id="section-{{ $field }}">
    <form hx-post="{{ BASE_URL }}/hx/weeklyplanning/planItems/saveSection"
          hx-target="#section-{{ $field }}"
          hx-swap="outerHTML">
        <input type="hidden" name="planId" value="{{ $planId }}">
        <input type="hidden" name="field" value="{{ $field }}">

        <textarea name="value"
                  class="form-control tw-w-full tw-mb-xs"
                  rows="5"
                  placeholder="{{ __('weeklyplanning.placeholders.section_text') }}">{{ $currentValue }}</textarea>

        <div class="tw-flex tw-gap-xs">
            <button type="submit" class="btn btn-primary btn-sm">
                {{ __('weeklyplanning.buttons.save') }}
            </button>
            <button type="button" class="btn btn-default btn-sm"
                    hx-get="{{ BASE_URL }}/hx/weeklyplanning/planItems/viewSection?planId={{ $planId }}&field={{ $field }}"
                    hx-target="#section-{{ $field }}"
                    hx-swap="outerHTML">
                {{ __('weeklyplanning.buttons.cancel') }}
            </button>
        </div>
    </form>
</div>
