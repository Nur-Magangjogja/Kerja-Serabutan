<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\PartnerActivity;

$mitra = User::where('role', 'mitra')->first();
if (!$mitra) {
    echo "No mitra user found. Create a user with role=mitra first.\n";
    exit(1);
}

$a = PartnerActivity::create([
    'user_id' => $mitra->id,
    'activity_type' => 'login',
    'description' => 'Test login recorded by script',
    'ip_address' => '127.0.0.1',
    'user_agent' => 'script-test',
]);

echo "Created activity id: {$a->id} for user {$mitra->email}\n";