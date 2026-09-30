<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserDummy extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        User::create([
            'nama' => 'Fauzan Fuadiansyah',
            'nim' => 'J3C123456',
            'email' => 'fauzan@apps.ipb.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'mahasiswa',
            'angkatan' => 2023,
            'talent_mapping' => 'Web Development',
            'no_handphone' => '08123456789'
        ]);
        User::create([
            'nama' => 'admin doksli',
            'nim' => 'Admin',
            'email' => 'admin@apps.ipb.ac.id',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'angkatan' => 2023,
            'talent_mapping' => 'Web Development',
            'no_handphone' => '08123456789'
        ]);
    }
}
