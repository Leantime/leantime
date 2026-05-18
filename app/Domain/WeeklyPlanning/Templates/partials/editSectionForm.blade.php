{{--
    Rendered by PlanItems::editSection().
    Variables: $planId (int), $field (string), $currentValue (string)
    Replaces the outer section div identified by #section-{field} in showPlan.blade.php.
--}}
<div class="wp-card" id="section-{{ $field }}">
    <div class="wp-card-head">
        <h4 class="wp-card-title" style="font-size:13px; font-weight:700; margin:0;">
            <i class="fa fa-pencil" style="color:var(--accent1);"></i> Editing section
        </h4>
    </div>
    <div class="wp-card-body">
        <form hx-post="{{ BASE_URL }}/hx/weekly-planning/planItems/saveSection"
              hx-target="#section-{{ $field }}"
              hx-swap="outerHTML">
            <input type="hidden" name="planId" value="{{ $planId }}">
            <input type="hidden" name="field" value="{{ $field }}">

            <textarea name="value"
                      class="form-control"
                      rows="5"
                      placeholder="{{ __('weeklyplanning.placeholders.section_text') }}"
                      style="margin-bottom:10px; border-radius:var(--element-radius);">{{ $currentValue }}</textarea>

            <div style="display:flex; gap:8px;">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fa fa-check"></i> {{ __('weeklyplanning.buttons.save') }}
                </button>
                <button type="button" class="btn btn-default btn-sm"
                        hx-get="{{ BASE_URL }}/hx/weekly-planning/planItems/viewSection?planId={{ $planId }}&field={{ $field }}"
                        hx-target="#section-{{ $field }}"
                        hx-swap="outerHTML">
                    {{ __('weeklyplanning.buttons.cancel') }}
                </button>
            </div>
        </form>
    </div>
</div>
