<?php

namespace App\Notifications;

use App\Models\RiskRegisterAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Poin catatan client: "Prodi tiap semester harus isi risk register
 * tapi ditugaskan oleh SPMI" — notifikasi penugasan ke akun Risk Owner
 * (Kaprodi / unit), tampil di widget Notifikasi dashboard.
 */
class RiskRegisterAssigned extends Notification
{
    use Queueable;

    public function __construct(
        public RiskRegisterAssignment $assignment,
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
        $assignment = $this->assignment;
        $assignment->loadMissing(['academicProgram', 'unit']);

        return [
            'type'          => 'risk_register_assignment',
            'assignment_id' => $assignment->id,
            'academic_year' => $assignment->academic_year,
            'semester'      => $assignment->semester,
            'owner'         => $assignment->owner_label,
            'message'       => 'SPMI menugaskan Anda mengisi Risk Register Semester '
                . $assignment->semester . ' T.A. ' . $assignment->academic_year
                . ' untuk ' . $assignment->owner_label . '.',
            'note'          => $assignment->note,
        ];
    }
}
