<?php

namespace Database\Seeders;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Str;
use Hash;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'first_name' => 'Ruchira',
            'last_name' => 'Dhananjaya',
            'email' => 'webruchira@gmail.com',
            'password' => Hash::make('12345678'),
            'role_id' => 1,
            'is_active' => 1,
            'remember_token' => Str::random(10),
            'created_at' => date("Y-m-d H:i:s")
        ]);

        Role::create([
            'role' => 'Admin',
        ]);
        Role::create([
            'role' => 'Author',
        ]);
    }
}
