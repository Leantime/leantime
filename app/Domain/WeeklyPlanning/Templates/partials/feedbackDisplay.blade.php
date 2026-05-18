{{--
    Rendered by Feedback::save() and Feedback::view().
    Variables: $planId (int), $type (string), $message (string),
               $canEdit (bool), $feedbackTypes (array)
    Replaces #feedback-{type} div (outerHTML swap).
--}}
<div class="wp-fb-row" id="feedback-{{ $type }}">
    <div class="wp-fb-edit-row">
        <span class="wp-fb-label">{{ __($feedbackTypes[$type] ?? $type) }}</span>
        @if($canEdit)
        <button class="wp-edit-btn"
                hx-get="{{ BASE_URL }}/hx/weekly-planning/feedback/editForm?planId={{ $planId }}&type={{ $type }}"
                hx-target="#feedback-{{ $type }}"
                hx-swap="outerHTML"
                title="Edit">
            <i class="fa fa-pencil"></i>
        </button>
        @endif
    </div>
    @if($message)
    <div class="wp-fb-text">{{ $message }}</div>
    @else
    <div class="wp-fb-empty">Not provided yet</div>
    @endif
</div>
