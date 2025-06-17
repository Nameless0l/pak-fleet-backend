<?php

namespace App\Notifications;

use App\Models\MaintenanceOperation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OperationValidated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $operation;

    /**
     * Create a new notification instance.
     */
    public function __construct(MaintenanceOperation $operation)
    {
        $this->operation = $operation;
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
        $status = $this->operation->status === 'validated' ? 'validée' : 'rejetée';
        $subject = "Opération de maintenance {$status}";

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Bonjour ' . $notifiable->name)
            ->line("Votre opération de maintenance a été {$status}.")
            ->line("Véhicule: {$this->operation->vehicle->plate_number}")
            ->line("Type de maintenance: {$this->operation->maintenanceType->name}")
            ->when($this->operation->validation_comment, function ($mail) {
                return $mail->line("Commentaire: {$this->operation->validation_comment}");
            })
            ->line('Merci d\'utiliser notre application de gestion de flotte.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'operation_id' => $this->operation->id,
            'status' => $this->operation->status,
            'vehicle_plate' => $this->operation->vehicle->plate_number,
            'maintenance_type' => $this->operation->maintenanceType->name,
            'validation_comment' => $this->operation->validation_comment,
            'validated_by' => $this->operation->validator->name ?? null,
            'validated_at' => $this->operation->validated_at,
        ];
    }
}
