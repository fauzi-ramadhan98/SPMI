<?php

namespace App\Notifications;

use App\Models\RiskRegister;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RiskRevisionRequested extends Notification
{
    use Queueable;

    public function __construct(
        public RiskRegister $risk,
        public string $note,
        public string $reviewer,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->payload();
    }

    public function toArray(object $notifiable): array
    {
        return $this->payload();
    }

    private function payload(): array
    {
        return [
            'risk_id'  => $this->risk->id,
            'standar'  => $this->risk->standar_mutu,
            'note'     => $this->note,
            'reviewer' => $this->reviewer,
            'message'  => 'SPMI meminta revisi pada profil risiko: ' . $this->risk->standar_mutu,
        ];
    }
}