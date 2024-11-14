@props([
    'user' => null
])

<img {{ $attributes->merge([
    'src' => BASE_URL . '/api/users?profileImage=' . $user['id'] .'&v='.format($user['modified'])->timestamp(),
]) }} />
