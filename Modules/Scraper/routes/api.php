<?php

use Illuminate\Support\Facades\Route;
use Modules\Scraper\Http\Controllers\DealHtmlController;

Route::prefix('v1')->group(function () { //middleware(['auth:sanctum'])->
    //Route::apiResource('scrapers', ScraperController::class)->names('scraper');
    Route::get('create-table', [DealHtmlController::class, 'createTable'])->name('create-table');
});
