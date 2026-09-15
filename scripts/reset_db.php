$tables = [
    'workflow_histories',
    'digital_signatures',
    'authorities',
    'case_nominees',
    'calculation_monthly_breakdowns',
    'calculation_runs',
    'inward_cases',
    'admin_security_tokens',
    'cache',
    'cache_locks',
    'jobs',
    'job_batches',
    'failed_jobs',
    'users'
];

foreach ($tables as $table) {
    if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
        \Illuminate\Support\Facades\DB::statement("TRUNCATE TABLE {$table} RESTART IDENTITY CASCADE;");
        echo "Truncated table and restarted identity sequence for: {$table}\n";
    }
}

$admin = \App\Models\User::create([
    'name' => 'System Administrator',
    'username' => 'admin',
    'email' => 'admin@tripura.gov.in',
    'password' => \Illuminate\Support\Facades\Hash::make('Passw0rd'),
    'role' => 'admin',
    'designation' => 'System Administrator',
    'section' => 'Directorate Office',
    'phone_number' => '0381-232-0001',
    'approval_status' => 'approved',
    'is_active' => true,
]);

echo "System Administrator account re-created successfully (ID: {$admin->id}, Username: {$admin->username})\n";
