<?php

use App\Http\Controllers\Settings\CompanySettingsController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\TwoFactorAuthenticationController;
use App\Http\Controllers\Settings\SeoSettingsController;
use App\Http\Controllers\Settings\CompanyServiceController;
use App\Http\Controllers\Settings\CompanyGalleryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('settings/company', [CompanySettingsController::class, 'edit'])->name('company-settings.edit');
    Route::patch('settings/company', [CompanySettingsController::class, 'update'])->name('company-settings.update');

    Route::get('settings/seo', [SeoSettingsController::class, 'edit'])->name('seo-settings.edit');
    Route::patch('settings/seo', [SeoSettingsController::class, 'update'])->name('seo-settings.update');

    Route::post('settings/services', [CompanyServiceController::class, 'store'])->name('company-services.store');
    Route::delete('settings/services/{companyService}', [CompanyServiceController::class, 'destroy'])->name('company-services.destroy');

    Route::post('settings/galleries', [CompanyGalleryController::class, 'store'])->name('company-galleries.store');
    Route::delete('settings/galleries/{companyGallery}', [CompanyGalleryController::class, 'destroy'])->name('company-galleries.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('user-password.edit');

    Route::put('settings/password', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::inertia('settings/appearance', 'settings/Appearance')->name('appearance.edit');

    Route::get('settings/two-factor', [TwoFactorAuthenticationController::class, 'show'])
        ->name('two-factor.show');
});
