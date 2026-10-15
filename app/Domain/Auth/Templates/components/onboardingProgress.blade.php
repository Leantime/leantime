@props([
    'current' => '',
])

@php
    $onboardingSteps = ['account' => 'Account', 'theme' => 'Theme', 'personalization' => 'Personalization', 'time' => 'Routine'];
    // Past the last step when the flow is finished (current = '').
    $currentPosition = $current === '' ? count($onboardingSteps) + 1 : array_search($current, array_keys($onboardingSteps), true) + 1;
@endphp

<x-global::navigation.steps :steps="array_values($onboardingSteps)" :current="$currentPosition" />
