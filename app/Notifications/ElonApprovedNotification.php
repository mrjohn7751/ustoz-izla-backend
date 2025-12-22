<?php

namespace App\Notifications;

use App\Models\Elon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ElonApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Elon $elon;
    protected ?string $adminNote;

    /**
     * Create a new notification instance.
     */
    public function __construct(Elon $elon, ?string $adminNote = null)
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
            ->subject('E\'lon tasdiqlandi - Ustoz Izla')
            ->greeting('Assalomu alaykum, ' . $notifiable->name . '!')
            ->line('Sizning e\'loningiz muvaffaqiyatli tasdiqlandi.')
            ->line('E\'lon sarlavhasi: ' . $this->elon->title)
            ->line('Fan: ' . $this->elon->subject)
            ->line('Narx: ' . number_format($this->elon->price, 0, ',', ' ') . ' so\'m')
            ->when($this->adminNote, function ($mail) {
                return $mail->line('Admin izohi: ' . $this->adminNote);
            })
            ->action('E\'lonni ko\'rish', url('/elon/' . $this->elon->id))
            ->line('Endi e\'loningiz barcha foydalanuvchilar uchun ko\'rinadi.')
            ->line('Muvaffaqiyatlar tilaymiz!')
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
            'type' => 'elon_approved',
            'elon_id' => $this->elon->id,
            'elon_title' => $this->elon->title,
            'subject' => $this->elon->subject,
            'price' => $this->elon->price,
            'admin_note' => $this->adminNote,
            'approved_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType(object $notifiable): string
    {
        return 'elon_approved';
    }
}
