{{--
    Rendered by Feedback::editForm().
    Variables: $planId (int), $type (string), $currentMessage (string),
               $feedbackTypes (array), $canEdit (bool)
    Replaces #feedback-{type} div (outerHTML swap).
--}}
<div id="feedback-{{ $type }}">
    <div class="tw-flex tw-justify-between tw-items-center tw-mb-xs">
        <small class="tw-font-semibold" style="color:var(--grey);">
            {{ __($feedbackTypes[$type] ?? $type) }}
        </small>
    </div>

    <form hx-post="{{ BASE_URL }}/hx/weeklyplanning/feedback/save"
          hx-target="#feedback-{{ $type }}"
          hx-swap="outerHTML">
        <input type="hidden" name="planId" value="{{ $planId }}">
        <input type="hidden" name="type" value="{{ $type }}">

        <textarea name="message"
                  class="form-control tw-w-full tw-mb-xs"
                  rows="3"
                  placeholder="{{ __('weeklyplanning.placeholders.feedback_message') }}">{{ $currentMessage }}</textarea>

        <div class="tw-flex tw-gap-xs">
            <button type="submit" class="btn btn-primary btn-xs">
                {{ __('weeklyplanning.buttons.save') }}
            </button>
            <button type="button" class="btn btn-default btn-xs"
                    hx-get="{{ BASE_URL }}/hx/weeklyplanning/feedback/view?planId={{ $planId }}&type={{ $type }}"
                    hx-target="#feedback-{{ $type }}"
                    hx-swap="outerHTML">
                {{ __('weeklyplanning.buttons.cancel') }}
            </button>
        </div>
    </form>
</div>
