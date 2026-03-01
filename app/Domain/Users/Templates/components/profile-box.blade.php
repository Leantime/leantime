@props([
    'user' => null
])

<div class="profileBox">
    <div class="commentImage">
        @if (isset($user['userId']) || isset($user->userId))
            <x-users::profile-image :user="$user" />
        @else
            <x-global::elements.icon name="person" />
        @endif
    </div>
    <div class="userName">
        {{ $slot }}
    </div>
</div>
