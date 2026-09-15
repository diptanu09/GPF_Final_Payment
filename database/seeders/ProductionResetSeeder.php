<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class ProductionResetSeeder extends Seeder
{
    /**
     * Clear all application data tables and reset auto-increment identities for production live deployment.
     * Preserves VLC master replica tables (vlcs_*) and statutory interest rate slabs.
     */
    public function run(): void
    {
        $driver = DB::getDriverName();

        $tablesToTruncate = [
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
            'users',
        ];

        if ($driver === 'pgsql') {
            foreach ($tablesToTruncate as $table) {
                if (Schema::hasTable($table)) {
                    DB::statement("TRUNCATE TABLE {$table} RESTART IDENTITY CASCADE;");
                }
            }
        } elseif ($driver === 'sqlite') {
            foreach ($tablesToTruncate as $table) {
                if (Schema::hasTable($table)) {
                    DB::statement("DELETE FROM {$table};");
                    DB::statement("DELETE FROM sqlite_sequence WHERE name = '{$table}';");
                }
            }
        } else {
            Schema::disableForeignKeyConstraints();
            foreach ($tablesToTruncate as $table) {
                if (Schema::hasTable($table)) {
                    DB::statement("TRUNCATE TABLE {$table};");
                }
            }
            Schema::enableForeignKeyConstraints();
        }

        // Re-create System Administrator User
        User::create([
            'name' => 'System Administrator',
            'username' => 'admin',
            'email' => 'admin@tripura.gov.in',
            'password' => Hash::make('Passw0rd'),
            'role' => 'admin',
            'designation' => 'System Administrator',
            'section' => 'Directorate Office',
            'phone_number' => '0381-232-0001',
            'approval_status' => 'approved',
            'is_active' => true,
        ]);
    }
}
