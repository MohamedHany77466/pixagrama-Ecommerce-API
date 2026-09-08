<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $exists = User::where('email', 'admin@site.com')->first();

        if (! $exists) {
            $exists = User::create([
                'name' => 'System Admin',
                'email' => 'admin@site.com',
                'password' => Hash::make('password123'), 
                'type' => 'admin',
            ]);
        }

        $exists->assignRole('admin');
    }
}
