<?php

use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadNoteController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\WidgetController;
use App\Http\Controllers\WidgetTemplateController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::post('locale', LocaleController::class)->name('locale.update');

// Widget public endpoints — CORS open to all origins (embeddable widget)
// OPTIONS preflight responses are handled automatically by Laravel's CORS middleware
// using the paths defined in config/cors.php
Route::get('widget.js', [WidgetController::class, 'script'])->name('widgets.script');
Route::get('widget-config', [WidgetController::class, 'config'])->name('widgets.config');
Route::post('widget-submit', [WidgetController::class, 'submit'])
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->middleware('throttle:30,1')
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

Route::middleware(['auth', 'verified', 'user.active'])->group(function () {
    Route::get('onboarding/company', [OnboardingController::class, 'create'])->name('onboarding.company.create');
    Route::post('onboarding/company', [OnboardingController::class, 'store'])->name('onboarding.company.store');
});

Route::middleware(['auth', 'verified', 'user.active', 'onboarded'])->group(function () {
    Route::get('dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('leads', LeadController::class);
    Route::patch('leads/{lead}/status', [LeadController::class, 'updateStatus'])->name('leads.update_status');
    Route::post('/leads/convert', [LeadController::class, 'storeConvertedClient'])->name('leads.store_client');
    Route::post('leads/{lead}/convert', [LeadController::class, 'convert'])->name('leads.convert');
    Route::post('leads/{lead}/notes', [LeadNoteController::class, 'store'])->name('leads.notes.store');
    Route::delete('leads/{lead}/notes/{note}', [LeadNoteController::class, 'destroy'])->name('leads.notes.destroy');

    Route::post('tasks', [\App\Http\Controllers\TaskController::class, 'store'])->name('tasks.store');
    Route::patch('tasks/{task}', [\App\Http\Controllers\TaskController::class, 'update'])->name('tasks.update');
    Route::delete('tasks/{task}', [\App\Http\Controllers\TaskController::class, 'destroy'])->name('tasks.destroy');

    Route::get('tags', [\App\Http\Controllers\TagController::class, 'index'])->name('tags.index');
    Route::post('tags', [\App\Http\Controllers\TagController::class, 'store'])->name('tags.store');
    Route::post('tags/sync', [\App\Http\Controllers\TagController::class, 'sync'])->name('tags.sync');

    Route::post('notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::post('notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');

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

    // Widget Templates
    Route::get('widget-templates', [WidgetTemplateController::class, 'index'])->name('widget-templates.index');
    Route::post('widget-templates', [WidgetTemplateController::class, 'store'])->name('widget-templates.store');
    Route::delete('widget-templates/{widgetTemplate}', [WidgetTemplateController::class, 'destroy'])->name('widget-templates.destroy');
    // Invoices
    Route::get('invoices', [\App\Http\Controllers\InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('clients/{client}/invoices/create', [\App\Http\Controllers\InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('invoices', [\App\Http\Controllers\InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('invoices/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('invoices/{invoice}/edit', [\App\Http\Controllers\InvoiceController::class, 'edit'])->name('invoices.edit');
    Route::put('invoices/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'update'])->name('invoices.update');
    Route::patch('invoices/{invoice}/status', [\App\Http\Controllers\InvoiceController::class, 'updateStatus'])->name('invoices.update_status');
    Route::delete('invoices/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'destroy'])->name('invoices.destroy');
    Route::post('invoices/{invoice}/send', [\App\Http\Controllers\InvoiceController::class, 'sendEmail'])->name('invoices.send');

    Route::delete('widgets/{leadForm}', [WidgetController::class, 'destroy'])->name('widgets.destroy');
});

require __DIR__.'/settings.php';

// ─── Super Admin Panel ─────────────────────────────────────────────────────────
Route::prefix('admin')
    ->middleware(['auth', 'verified', 'super.admin'])
    ->name('admin.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('dashboard');

        // Companies
        Route::get('companies',         [\App\Http\Controllers\Admin\AdminCompanyController::class, 'index'])->name('companies.index');
        Route::get('companies/create',  [\App\Http\Controllers\Admin\AdminCompanyController::class, 'create'])->name('companies.create');
        Route::post('companies',        [\App\Http\Controllers\Admin\AdminCompanyController::class, 'store'])->name('companies.store');
        Route::get('companies/{company}',       [\App\Http\Controllers\Admin\AdminCompanyController::class, 'show'])->name('companies.show');
        Route::get('companies/{company}/edit',  [\App\Http\Controllers\Admin\AdminCompanyController::class, 'edit'])->name('companies.edit');
        Route::put('companies/{company}',       [\App\Http\Controllers\Admin\AdminCompanyController::class, 'update'])->name('companies.update');
        Route::patch('companies/{company}/toggle', [\App\Http\Controllers\Admin\AdminCompanyController::class, 'toggleActive'])->name('companies.toggle');

        // Users
        Route::get('users',          [\App\Http\Controllers\Admin\AdminUserController::class, 'index'])->name('users.index');
        Route::get('users/create',   [\App\Http\Controllers\Admin\AdminUserController::class, 'create'])->name('users.create');
        Route::post('users',         [\App\Http\Controllers\Admin\AdminUserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit',   [\App\Http\Controllers\Admin\AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}',        [\App\Http\Controllers\Admin\AdminUserController::class, 'update'])->name('users.update');
        Route::patch('users/{user}/toggle', [\App\Http\Controllers\Admin\AdminUserController::class, 'toggleActive'])->name('users.toggle');
        Route::post('users/{user}/reset-password', [\App\Http\Controllers\Admin\AdminUserController::class, 'resetPassword'])->name('users.reset-password');

        // Ghost Mode (Impersonation)
        Route::post('impersonate/{user}',  [\App\Http\Controllers\Admin\AdminController::class, 'impersonate'])->name('impersonate');
        Route::post('stop-impersonating',  [\App\Http\Controllers\Admin\AdminController::class, 'stopImpersonating'])->name('stop-impersonating');
    });
