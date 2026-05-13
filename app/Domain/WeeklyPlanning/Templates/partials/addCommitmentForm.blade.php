{{--
    Rendered by PlanItems::commitmentForm().
    Variables: $planId (int), $teamMembers (array)
--}}
<div class="tw-p-m tw-mb-s tw-rounded" style="background:var(--secondary-background); border:1px solid var(--main-border-color);">
    <h5 class="tw-mb-s">{{ __('weeklyplanning.headlines.add_commitment') }}</h5>

    <form hx-post="{{ BASE_URL }}/hx/weeklyplanning/planItems/addCommitment"
          hx-target="#commitments-list"
          hx-swap="innerHTML">
        <input type="hidden" name="planId" value="{{ $planId }}">

        <div class="form-group tw-mb-xs">
            <input type="text"
                   name="task"
                   class="form-control"
                   placeholder="{{ __('weeklyplanning.placeholders.commitment_task') }}"
                   required>
        </div>

        <div class="form-group tw-mb-xs">
            <select name="ownerId" class="form-control">
                @foreach($teamMembers as $member)
                    <option value="{{ $member['id'] }}">
                        {{ $member['firstname'] }} {{ $member['lastname'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group tw-mb-xs">
            <input type="date"
                   name="deadline"
                   class="form-control"
                   required>
        </div>

        <div class="tw-flex tw-gap-xs">
            <button type="submit" class="btn btn-primary btn-sm">
                {{ __('weeklyplanning.buttons.add') }}
            </button>
            <button type="button" class="btn btn-default btn-sm"
                    onclick="document.getElementById('commitment-container').innerHTML = ''">
                {{ __('weeklyplanning.buttons.cancel') }}
            </button>
        </div>
    </form>
</div>
