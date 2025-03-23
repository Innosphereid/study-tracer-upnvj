<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create superadmin user
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@upnvj.ac.id',
            'username' => 'superadmin',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Nugraha Adhitama',
            'email' => 'nugrahaadhitama22@gmail.com',
            'username' => 'Adhi',
            'password' => Hash::make('12345678'),
            'role' => 'superadmin',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Nugraha Adhitama',
            'email' => '2210512109@mahasiswa.upnvj.ac.id',
            'username' => 'Nugraha Adhitama',
            'password' => Hash::make('12345678'),
            'role' => 'superadmin',
            'email_verified_at' => now(),
        ]);

        // Create admin user
        User::create([
            'name' => 'Admin',
            'email' => 'admin@upnvj.ac.id',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }
}