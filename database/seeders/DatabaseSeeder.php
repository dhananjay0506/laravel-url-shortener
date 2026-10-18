<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'System SuperAdmin',
                'password' => Hash::make('password123'),
                'company_id' => null,
                'role' => 'SuperAdmin',
            ]
        );
    }
}