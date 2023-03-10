<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Admins;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Admins::updateOrCreate(
            ['email' => 'admin@super.com'],
            [
                'firstname' => "admin",
                'lastname' => "super",
                'phone_number' => "10393003",
                'admin_type' => 0,
                'email' => 'admin@super.com',
                'password' => Hash::make('password'),
            ]
        );
    }
}
