<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignClaimNotify extends Notification
{
    use Queueable;
    private $claim;
    private $customer;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($claim, $customer)
    {
        $this->claim = $claim;
        $this->customer = $customer;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New claim')
            ->greeting('Hello ' . $this->customer["firstname"] . ' ' . $this->customer["lastname"])
            // ->action('Notification Action', url('/'))
            ->line('Your health claim has been assigned to you.  You can now consult with a medical provider free of charge and get any prescribed medications for free as well.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            "claim" => $this->claim,
            "message" => "Congratulations a claim has been assigned to you.",
        ];
    }
}
