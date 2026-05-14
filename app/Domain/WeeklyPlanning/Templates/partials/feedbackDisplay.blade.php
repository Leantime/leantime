{{--
    Rendered by Feedback::save() and Feedback::view().
    Variables: $planId (int), $type (string), $message (string),
               $canEdit (bool), $feedbackTypes (array)
    Replaces #feedback-{type} div (outerHTML swap).
--}}
<div class="tw-mb-s" id="feedback-{{ $type }}">
    <div class="tw-flex tw-justify-between tw-items-center">
        <small class="tw-font-semibold" style="color:var(--grey);">
            {{ __($feedbackTypes[$type] ?? $type) }}
        </small>
        @if($canEdit)
            <button class="btn btn-xs btn-link"
                    hx-get="{{ BASE_URL }}/hx/weekly-planning/feedback/editForm?planId={{ $planId }}&type={{ $type }}"
                    hx-target="#feedback-{{ $type }}"
                    hx-swap="outerHTML">
                <i class="fa fa-pencil"></i>
            </button>
        @endif
    </div>
    <p class="tw-text-sm tw-mb-0">{{ $message ?: '—' }}</p>
</div>
