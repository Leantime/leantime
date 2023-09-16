<div class="profileBox">
    <div class="commentImage">
        @if (isset($user['id']) || isset($user->id))
            <x-users::profile-image :userId="$user['id'] ?? $user->id" />
        @else
            <i class="fa fa-user-plus"></i>
        @endif
    </div>
    <div class="userName">
        {{ $slot }}
    </div>
</div>
