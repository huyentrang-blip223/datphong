<?php
namespace App\Services;

use App\Mail\BusinessNotificationMail;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function send(User|int $user, string $type, string $title, string $message, ?string $referenceType = null, ?int $referenceId = null): Notification
    {
        $userId = $user instanceof User ? $user->id : $user;
        $recipient = $user instanceof User ? $user : User::find($userId);

        $notification = Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
        ]);

        $this->sendMailIfRelevant($recipient, $type, $title, $message, $referenceType, $referenceId);

        return $notification;
    }

    private function sendMailIfRelevant(?User $recipient, string $type, string $title, string $message, ?string $referenceType, ?int $referenceId): void
    {
        if (!config('mail.notifications_enabled') || !$recipient?->email || !$this->shouldEmail($type, $referenceType)) {
            return;
        }

        if (!config('mail.mailers.smtp.username') || !config('mail.mailers.smtp.password')) {
            Log::warning('Skipped notification email because SMTP username/password is not configured.', [
                'notification_type' => $type,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);
            return;
        }

        $body = implode("\n\n", [
            $message,
            'Loại thông báo: ' . $type,
            $referenceType ? ('Tham chiếu: ' . $referenceType . ' #' . $referenceId) : null,
            'Đây là email tự động từ ĐT-07 Homestay Cộng Đồng.',
        ]);

        try {
            Mail::to($recipient->email, $recipient->full_name)
                ->send(new BusinessNotificationMail($title, $body));
        } catch (\Throwable $exception) {
            Log::warning('Failed to send notification email.', [
                'user_id' => $recipient->id,
                'email' => $recipient->email,
                'notification_type' => $type,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function shouldEmail(string $type, ?string $referenceType): bool
    {
        return str_starts_with($type, 'booking')
            || str_contains($type, 'payment')
            || str_starts_with($type, 'product_order')
            || str_starts_with($type, 'experience')
            || in_array($referenceType, ['booking', 'product_order', 'experience_booking'], true);
    }
}
