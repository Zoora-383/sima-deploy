<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name'     => 'superAdmin123',
            'email'    => 'superadmin123@gmail.com',
            'username' => 'superAdmin123',
            'role'  => 'super-admin',
            'password' => '1911SuperAdmin08UNJ'
        ]);
    }
}
