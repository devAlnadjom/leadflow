<?php

use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadNoteController;
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
    ->middleware('throttle:10,1')
    ->name('widgets.submit');
Route::get('widget-preview', [WidgetController::class, 'preview'])->name('widgets.preview');
Route::post('widget-preview', [WidgetController::class, 'submitPreview'])
    ->middleware('throttle:10,1')
    ->name('widgets.preview.submit');

// Public quote view (no auth required)
Route::get('devis/{uid}', [\App\Http\Controllers\PublicQuoteController::class, 'show'])->name('quotes.public.show');
Route::post('devis/{uid}/respond', [\App\Http\Controllers\PublicQuoteController::class, 'respond'])
    ->middleware('throttle:10,1')
    ->name('quotes.public.respond');

// Public invoice view (no auth required)
Route::get('facture/{uid}', [\App\Http\Controllers\PublicInvoiceController::class, 'show'])->name('invoices.public.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('onboarding/company', [OnboardingController::class, 'create'])->name('onboarding.company.create');
    Route::post('onboarding/company', [OnboardingController::class, 'store'])->name('onboarding.company.store');
});

Route::middleware(['auth', 'verified', 'onboarded'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::resource('leads', LeadController::class);
    Route::patch('leads/{lead}/status', [LeadController::class, 'updateStatus'])->name('leads.update_status');
    Route::post('/leads/convert', [LeadController::class, 'storeConvertedClient'])->name('leads.store_client');
    Route::post('leads/{lead}/convert', [LeadController::class, 'convert'])->name('leads.convert');
    Route::post('leads/{lead}/notes', [LeadNoteController::class, 'store'])->name('leads.notes.store');
    Route::delete('leads/{lead}/notes/{note}', [LeadNoteController::class, 'destroy'])->name('leads.notes.destroy');

    Route::resource('clients', \App\Http\Controllers\ClientController::class);
    Route::post('/clients/{client}/notes', [\App\Http\Controllers\ClientNoteController::class, 'store'])->name('clients.notes.store');
    Route::delete('/clients/{client}/notes/{note}', [\App\Http\Controllers\ClientNoteController::class, 'destroy'])->name('clients.notes.destroy');

    Route::get('quotes', [\App\Http\Controllers\QuoteController::class, 'index'])->name('quotes.index');
    Route::get('leads/{lead}/quotes/create', [\App\Http\Controllers\QuoteController::class, 'create'])->name('quotes.create');
    Route::post('leads/{lead}/quotes', [\App\Http\Controllers\QuoteController::class, 'store'])->name('quotes.store');
    Route::get('quotes/{quote}/edit', [\App\Http\Controllers\QuoteController::class, 'edit'])->name('quotes.edit');
    Route::put('quotes/{quote}', [\App\Http\Controllers\QuoteController::class, 'update'])->name('quotes.update');
    Route::delete('quotes/{quote}', [\App\Http\Controllers\QuoteController::class, 'destroy'])->name('quotes.destroy');
    Route::post('quotes/{quote}/send', [\App\Http\Controllers\QuoteController::class, 'sendEmail'])->name('quotes.send');

    Route::get('widgets', [WidgetController::class, 'index'])->name('widgets.index');
    Route::get('widgets/create', [WidgetController::class, 'create'])->name('widgets.create');
    Route::post('widgets', [WidgetController::class, 'store'])->name('widgets.store');
    Route::get('widgets/{leadForm}', [WidgetController::class, 'show'])->name('widgets.show');
    Route::get('widgets/{leadForm}/edit', [WidgetController::class, 'edit'])->name('widgets.edit');
    Route::patch('widgets/{leadForm}', [WidgetController::class, 'update'])->name('widgets.update');
    // Invoices
    Route::get('invoices', [\App\Http\Controllers\InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('clients/{client}/invoices/create', [\App\Http\Controllers\InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('invoices', [\App\Http\Controllers\InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('invoices/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'show'])->name('invoices.show');
    Route::patch('invoices/{invoice}/status', [\App\Http\Controllers\InvoiceController::class, 'updateStatus'])->name('invoices.update_status');
    Route::delete('invoices/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'destroy'])->name('invoices.destroy');
    Route::post('invoices/{invoice}/send', [\App\Http\Controllers\InvoiceController::class, 'sendEmail'])->name('invoices.send');

    Route::delete('widgets/{leadForm}', [WidgetController::class, 'destroy'])->name('widgets.destroy');
});

require __DIR__.'/settings.php';
