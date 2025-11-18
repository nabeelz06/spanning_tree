<?php

/*
|--------------------------------------------------------------------------
| Vercel Entry Point for Laravel
|--------------------------------------------------------------------------
|
| Script ini menjembatani Vercel (Serverless) dengan Laravel.
| Kita memindahkan storage ke /tmp karena Vercel bersifat Read-Only.
|
*/

require __DIR__ . '/../vendor/autoload.php';

// 1. Bootstrap Aplikasi
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 2. Konfigurasi Storage Khusus Vercel (/tmp)
$storagePath = '/tmp/storage';
$app->useStoragePath($storagePath);

// 3. Buat Struktur Folder Wajib secara Manual
//    Tanpa folder ini, Laravel akan crash saat mencoba render View/Session
$folders = [
    $storagePath . '/app',
    $storagePath . '/framework/cache/data',
    $storagePath . '/framework/views',
    $storagePath . '/framework/sessions',
    $storagePath . '/logs',
];

foreach ($folders as $folder) {
    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }
}

// 4. Jalankan Aplikasi
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);