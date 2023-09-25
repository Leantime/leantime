@props([
    'userId' => null
])

<img {{ $attributes->merge([
    'src' => BASE_URL . '/api/users?profileImage=' . $userId,
]) }} />
