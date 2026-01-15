<?php

namespace Database\Seeders;

use App\Models\Tenant\VtigerUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        VtigerUser::updateOrCreate(
            ['user_name' => 'admin'],
            [
                'user_password' => Hash::make('password'),
                'first_name' => 'Tenant',
                'last_name' => 'Admin',
                'email1' => 'admin@tenant.com',
                'status' => 'Active',
                'is_admin' => 'on',
                'date_entered' => now(),
            ]
        );
    }
}
