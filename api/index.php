<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Fix Storage & Cache Path for Vercel (Read-Only Filesystem)
|--------------------------------------------------------------------------
*/
$storagePath = '/tmp/storage';

// Daftar folder wajib ada agar Laravel tidak crash
$folders = [
    $storagePath . '/app',
    $storagePath . '/framework/cache',
    $storagePath . '/framework/views',   // PENTING: Untuk Blade View
    $storagePath . '/framework/sessions', // PENTING: Untuk Session
    $storagePath . '/logs',
];

foreach ($folders as $folder) {
    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }
}

// Set path baru ke aplikasi
$app->useStoragePath($storagePath);

/*
|--------------------------------------------------------------------------
| Handle Request
|--------------------------------------------------------------------------
*/
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);