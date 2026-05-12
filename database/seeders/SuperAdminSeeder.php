<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'dhruvideas@gmail.com'],
            [
                'name' => 'Dhruv (Super Admin)',
                'email' => 'dhruvideas@gmail.com',
                'role' => 'super_admin',
                'organization' => 'InviteFlow Platform',
                'password' => bcrypt(Str::random(32)),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
    }
}
