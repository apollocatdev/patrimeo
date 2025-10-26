<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ScheduleNotification extends Notification
{
    use Queueable;

    public array $data;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Schedule Notification: ' . $this->data['schedule']->name)
            ->line($this->data['message'])
            ->line('Schedule: ' . $this->data['schedule']->name)
            ->line('Cron: ' . $this->data['schedule']->cron);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->data['message'],
            'schedule_id' => $this->data['schedule']->id,
            'schedule_name' => $this->data['schedule']->name,
            'valuations_count' => $this->data['valuations']->count(),
            'assets_count' => $this->data['assets']->count(),
        ];
    }
}
