$users = \App\Models\User::all();
echo "Users Count: " . $users->count() . "\n";
foreach ($users as $u) {
    echo "User ID: {$u->id} | Name: {$u->name} | Username: {$u->username} | Role: {$u->role}\n";
}

$tables = [
    'inward_cases',
    'calculation_runs',
    'calculation_monthly_breakdowns',
    'case_nominees',
    'authorities',
    'digital_signatures',
    'workflow_histories',
    'admin_security_tokens',
    'cache',
    'jobs'
];

foreach ($tables as $t) {
    if (\Illuminate\Support\Facades\Schema::hasTable($t)) {
        $cnt = \Illuminate\Support\Facades\DB::table($t)->count();
        echo "Table '{$t}': {$cnt} rows\n";
    }
}
