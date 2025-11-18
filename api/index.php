<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';

    $storagePath = '/tmp/storage';
    if (!is_dir($storagePath)) {
        mkdir($storagePath, 0777, true);
        mkdir($storagePath . '/framework/views', 0777, true);
    }
    $app->useStoragePath($storagePath);

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );
    $response->send();
    $kernel->terminate($request, $response);

} catch (\Throwable $e) {
    http_response_code(500);
    echo "<div style='background:#ffebe8; border:1px solid #cc0000; padding:20px; font-family:monospace;'>";
    echo "<h2 style='color:#cc0000; margin-top:0;'>🔥 Error Detacted</h2>";
    echo "<strong>Message:</strong> " . $e->getMessage() . "<br><br>";
    echo "<strong>File:</strong> " . $e->getFile() . " (Line: " . $e->getLine() . ")";
    echo "<pre style='background:#fff; padding:10px; margin-top:10px; overflow:auto;'>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}