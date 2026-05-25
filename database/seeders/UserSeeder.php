<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = 'superadmin123@gmail.com';
        $username = Str::before($email, '@');

        $role = Role::createOrFirst(['uuid' => Str::uuid()->toString(), 'name' => 'super-admin']);
        $user = User::create([
            'uuid' => Str::uuid()->toString(),
            'role_id'  => $role->id,
            'email'    => $email,
            'password' => Hash::make('superadmin123'),
        ]);
        $user->userProfile()->create([
            'uuid' => Str::uuid()->toString(),
            'fullname'  => 'superadmin-UNJ',
            'username'   => $username,
            'phone'      => '+6282189899090',
            'location'   => 'Jl. Rawamangun Muka, RT.11/RW.14, Rawamangun, Kecamatan Pulo Gadung, Kota Jakarta Timur, Daerah Khusus Ibukota Jakarta 13220',
            'avatar_url' => 'url.png',
        ]);
    }
}
