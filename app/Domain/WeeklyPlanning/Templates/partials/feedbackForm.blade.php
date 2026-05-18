{{--
    Rendered by Feedback::editForm().
    Variables: $planId (int), $type (string), $currentMessage (string),
               $feedbackTypes (array), $canEdit (bool)
    Replaces #feedback-{type} div (outerHTML swap).
--}}
<div id="feedback-{{ $type }}">
    <div class="wp-fb-edit-row">
        <span class="wp-fb-label">{{ __($feedbackTypes[$type] ?? $type) }}</span>
    </div>

    <form hx-post="{{ BASE_URL }}/hx/weekly-planning/feedback/save"
          hx-target="#feedback-{{ $type }}"
          hx-swap="outerHTML">
        <input type="hidden" name="planId" value="{{ $planId }}">
        <input type="hidden" name="type" value="{{ $type }}">

        <textarea name="message"
                  class="form-control"
                  rows="3"
                  placeholder="{{ __('weeklyplanning.placeholders.feedback_message') }}"
                  style="margin-bottom:8px; font-size:13px; border-radius:var(--element-radius);">{{ $currentMessage }}</textarea>

        <div style="display:flex; gap:6px;">
            <button type="submit" class="btn btn-primary btn-xs">
                <i class="fa fa-check"></i> {{ __('weeklyplanning.buttons.save') }}
            </button>
            <button type="button" class="btn btn-default btn-xs"
                    hx-get="{{ BASE_URL }}/hx/weekly-planning/feedback/view?planId={{ $planId }}&type={{ $type }}"
                    hx-target="#feedback-{{ $type }}"
                    hx-swap="outerHTML">
                {{ __('weeklyplanning.buttons.cancel') }}
            </button>
        </div>
    </form>
</div>
