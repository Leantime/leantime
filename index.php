<?php

// Define the public path
define('PUBLIC_PATH', __DIR__.'/public');

// Check if the requested file exists in public directory
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$publicFile = PUBLIC_PATH.$requestUri;

if (file_exists($publicFile) && is_file($publicFile)) {
    // If it's a static file in public, serve it directly
    $extension = pathinfo($publicFile, PATHINFO_EXTENSION);
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
    ];

    if (isset($mimeTypes[$extension])) {
        header('Content-Type: '.$mimeTypes[$extension]);
    }

    readfile($publicFile);
    exit;
}

// Otherwise, include the main application entry point
require PUBLIC_PATH.'/index.php';
