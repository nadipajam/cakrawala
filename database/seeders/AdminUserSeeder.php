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
                'email' => 'admin@gmail.com',
                'name' => 'Admin',
                'phone' => '08123456789',
                'role' => 'admin',
            ],
            [
                'email' => 'staff@gmail.com',
                'name' => 'Staff',
                'phone' => '08123456780',
                'role' => 'staff',
            ],
            [
                'email' => 'manager@gmail.com',
                'name' => 'Manager',
                'phone' => '08123456781',
                'role' => 'manager',
            ],
            [
                'email' => 'customer@gmail.com',
                'name' => 'Customer',
                'phone' => '08123456782',
                'role' => 'customer',
            ],
        ];

        foreach ($accounts as $account) {
            $user = User::query()->where('role', $account['role'])->first();

            if ($user) {
                $user->forceFill([
                    'name' => $account['name'],
                    'email' => $account['email'],
                    'phone' => $account['phone'],
                    'password' => Hash::make('password12345678'),
                    'role' => $account['role'],
                ])->save();

                continue;
            }

            User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'phone' => $account['phone'],
                    'password' => Hash::make('password12345678'),
                    'role' => $account['role'],
                ]
            );
        }
    }
}
