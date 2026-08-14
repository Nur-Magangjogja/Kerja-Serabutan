<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

try {
    $count = User::where('role', 'kustomer')->count();
    $total = User::count();
    echo "kustomer_count:" . $count . "\n";
    echo "total_users:" . $total . "\n";
    echo "sample_kustomer_records:\n";
    $samples = User::where('role', 'kustomer')->limit(10)->get(['id', 'email', 'role'])->toArray();
    echo json_encode($samples, JSON_PRETTY_PRINT) . "\n";

    echo "customer_count:" . User::where('role', 'customer')->count() . "\n";
    echo "roles_distribution:\n";
    $dist = DB::table('users')->select('role', DB::raw('count(*) as c'))->groupBy('role')->get();
    echo json_encode($dist, JSON_PRETTY_PRINT) . "\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
