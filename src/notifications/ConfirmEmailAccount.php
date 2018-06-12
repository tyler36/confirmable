<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Class ConfirmEmailAccount
 *
 *
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ConfirmEmailAccount extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var string
     */
    public $url;

    /**
     * Create a new notification instance.
     *
     * @param mixed $token
     * @param mixed $confirmation
     */
    public function __construct($confirmation)
    {
        $this->confirmation = $confirmation;
        $this->url          = route('confirm.update', ['token' => $confirmation->token]);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->markdown('mail.confirmation', ['url' => $this->url]);
    }
}
