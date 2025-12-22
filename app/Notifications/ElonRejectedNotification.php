<?php

namespace App\Notifications;

use App\Models\Elon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ElonRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Elon $elon;
    protected string $adminNote;

    /**
     * Create a new notification instance.
     */
    public function __construct(Elon $elon, string $adminNote)
    {
        $this->elon = $elon;
        $this->adminNote = $adminNote;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('E\'lon rad etildi - Ustoz Izla')
            ->greeting('Assalomu alaykum, ' . $notifiable->name . '!')
            ->line('Afsuski, sizning e\'loningiz rad etildi.')
            ->line('E\'lon sarlavhasi: ' . $this->elon->title)
            ->line('Fan: ' . $this->elon->subject)
            ->line('')
            ->line('Rad etilish sababi:')
            ->line($this->adminNote)
            ->line('')
            ->line('Iltimos, e\'lonni qayta ko\'rib chiqing va tuzatishlar kiritib qayta yuboring.')
            ->action('E\'lonni tahrirlash', url('/elon/' . $this->elon->id . '/edit'))
            ->line('Agar savollaringiz bo\'lsa, biz bilan bog\'laning.')
            ->salutation('Hurmat bilan, Ustoz Izla jamoasi');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'elon_rejected',
            'elon_id' => $this->elon->id,
            'elon_title' => $this->elon->title,
            'subject' => $this->elon->subject,
            'admin_note' => $this->adminNote,
            'rejected_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType(object $notifiable): string
    {
        return 'elon_rejected';
    }
}
