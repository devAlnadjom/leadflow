<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 |--------------------------------------------------------------------------
 | clientux Scheduled Tasks
 |--------------------------------------------------------------------------
 |
 | A single cron entry on the server is enough:
 |   * * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
 |
 | No persistent queue worker needed — the scheduler starts the queue worker
 | every 5 minutes and the --stop-when-empty flag makes it exit automatically
 | once all pending jobs are processed.
 |
 */

// Process all pending queue jobs (notifications, emails, etc.)
// Runs every 5 minutes, exits automatically when queue is empty.
Schedule::command('queue:work', [
    '--stop-when-empty',
    '--tries=3',
    '--timeout=60',
    '--max-time=240', // Force-exit after 4 min max (safety net before next cron)
])->everyFiveMinutes()
    ->withoutOverlapping(5) // Prevents a 2nd instance if the first one is still running
    ->runInBackground(); // Don't block the scheduler while it runs

// Send batched lead summary emails to users with "hourly" notification preference
Schedule::command('clientux:send-batched-notifications')
    ->hourly()
    ->withoutOverlapping();

// Prune old failed jobs (keep the table clean, keep 30 days of history)
Schedule::command('queue:prune-failed', ['--hours=720'])
    ->weekly();

// Prune old notifications older than 30 days
Schedule::call(function () {
    \App\Models\User::all()->each(function ($user) {
            $user->notifications()->where('created_at', '<', now()->subDays(30))->delete();
        }
        );
    })->weekly()->name('prune-old-notifications')->withoutOverlapping();