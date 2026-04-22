<?php

use Illuminate\Support\Facades\Route;
use Modules\ShowDocManager\Http\Controllers\ProjectController;
use Modules\ShowDocManager\Http\Controllers\DirectoryController;
use Modules\ShowDocManager\Http\Controllers\PageController;

Route::prefix('v1')->group(function () {//middleware(['auth:sanctum'])->
    //Route::apiResource('projects', ProjectController::class)->names('showdocmanager');
});

Route::prefix('showdoc')->group(function () {

    // 项目管理
    Route::apiResource('projects', ProjectController::class);
    Route::post('projects/{id}/sync', [ProjectController::class, 'sync']);

    // 目录管理
    Route::get('projects/{projectId}/directories/tree', [DirectoryController::class, 'tree']);
    Route::get('projects/{projectId}/directories/{catId}/pages', [DirectoryController::class, 'pages']);
    Route::post('projects/{projectId}/directories', [DirectoryController::class, 'store']);

    // 页面管理
    Route::get('projects/{projectId}/pages', [PageController::class, 'index']);
    Route::get('projects/{projectId}/pages/{pageId}', [PageController::class, 'show']);
    Route::post('projects/{projectId}/pages/{pageId}/sync', [PageController::class, 'sync']);
    Route::post('projects/{projectId}/pages/sync-all', [PageController::class, 'syncAll']);

});
