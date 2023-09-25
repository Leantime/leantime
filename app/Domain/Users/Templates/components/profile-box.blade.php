@props([
    'user' => null
])

<div class="profileBox">
    <div class="commentImage">
        @if (isset($user['userId']) || isset($user->userId))
            <x-users::profile-image :userId="$user['userId'] ?? $user->userId" />
        @else
            <i class="fa fa-user"></i>
        @endif
    </div>
    <div class="userName">
        {{ $slot }}
    </div>
</div>
