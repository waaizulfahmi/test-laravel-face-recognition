<?php

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class UserSeeder extends Seeder
{
    public function run()
    {
        User::insert([
            'name' => 'wanda',
            'email' => 'johndoe@example.com',
            'password' => Hash::make('password123'),
        ]);

        // DB::table('users')->insert([
        //     'name' => 'Jane Smith',
        //     'email' => 'janesmith@example.com',
        //     'password' => Hash::make('password456'),
        // ]);
    }
}
