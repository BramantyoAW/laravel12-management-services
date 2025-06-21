<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'username' => 'bramantyo',
            'full_name' => 'Bramantyo Admin',
            'email' => 'bram.antyo2796@gmail.com',
            'password' => Hash::make('Password@123'),
        ]);
    }
}