<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
