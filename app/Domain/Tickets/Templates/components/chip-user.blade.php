@props([
    'ticketId',
    'editorId' => null,          // current assignee id (0/'' = nobody)
    'editorName' => '',          // current assignee's display name
    'users' => [],               // project users: ['id', 'firstname', 'lastname']
    'collaborators' => [],       // collaborator user ids to preview next to the assignee
    'collaboratorOverflow' => 0, // collaborators beyond the preview
    'float' => false,
    'align' => 'start',
    'showName' => true,          // false: avatar only (kanban cards); the name stays in the DOM for screen readers/updates
    'compact' => false,          // smaller collaborator avatars (kanban cards)
])

{{-- A to-do's assignee chip (avatar + name). Saves `editorId`. --}}
@php
    $hasEditor = $editorId !== null && $editorId !== '' && (string) $editorId !== '0' && $editorName !== '';
    $avatarUrl = fn ($userId) => BASE_URL.'/api/users?profileImage='.$userId;
    $collaboratorSize = $compact ? 18 : 20;
@endphp
<x-global::forms.chip
    {{ $attributes->class(['noBg']) }}
    type="user"
    field="editorId"
    :entity-id="$ticketId"
    :value="$hasEditor ? $editorId : 0"
    :toggle-class="$float ? 'f-left' : ''"
    :header="__('dropdown.choose_user')"
    :align="$align">
    <x-slot:toggle>
        <span class="text" style="display:inline-flex; align-items:center;{{ $showName ? ' gap:6px;' : '' }}">
            <span id="userImage{{ $ticketId }}"><img data-chip-image src="{{ $avatarUrl($hasEditor ? $editorId : 'false') }}" width="25" style="vertical-align: middle;{{ $showName ? ' margin-right:5px;' : '' }}" alt=""/></span><span id="user{{ $ticketId }}" data-chip-label @unless ($showName) class="sr-only" @endunless>{{ $hasEditor ? $editorName : __('dropdown.not_assigned') }}</span>
            @if (! empty($collaborators))
                <span class="ticket-collaborators" style="display:inline-flex; align-items:center; margin-left:{{ $compact ? 6 : 4 }}px;">
                    @foreach ($collaborators as $index => $collaboratorId)
                        <span class="ticket-collaborator-avatar" title="{{ __('label.collaborators') }}" style="display:inline-flex; width:{{ $collaboratorSize }}px; height:{{ $collaboratorSize }}px; border-radius:999px; border:2px solid var(--main-background-color, #fff); overflow:hidden; {{ $index > 0 ? 'margin-left:-8px;' : '' }}"><img src="{{ $avatarUrl((int) $collaboratorId) }}" width="{{ $collaboratorSize }}" height="{{ $collaboratorSize }}" style="display:block; width:{{ $collaboratorSize }}px; height:{{ $collaboratorSize }}px;" alt=""/></span>
                    @endforeach
                    @if ($collaboratorOverflow > 0)
                        <span class="ticket-collaborator-more" title="{{ __('label.collaborators') }}" style="display:inline-flex; align-items:center; justify-content:center; min-width:{{ $collaboratorSize }}px; height:{{ $collaboratorSize }}px; padding:0 {{ $compact ? 4 : 5 }}px; margin-left:4px; border-radius:999px; background:var(--accent-color, #e9ecef); color:var(--secondary-font-color, #333); font-size:{{ $compact ? 10 : 11 }}px; line-height:{{ $collaboratorSize }}px;">+{{ (int) $collaboratorOverflow }}</span>
                    @endif
                </span>
            @endif
        </span>
    </x-slot:toggle>
    <x-global::forms.chip.option value="0" :label="__('label.not_assigned_to_user')" :image="$avatarUrl('false')" id="userStatusChange{{ $ticketId }}0">{{ __('label.not_assigned_to_user') }}</x-global::forms.chip.option>
    @foreach ($users as $user)
        @php $fullName = sprintf(__('text.full_name'), $user['firstname'], $user['lastname']); @endphp
        <x-global::forms.chip.option :value="$user['id']" :label="$fullName" :image="$avatarUrl($user['id'])" id="userStatusChange{{ $ticketId }}{{ $user['id'] }}"><img src="{{ $avatarUrl($user['id']) }}" width="25" style="vertical-align: middle; margin-right:5px;"/>{{ $fullName }}</x-global::forms.chip.option>
    @endforeach
</x-global::forms.chip>
