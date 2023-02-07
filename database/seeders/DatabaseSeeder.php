<?php

namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Seeder;
use Str;
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
            'password' => '$2y$10$KgsBSoxjfhDLXbLVQhj3reZH8LhqDIB3cJQG9Zpo9rrq5pvaGbgDu',
            'role_id' => 1,
            'is_active' => 1,
            'remember_token' => Str::random(10),
            'created_at' => date("Y-m-d H:i:s")
        ]);
        User::factory(10)->create();
        Book::factory(10)->create();
    }
}
