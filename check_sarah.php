<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = App\Models\User::get(['id', 'name', 'email']);
$roles = [];
foreach($users as $u) {
    $roles[$u->id] = $u->getRoleNames()->all();
}
$assignments = App\Models\AuditAssignment::get(['id', 'auditor_id', 'auditor_name', 'status']);
file_put_contents('out.json', json_encode([
    'users' => $users->map(fn($u) => ['id'=>$u->id,'name'=>$u->name,'email'=>$u->email,'roles'=>$roles[$u->id]])->toArray(),
    'assignments' => $assignments->toArray()
], JSON_PRETTY_PRINT));
