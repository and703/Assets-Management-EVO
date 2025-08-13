<?php
spl_autoload_register(function($class){
    $prefixes = [
        'Core\\'        => __DIR__.'/../core/',
        'Middleware\\'  => __DIR__.'/../middleware/',
        'Controllers\\' => __DIR__.'/../controllers/',
        'Models\\'      => __DIR__.'/../models/',
    ];
    foreach ($prefixes as $p => $dir) {
        if (strpos($class, $p) === 0) {
            $rel = substr($class, strlen($p));
            $path = $dir . str_replace('\\','/',$rel) . '.php';
            if (is_file($path)) { require $path; return; }
        }
    }
});

require __DIR__.'/config.php';
require __DIR__.'/db.php';
