{{--
    Rendered by StatusUpdate::get() and StatusUpdate::save().
    Variables: $item (array|null), $itemStatuses (array), $reasonRequiredStatuses (array),
               $isEmployee (bool), $reasonRequired (bool, optional), $selectedStatus (string, optional)
--}}
@php
$itemId = $item['id'] ?? 0;
$currentStatus = $selectedStatus ?? ($item['status'] ?? 'not_started');
$needsReason = isset($reasonRequired) && $reasonRequired;
$showReason = ($isEmployee ?? false)
    && ($needsReason || in_array($currentStatus, $reasonRequiredStatuses ?? [], true));
$chipStyle = match($currentStatus) {
    'completed'     => 'background:rgba(34,197,94,.15); color:#22c55e;',
    'in_progress'   => 'background:rgba(74,158,255,.15); color:#4a9eff;',
    'blocked'       => 'background:rgba(249,115,22,.15); color:#f97316;',
    'not_completed' => 'background:rgba(239,68,68,.15); color:#ef4444;',
    default         => 'background:rgba(150,150,150,.1); color:var(--grey);',
};
@endphp

<div id="status-control-{{ $itemId }}">
    <form hx-post="{{ BASE_URL }}/hx/weekly-planning/statusUpdate/save"
        hx-target="#status-control-{{ $itemId }}"
        hx-swap="outerHTML">
        <input type="hidden" name="itemId" value="{{ $itemId }}">

        <select name="status"
            class="form-control input-sm"
            style="border-radius:20px; font-size:11px; font-weight:600; padding:2px 8px; height:auto; {{ $chipStyle }}"
            onchange="this.closest('form').dispatchEvent(new Event('submit',{bubbles:true}))">
            @foreach($itemStatuses as $value => $langKey)
            <option value="{{ $value }}" {{ $currentStatus === $value ? 'selected' : '' }}>
                {{ __($langKey) }}
            </option>
            @endforeach
        </select>

        @if($needsReason && ($isEmployee ?? false))
        <p style="font-size:11px; color:#ef4444; margin-top:5px; margin-bottom:0;">
            {{ __('weeklyplanning.text.reason_required') }}
        </p>
        @endif

        @if($showReason)
        <div style="margin-top:8px; display:flex; flex-direction:column; gap:6px;">
            <textarea name="completionReason"
                class="form-control input-sm"
                rows="2"
                placeholder="{{ __('weeklyplanning.placeholders.completion_reason') }}"
                required>{{ $item['completionReason'] ?? '' }}</textarea>
            <input type="text"
                name="supportNeeded"
                class="form-control input-sm"
                placeholder="{{ __('weeklyplanning.placeholders.support_needed') }}"
                value="{{ $item['supportNeeded'] ?? '' }}">
            <input type="date"
                name="newDueDate"
                class="form-control input-sm"
                value="{{ $item['newDueDate'] ?? '' }}">
            <button type="submit" class="btn btn-xs btn-primary">
                {{ __('weeklyplanning.buttons.save') }}
            </button>
        </div>
        @endif
    </form>
</div>
