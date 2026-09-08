<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'System Administrator',
                'username' => 'admin',
                'email' => 'admin@agartala.cag.gov.in',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'is_active' => true,
            ],
            [
                'name' => 'Senior Accounts Officer (Sr. AO)',
                'username' => 'srao',
                'email' => 'srao@agartala.cag.gov.in',
                'password' => Hash::make('password123'),
                'role' => 'approver',
                'is_active' => true,
            ],
            [
                'name' => 'Assistant Accounts Officer (AAO)',
                'username' => 'aao',
                'email' => 'aao@agartala.cag.gov.in',
                'password' => Hash::make('password123'),
                'role' => 'checker',
                'is_active' => true,
            ],
            [
                'name' => 'Dealing Assistant',
                'username' => 'da_fund',
                'email' => 'da@agartala.cag.gov.in',
                'password' => Hash::make('password123'),
                'role' => 'dealing_assistant',
                'is_active' => true,
            ],
            [
                'name' => 'Data Entry Operator',
                'username' => 'deo_inward',
                'email' => 'deo@agartala.cag.gov.in',
                'password' => Hash::make('password123'),
                'role' => 'deo',
                'is_active' => true,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['username' => $user['username']],
                $user
            );
        }
    }
}
