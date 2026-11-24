@php
    // Where the import is, from the provider's own steps. The result page marks every step done.
    $steps = array_values($provider->steps);
    $currentStep = $currentStep ?? ($_GET['step'] ?? 'connect');
    $index = array_search($currentStep, $steps, true);
    $position = $index === false ? 1 : $index + 1;
    if (! empty($allStepsDone)) {
        $position = count($steps) + 1;
    }
@endphp
<x-global::navigation.steps
    class="lt-import-steps"
    :steps="array_map(fn (string $step) => __($provider->stepDetails[$step]['title'] ?? $step), $steps)"
    :current="$position"
    :label="__('label.import_progress')" />
