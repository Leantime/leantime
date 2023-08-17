<div class="profileBox">
    <div class="commentImage">
        @isset ($user['id'] || $user->id)
            <x-user-profile-image :userId="{!! $user['id'] ?? $user->id !!}" />
        @else
            <i class="fa fa-user-plus"></i>
        @endif
    </div>
    <div class="userName">
        {{ $slot }}
    </div>
</div>
