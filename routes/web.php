<?php

use App\Http\Controllers\LeadController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\WidgetController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::post('locale', LocaleController::class)->name('locale.update');
Route::get('widget.js', [WidgetController::class, 'script'])->name('widgets.script');
Route::get('widget-config', [WidgetController::class, 'config'])->name('widgets.config');
Route::post('widget-submit', [WidgetController::class, 'submit'])
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->name('widgets.submit');
Route::get('widget-preview', [WidgetController::class, 'preview'])->name('widgets.preview');
Route::post('widget-preview', [WidgetController::class, 'submitPreview'])->name('widgets.preview.submit');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('onboarding/company', [OnboardingController::class, 'create'])->name('onboarding.company.create');
    Route::post('onboarding/company', [OnboardingController::class, 'store'])->name('onboarding.company.store');
});

Route::middleware(['auth', 'verified', 'onboarded'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::resource('leads', LeadController::class);
    Route::get('widgets', [WidgetController::class, 'index'])->name('widgets.index');
    Route::get('widgets/create', [WidgetController::class, 'create'])->name('widgets.create');
    Route::post('widgets', [WidgetController::class, 'store'])->name('widgets.store');
    Route::get('widgets/{leadForm}', [WidgetController::class, 'show'])->name('widgets.show');
    Route::get('widgets/{leadForm}/edit', [WidgetController::class, 'edit'])->name('widgets.edit');
    Route::patch('widgets/{leadForm}', [WidgetController::class, 'update'])->name('widgets.update');
    Route::delete('widgets/{leadForm}', [WidgetController::class, 'destroy'])->name('widgets.destroy');
});

require __DIR__.'/settings.php';
