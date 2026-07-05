<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            ['name' => 'Super Admin', 'password' => Hash::make('password')]
        );
        $admin->syncRoles(['super_admin']);

        // Pemilik Apotek
        $pemilik = User::firstOrCreate(
            ['email' => 'pemilik@admin.com'],
            ['name' => 'Pemilik Apotek', 'password' => Hash::make('password')]
        );
        $pemilik->syncRoles(['pemilik_apotek']);

        // Petugas Apotek
        $petugas = User::firstOrCreate(
            ['email' => 'petugas@admin.com'],
            ['name' => 'Petugas Apotek', 'password' => Hash::make('password')]
        );
        $petugas->syncRoles(['petugas_apotek']);
    }
}
