<?php

use Illuminate\Support\Facades\Route;
use Modules\Scraper\Http\Controllers\ScraperController;

Route::middleware(['auth', 'verified'])->group(function () {
    //Route::resource('scrapers', ScraperController::class)->names('scraper');
});
