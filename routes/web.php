<?php

use App\Modules\Landing\Presentation\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

foreach (config('tenancy.central_domains') as $domain) {

    Route::domain($domain)->group(function () {
        Route::get('/', [LandingController::class, 'index'])->name('landing');
    });
}

Route::get('lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'ar'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('lang.switch');

