@props([
    'user' => null
])

<x-global::elements.card compact class="profileBox">
    <div class="commentImage">
        @if (isset($user['userId']) || isset($user->userId))
            <x-users::profile-image :user="$user" />
        @else
            <i class="fa fa-user"></i>
        @endif
    </div>
    <div class="userName">
        {{ $slot }}
    </div>
</x-global::elements.card>
