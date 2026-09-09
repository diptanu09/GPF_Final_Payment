<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Integration\OracleMasterBridge;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class SyncUserAccountsCommand extends Command
{
    protected $signature = 'gpf:sync-users {--force : Overwrite existing passwords with fresh Bcrypt hashes from user_accounts}';
    protected $description = 'Synchronize authentic institutional accounts from USER_ACCOUNTS into the PostgreSQL users table';

    public function handle(OracleMasterBridge $bridge): int
    {
        $this->info('Starting synchronization of institutional user accounts...');

        $syncedCount = 0;
        $accounts = collect();

        // 1. Try fetching from PostgreSQL user_accounts replica table
        if (Schema::hasTable('user_accounts')) {
            $accounts = DB::table('user_accounts')->get();
            $this->info("Found {$accounts->count()} accounts in local user_accounts table.");
        }

        // 2. If empty and Oracle is online, fetch from live Oracle gpffp.USER_ACCOUNTS
        if ($accounts->isEmpty()) {
            $conn = $bridge->getConnection();
            if ($conn) {
                $stmt = oci_parse($conn, 'SELECT * FROM gpffp.USER_ACCOUNTS ORDER BY USERNAME');
                if (@oci_execute($stmt)) {
                    $rows = [];
                    while ($row = oci_fetch_assoc($stmt)) {
                        $rows[] = (object) [
                            'username' => trim($row['USERNAME']),
                            'full_name' => trim($row['FULL_NAME'] ?? $row['USERNAME']),
                            'password' => trim($row['PASSWORD'] ?? ''),
                            'user_status' => trim($row['USER_STATUS'] ?? 'Y'),
                            'user_role' => (int) ($row['USER_ROLE'] ?? 1),
                        ];
                    }
                    oci_free_statement($stmt);
                    $accounts = collect($rows);
                    $this->info("Fetched {$accounts->count()} accounts directly from Oracle 11g gpffp.USER_ACCOUNTS.");
                }
            }
        }

        if ($accounts->isEmpty()) {
            $this->warn('No accounts found in user_accounts table or Oracle.');
            return Command::FAILURE;
        }

        foreach ($accounts as $acc) {
            $username = strtolower(trim($acc->username));
            if (empty($username)) continue;

            $role = match ((int) ($acc->user_role ?? 1)) {
                1 => 'admin',
                2 => 'deo',
                3 => 'checker',
                4 => 'approver',
                5 => 'dispatch',
                default => 'deo',
            };

            $isActive = strtoupper(trim($acc->user_status ?? 'Y')) === 'Y';
            $plainPass = trim($acc->password ?? '');

            $existingUser = User::where('username', $username)->first();

            if ($existingUser) {
                $existingUser->name = trim($acc->full_name) ?: ucfirst($username);
                $existingUser->role = $role;
                $existingUser->is_active = $isActive;

                if ($this->option('force') || !Hash::check($plainPass, $existingUser->password)) {
                    if (!empty($plainPass)) {
                        $existingUser->password = Hash::make($plainPass);
                    }
                }

                $existingUser->save();
            } else {
                User::create([
                    'username' => $username,
                    'name' => trim($acc->full_name) ?: ucfirst($username),
                    'email' => $username . '@tripura.gov.in',
                    'role' => $role,
                    'password' => Hash::make(!empty($plainPass) ? $plainPass : 'password123'),
                    'is_active' => $isActive,
                ]);
            }

            $syncedCount++;
            $statusStr = $isActive ? '<fg=green>ACTIVE</>' : '<fg=red>INACTIVE</>';
            $this->line("Synced: <fg=cyan>{$username}</> ({$role}) - {$statusStr}");
        }

        $this->info("Successfully synchronized {$syncedCount} institutional accounts.");
        return Command::SUCCESS;
    }
}
