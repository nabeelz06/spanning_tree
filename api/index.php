<?php

// 1. Load Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// 2. Bootstrap Laravel App
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 3. [FIX UTAMA] Pindahkan Storage path ke /tmp karena Vercel read-only
$storagePath = '/tmp/storage';
if (!is_dir($storagePath)) {
    mkdir($storagePath, 0777, true);
}
$app->useStoragePath($storagePath);

// 4. Handle Request (Standard Laravel)
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);