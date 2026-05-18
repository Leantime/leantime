{{-- Rendered by PlanItems::addForm(). Variables: $planId (int) --}}
<div style="background:var(--layered-background); border:1px solid var(--main-border-color);
            border-radius:var(--box-radius-small); padding:14px 16px; margin-bottom:12px;">
    <h5 style="margin:0 0 12px; font-size:13px; font-weight:700; display:flex; align-items:center; gap:6px;">
        <i class="fa fa-pen-to-square" style="color:var(--accent1);"></i>
        {{ __('weeklyplanning.headlines.add_task') }}
    </h5>
    <form hx-post="{{ BASE_URL }}/hx/weekly-planning/planItems/add"
          hx-target="#plan-items-list"
          hx-swap="innerHTML">
        <input type="hidden" name="planId" value="{{ $planId }}">
        <div style="margin-bottom:10px;">
            <input type="text"
                   name="expectedOutcome"
                   class="form-control"
                   placeholder="{{ __('weeklyplanning.placeholders.task_title') }}"
                   required
                   style="border-radius:var(--element-radius);">
        </div>
        <div style="display:flex; gap:8px;">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fa fa-check"></i> {{ __('weeklyplanning.buttons.add_task') }}
            </button>
            <button type="button" class="btn btn-default btn-sm"
                    onclick="document.getElementById('add-item-container').innerHTML = ''">
                {{ __('weeklyplanning.buttons.cancel') }}
            </button>
        </div>
    </form>
</div>
