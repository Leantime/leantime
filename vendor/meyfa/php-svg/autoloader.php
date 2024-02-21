<?php

// PSR-4 autoloader implementation, taken from:
// https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
// which is licensed under MIT.

spl_autoload_register(function ($class) {

    // project-specific namespace prefix
    $prefix = 'SVG\\';

    // base directory for the namespace prefix
    $base_dir = __DIR__ . '/src/';

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative class name
    $relative_class = substr($class, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});
