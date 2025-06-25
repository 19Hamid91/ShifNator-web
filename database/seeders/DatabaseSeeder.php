<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (!User::where('email', 'admin@mail.com')->exists()) {
            User::factory()->create([
                'name' => 'admin',
                'email' => 'admin@mail.com',
            ]);
        }

        $this->call(EmployeeSeeder::class);
    }
}
