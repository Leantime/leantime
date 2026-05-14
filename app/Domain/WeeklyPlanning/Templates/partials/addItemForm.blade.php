{{-- Rendered by PlanItems::addForm(). Variables: $planId (int) --}}
<div class="tw-p-m tw-mb-m tw-rounded"
     style="background:var(--secondary-background); border:1px solid var(--main-border-color);">
    <h5 class="tw-mb-s">{{ __('weeklyplanning.headlines.add_task') }}</h5>

    <form hx-post="{{ BASE_URL }}/hx/weekly-planning/planItems/add"
          hx-target="#plan-items-list"
          hx-swap="innerHTML">
        <input type="hidden" name="planId" value="{{ $planId }}">

        <div class="form-group tw-mb-s">
            <label class="tw-text-sm tw-font-semibold">{{ __('weeklyplanning.labels.task') }}</label>
            <input type="text"
                   name="expectedOutcome"
                   class="form-control"
                   placeholder="{{ __('weeklyplanning.placeholders.task_title') }}"
                   required>
        </div>

        <div class="tw-flex tw-gap-xs">
            <button type="submit" class="btn btn-primary btn-sm">
                {{ __('weeklyplanning.buttons.add_task') }}
            </button>
            <button type="button"
                    class="btn btn-default btn-sm"
                    onclick="document.getElementById('add-item-container').innerHTML = ''">
                {{ __('weeklyplanning.buttons.cancel') }}
            </button>
        </div>
    </form>
</div>
