<?php

$projectRootDirectory = dirname(__FILE__);

$project_files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($projectRootDirectory)
);

// The path to the 'app' directory.
$app_path = $projectRootDirectory.'/app';

$dispatched_events = [];
$listeners = [];

foreach ($project_files as $file) {
    if (! $file->isFile()) {
        continue;
    }

    $relative_path = str_replace([$app_path, '.php'], ['', ''], $file->getRealPath());
    $context = 'leantime'.strtolower(str_replace('/', '.', $relative_path));

    $source_code = file_get_contents($file->getRealPath());

    if (preg_match_all('/dispatch_(event|filter)\(\s*[\'"](.+?)[\'"]\s*[),]/', $source_code, $matches)) {
        $events = array_map(function ($eventName) use ($context) {
            return $context.'.'.strtolower($eventName);
        }, $matches[2]);

        $dispatched_events = array_merge($dispatched_events, $events);
    }

    if (preg_match_all('/add_(event|filter)_listener\(\s*[\'"](.+?)[\'"]\s*[),]/', $source_code, $matches)) {
        $listeners = array_merge($listeners, array_map('strtolower', $matches[2]));
    }
}

$valid_listeners = [];

foreach ($listeners as $listener) {
    $listener_parts = explode('*', strtolower($listener));
    foreach ($dispatched_events as $event) {
        if (strpos(strtolower($event), $listener_parts[0]) === 0) {
            $valid_listeners[] = $listener;
            break;
        }
    }
}

$invalid_listeners = array_diff($listeners, $valid_listeners);

foreach ($invalid_listeners as $invalid_listener) {
    echo "Invalid listener found for event '$invalid_listener'\n";
}
