<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\Integration\OracleMasterBridge;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('TRUNCATE TABLE users RESTART IDENTITY CASCADE;');
        } elseif ($driver === 'sqlite') {
            DB::statement('DELETE FROM users;');
            DB::statement('DELETE FROM sqlite_sequence WHERE name = "users";');
        } elseif ($driver === 'mysql') {
            Schema::disableForeignKeyConstraints();
            DB::statement('TRUNCATE TABLE users;');
            Schema::enableForeignKeyConstraints();
        }

        $usersMap = [];

        // 1. Default bootstrap admin from environment / config
        $defaultAdmin = config('gpf.default_admin');
        if (!empty($defaultAdmin) && !empty($defaultAdmin['username'])) {
            $username = strtolower(trim($defaultAdmin['username']));
            $usersMap[$username] = [
                'name' => $defaultAdmin['name'] ?? 'Administrator',
                'username' => $username,
                'email' => $defaultAdmin['email'] ?? ($username . '@tripura.gov.in'),
                'password' => $defaultAdmin['password'] ?? 'dir',
                'role' => $defaultAdmin['role'] ?? 'admin',
                'designation' => $defaultAdmin['designation'] ?? 'Director',
                'section' => $defaultAdmin['section'] ?? 'Directorate Office',
                'phone_number' => $defaultAdmin['phone_number'] ?? null,
                'approval_status' => 'approved',
                'is_active' => true,
            ];
        }

        // 2. Attempt dynamic import from Oracle gpffp.USER_ACCOUNTS if online
        try {
            /** @var OracleMasterBridge $bridge */
            $bridge = app(OracleMasterBridge::class);
            $conn = $bridge->getConnection();

            if ($conn) {
                $stmt = oci_parse($conn, 'SELECT FULL_NAME, USERNAME, PASSWORD, USER_ROLE, USER_STATUS FROM gpffp.USER_ACCOUNTS ORDER BY USER_ID ASC');
                if (@oci_execute($stmt)) {
                    while ($row = oci_fetch_assoc($stmt)) {
                        $username = strtolower(trim($row['USERNAME'] ?? ''));
                        if (empty($username)) continue;

                        $role = match (trim((string) ($row['USER_ROLE'] ?? ''))) {
                            '1', 'admin', 'director' => 'admin',
                            '2', 'approver', 'srao', 'ao' => 'approver',
                            '3', 'checker', 'aao' => 'checker',
                            '5', 'dispatch', 'outward' => 'dispatch',
                            default => 'deo',
                        };

                        $isActive = strtoupper(trim((string) ($row['USER_STATUS'] ?? 'Y'))) === 'Y';
                        $existing = $usersMap[$username] ?? [];

                        $usersMap[$username] = [
                            'name' => !empty($row['FULL_NAME']) ? trim($row['FULL_NAME']) : ($existing['name'] ?? ucfirst($username)),
                            'username' => $username,
                            'email' => $existing['email'] ?? ($username . '@tripura.gov.in'),
                            'password' => !empty($row['PASSWORD']) ? trim($row['PASSWORD']) : ($existing['password'] ?? 'secret123'),
                            'role' => $role,
                            'designation' => $existing['designation'] ?? null,
                            'section' => $existing['section'] ?? null,
                            'phone_number' => $existing['phone_number'] ?? null,
                            'approval_status' => $isActive ? 'approved' : 'pending',
                            'is_active' => $isActive,
                        ];
                    }
                    oci_free_statement($stmt);
                }
            }
        } catch (\Throwable $e) {
            Log::info("UserSeeder: Oracle master accounts lookup skipped (" . $e->getMessage() . "). Using config defaults.");
        }

        // 2. Insert all users sequentially into the database
        foreach ($usersMap as $userData) {
            $password = $userData['password'] ?? 'secret123';
            // Hash password if not already bcrypt-hashed
            if (!str_starts_with($password, '$2y$') && !str_starts_with($password, '$2a$')) {
                $password = Hash::make($password);
            }

            User::create([
                'name' => $userData['name'],
                'username' => $userData['username'],
                'email' => $userData['email'],
                'password' => $password,
                'role' => $userData['role'] ?? 'deo',
                'designation' => $userData['designation'] ?? null,
                'section' => $userData['section'] ?? null,
                'phone_number' => $userData['phone_number'] ?? null,
                'approval_status' => $userData['approval_status'] ?? 'approved',
                'is_active' => $userData['is_active'] ?? true,
            ]);
        }
    }
}
