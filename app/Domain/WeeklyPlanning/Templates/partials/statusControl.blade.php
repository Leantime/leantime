{{--
    Rendered by StatusUpdate::get() and StatusUpdate::save().
    Variables: $item (array|null), $itemStatuses (array), $reasonRequiredStatuses (array),
               $isEmployee (bool), $reasonRequired (bool, optional), $selectedStatus (string, optional)
--}}
@php
$itemId = $item['id'] ?? 0;
$currentStatus = $selectedStatus ?? ($item['status'] ?? 'not_started');
$needsReason = isset($reasonRequired) && $reasonRequired;
// Only show the reason form for developer/employee role
$showReason = ($isEmployee ?? false)
&& ($needsReason || in_array($currentStatus, $reasonRequiredStatuses ?? [], true));
@endphp

<div id="status-control-{{ $itemId }}">
    <form hx-post="{{ BASE_URL }}/hx/weeklyplanning/statusUpdate/save"
        hx-target="#status-control-{{ $itemId }}"
        hx-swap="outerHTML">
        <input type="hidden" name="itemId" value="{{ $itemId }}">

        <select name="status"
            class="form-control input-sm"
            onchange="this.closest('form').dispatchEvent(new Event('submit',{bubbles:true}))">
            @foreach($itemStatuses as $value => $langKey)
            <option value="{{ $value }}" {{ $currentStatus === $value ? 'selected' : '' }}>
                {{ __($langKey) }}
            </option>
            @endforeach
        </select>

        @if($needsReason && ($isEmployee ?? false))
        <p class="tw-text-xs tw-text-red-500 tw-mt-xs tw-mb-0">
            {{ __('weeklyplanning.text.reason_required') }}
        </p>
        @endif

        @if($showReason)
        <textarea name="completionReason"
            class="form-control input-sm tw-mt-xs"
            rows="2"
            placeholder="{{ __('weeklyplanning.placeholders.completion_reason') }}"
            required>{{ $item['completionReason'] ?? '' }}</textarea>
        <input type="text"
            name="supportNeeded"
            class="form-control input-sm tw-mt-xs"
            placeholder="{{ __('weeklyplanning.placeholders.support_needed') }}"
            value="{{ $item['supportNeeded'] ?? '' }}">
        <input type="date"
            name="newDueDate"
            class="form-control input-sm tw-mt-xs"
            value="{{ $item['newDueDate'] ?? '' }}">
        <button type="submit" class="btn btn-xs btn-primary tw-mt-xs">
            {{ __('weeklyplanning.buttons.save') }}
        </button>
        @endif
    </form>
</div>
