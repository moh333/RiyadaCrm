<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Central\User;

class MasterAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Master Admin User
        User::firstOrCreate(
            ['email' => 'admin@riyadacrm.test'],
            [
                'name' => 'Master Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Master Admin created: admin@riyadacrm.test / password');
    }
}
