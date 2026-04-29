<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            [
                'email' => 'admin@cakrawala.com',
                'name' => 'Administrator',
                'phone' => '08123456789',
                'role' => 'admin',
            ],
            [
                'email' => 'staff@cakrawala.com',
                'name' => 'Operations Staff',
                'phone' => '08123456780',
                'role' => 'staff',
            ],
            [
                'email' => 'manager@cakrawala.com',
                'name' => 'Operations Manager',
                'phone' => '08123456781',
                'role' => 'manager',
            ],
        ];

        foreach ($accounts as $account) {
            User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'phone' => $account['phone'],
                    'password' => Hash::make('password'),
                    'role' => $account['role'],
                ]
            );
        }
    }
}
