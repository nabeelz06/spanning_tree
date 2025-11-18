<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpanningTreeController;

Route::get('/', [SpanningTreeController::class, 'index'])->name('spanning-tree.index');
Route::post('/generate-random-graph', [SpanningTreeController::class, 'generateRandomGraph'])->name('spanning-tree.generate-random');
Route::post('/run-bfs-algorithm', [SpanningTreeController::class, 'runBFSAlgorithm'])->name('spanning-tree.run-bfs');
Route::post('/run-dfs-algorithm', [SpanningTreeController::class, 'runDFSAlgorithm'])->name('spanning-tree.run-dfs');