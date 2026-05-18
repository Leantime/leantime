{{--
    Rendered by PlanItems::commitmentForm().
    Variables: $planId (int), $teamMembers (array)
--}}
<div style="background:var(--layered-background); border:1px solid var(--main-border-color);
            border-radius:var(--box-radius-small); padding:14px; margin-bottom:10px;">
    <h5 style="margin:0 0 12px; font-size:13px; font-weight:700; display:flex; align-items:center; gap:6px;">
        <i class="fa fa-plus" style="color:var(--accent1);"></i>
        {{ __('weeklyplanning.headlines.add_commitment') }}
    </h5>

    <form hx-post="{{ BASE_URL }}/hx/weekly-planning/planItems/addCommitment"
          hx-target="#commitments-list"
          hx-swap="innerHTML">
        <input type="hidden" name="planId" value="{{ $planId }}">

        <div style="margin-bottom:8px;">
            <input type="text"
                   name="task"
                   class="form-control"
                   placeholder="{{ __('weeklyplanning.placeholders.commitment_task') }}"
                   required
                   style="border-radius:var(--element-radius);">
        </div>

        <div style="margin-bottom:8px;">
            <select name="ownerId" class="form-control" style="border-radius:var(--element-radius);">
                @foreach($teamMembers as $member)
                <option value="{{ $member['id'] }}">
                    {{ $member['firstname'] }} {{ $member['lastname'] }}
                </option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom:10px;">
            <input type="date"
                   name="deadline"
                   class="form-control"
                   required
                   style="border-radius:var(--element-radius);">
        </div>

        <div style="display:flex; gap:6px;">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fa fa-check"></i> {{ __('weeklyplanning.buttons.add') }}
            </button>
            <button type="button" class="btn btn-default btn-sm"
                    onclick="document.getElementById('commitment-container').innerHTML = ''">
                {{ __('weeklyplanning.buttons.cancel') }}
            </button>
        </div>
    </form>
</div>
