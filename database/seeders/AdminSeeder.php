<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admins')->insert([
            'firstname' => "admin",
            'lastname' => "super",
            'phone_number' => "10393003",
            'admin_type' => 0,
            'email' => 'admin@super.com',
            'password' => Hash::make('password'),
        ]);
    }
}
