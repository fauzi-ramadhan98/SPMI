<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $c = App\Models\AuditCycle::orderBy('id', 'desc')->first();
    if (!$c) {
        echo "No Audit Cycle found.";
        exit;
    }
    $a = App\Models\AuditAssignment::where('audit_cycle_id', $c->id)->get();
    
    $pdf = Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.ami_pdf', ['cycle' => $c, 'assignments' => $a]);
    file_put_contents('test.pdf', $pdf->output());
    
    echo "SUCCESS: " . filesize('test.pdf') . " bytes.";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
