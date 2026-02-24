@props([
    'reactions' => [],
    'commentId' => 0,
    'userReactions' => []
])

@php
    $emojiMap = [
        'like' => '👍',
        'love' => '❤️',
        'celebrate' => '🎉',
        'funny' => '😄',
        'interesting' => '🤔',
        'support' => '💯',
        'sad' => '😥',
        'anger' => '😡',
    ];
@endphp

<span class="comment-reactions" id="reactions-{{ $commentId }}" aria-live="polite">
    @if(count($reactions) > 0)
        <span class="reaction-list">
            @foreach($reactions as $reactionData)
                @php
                    $reactionKey = $reactionData['reaction'];
                    $emoji = $emojiMap[$reactionKey] ?? $reactionKey;
                    $isActive = in_array($reactionKey, $userReactions);
                    $userNames = [];
                    if (!empty($reactionData['users'])) {
                        foreach ($reactionData['users'] as $user) {
                            $userNames[] = $user['name'];
                        }
                    }
                    $tooltip = implode(', ', $userNames);
                @endphp
                <button type="button"
                        class="reaction-btn {{ $isActive ? 'active' : '' }}"
                        title="{{ $tooltip }}"
                        hx-post="{{ BASE_URL }}/hx/comments/reactions/toggle?commentId={{ $commentId }}"
                        hx-vals='{"reaction": "{{ $reactionKey }}"}'
                        hx-target="#reactions-{{ $commentId }}"
                        hx-swap="outerHTML">
                    <span class="reaction-emoji">{{ $emoji }}</span>
                    <span class="reaction-count">{{ $reactionData['reactionCount'] }}</span>
                </button>
            @endforeach
        </span>
    @endif

    <span class="reaction-picker-toggle">
        <button type="button" class="add-reaction-btn" onclick="toggleReactionPicker(this, {{ $commentId }})">
            <i class="fa fa-smile-o" aria-hidden="true"></i>
            <span class="sr-only">Add reaction</span>
        </button>
    </span>
</span>
