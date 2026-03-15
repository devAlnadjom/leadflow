<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Mail\BatchedLeadSummary;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendBatchedLeadNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clientux:send-batched-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends email batch summaries to users for leads received in the last hour.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get users with hourly preference
        $users = User::where('notification_preference', 'hourly')->get();

        foreach ($users as $user) {
            // Find unread database notifications for this user from the last hour
            // Make sure we only grab lead notifications
            $recentNotifications = $user->unreadNotifications()
                ->where('type', 'App\Notifications\LeadReceivedNotification')
                ->where('created_at', '>=', Carbon::now()->subHour())
                ->get();

            if ($recentNotifications->isNotEmpty()) {
                Mail::to($user->email)->queue(new BatchedLeadSummary($user, $recentNotifications->count()));
            }
        }

        $this->info('Batched notifications sent successfully.');
    }
}