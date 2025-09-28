<?php
// File: public/libapp/appclasses/autoload.php

spl_autoload_register(function ($class) {
    // Root directory where all class folders live
    $baseDir = __DIR__ . '/';

    // Convert namespace to full file path
    $file = $baseDir . str_replace('\\', '/', $class) . '.php';

    // Require if it exists
    if (file_exists($file)) {
        require_once $file;
    }
});
