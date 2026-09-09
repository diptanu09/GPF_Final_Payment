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
                'name' => 'Administrator',
                'username' => 'dir',
                'email' => 'dir@tripura.gov.in',
                'password' => Hash::make('dir'),
                'role' => 'admin',
                'is_active' => true,
            ],
            [
                'name' => 'Jhuntu Das Gupta',
                'username' => 'jdg',
                'email' => 'jdg@tripura.gov.in',
                'password' => Hash::make('Juhi1234@'),
                'role' => 'admin',
                'is_active' => true,
            ],
            [
                'name' => 'Rajkumar Debbarma (Sr. AO)',
                'username' => 'rkdb',
                'email' => 'rkdb@tripura.gov.in',
                'password' => Hash::make('rbsr123'),
                'role' => 'approver',
                'is_active' => true,
            ],
            [
                'name' => 'Anjana Das (AAO)',
                'username' => 'anjana',
                'email' => 'anjana@tripura.gov.in',
                'password' => Hash::make('ad123'),
                'role' => 'checker',
                'is_active' => true,
            ],
            [
                'name' => 'Deeksha Awasthi',
                'username' => 'deeksha',
                'email' => 'deeksha@tripura.gov.in',
                'password' => Hash::make('deeksha@123'),
                'role' => 'deo',
                'is_active' => true,
            ],
            [
                'name' => 'Kalipada Paul',
                'username' => 'kalipada',
                'email' => 'kalipada@tripura.gov.in',
                'password' => Hash::make('Lp123'),
                'role' => 'deo',
                'is_active' => true,
            ],
            [
                'name' => 'Tapas Kanti Roy (Supervisor)',
                'username' => 'tapasaao',
                'email' => 'tapasaao@tripura.gov.in',
                'password' => Hash::make('tapasaao'),
                'role' => 'checker',
                'is_active' => true,
            ],
            [
                'name' => 'Deepak Kumar (Dispatch)',
                'username' => 'deepak',
                'email' => 'deepak@tripura.gov.in',
                'password' => Hash::make('dk123'),
                'role' => 'dispatch',
                'is_active' => true,
            ],
            [
                'name' => 'Accountant General',
                'username' => 'ag',
                'email' => 'ag@tripura.gov.in',
                'password' => Hash::make('ag123'),
                'role' => 'admin',
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
