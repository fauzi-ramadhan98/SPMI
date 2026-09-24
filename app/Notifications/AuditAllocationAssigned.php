<?php

namespace App\Notifications;

use App\Models\AuditAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi alokasi penugasan audit (Poin catatan client):
 * "muncul notifikasi di Ka Prodi dan ke UNIT ... ketika alokasi penugasan muncul,
 *  informasi akan dilakukan Audit berdasarkan AMI".
 *
 * Dikirim ke seluruh akun Ka Prodi (prodi) / Unit kerja yang menjadi auditee,
 * melalui channel database (ditampilkan di widget Notifikasi dashboard).
 */
class AuditAllocationAssigned extends Notification
{
    use Queueable;

    public function __construct(
        public AuditAssignment $assignment,
        public string $action = 'created', // created | cancelled
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
        $auditee = $assignment->auditee_label;
        $cycle = $assignment->cycle;

        $message = $this->action === 'cancelled'
            ? 'Alokasi penugasan audit untuk ' . $auditee . ' telah dibatalkan oleh SPMI.'
            : 'Audit berdasarkan AMI akan dilakukan untuk ' . $auditee . ' oleh auditor ' . $assignment->auditor_name . '.';

        $note = null;
        if ($cycle) {
            $note = 'Siklus ' . $cycle->name
                . ' — ' . $cycle->academic_year . ' Semester ' . $cycle->semester;
            if ($cycle->start_date && $cycle->end_date) {
                $note .= ', jadwal ' . $cycle->start_date->format('d M Y')
                    . ' s.d. ' . $cycle->end_date->format('d M Y');
            }
        }

        return [
            'type'          => 'audit_allocation',
            'action'        => $this->action,
            'assignment_id' => $assignment->id,
            'cycle_id'      => $assignment->audit_cycle_id,
            'auditee'       => $auditee,
            'auditor'       => $assignment->auditor_name,
            'message'       => $message,
            'note'          => $note,
        ];
    }
}
