<?php

namespace App\Notifications;

use App\Models\LeadRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeadReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public LeadRecord $lead;

    /**
     * Create a new notification instance.
     */
    public function __construct(LeadRecord $lead)
    {
        $this->lead = $lead;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (str_contains($notifiable->notification_preference ?? 'immediate', 'immediate')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $leadName = $this->lead->name ?? 'Anonyme';
        $leadEmail = $this->lead->email ?? 'Non renseigné';

        return (new MailMessage)
            ->subject('Nouveau Prospect (Lead) reçu : ' . $leadName)
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line('Vous venez de recevoir un nouveau prospect via votre widget : **' . ($this->lead->leadForm->name ?? 'Widget clientux') . '**.')
            ->line('Nom : ' . $leadName)
            ->line('Email : ' . $leadEmail)
            ->action('Voir le prospect', url('/leads/' . $this->lead->id))
            ->line('Restez réactif et convertissez vite !');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'lead_id' => $this->lead->id,
            'lead_name' => $this->lead->name ?? 'Anonyme',
            'lead_email' => $this->lead->email,
            'form_name' => $this->lead->leadForm->name ?? 'Widget',
            'message' => 'Nouveau prospect reçu : ' . ($this->lead->name ?? 'Anonyme'),
        ];
    }
}