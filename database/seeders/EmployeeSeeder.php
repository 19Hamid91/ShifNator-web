<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $shifts = ['morning', 'night'];

        for ($i = 0; $i < 23; $i++) {
            $unavailable = [];

            // Randomize unavailable shifts
            foreach ($days as $day) {
                $dayShifts = [];
                foreach ($shifts as $shift) {
                    $dayShifts[$shift] = $faker->boolean(30);
                }
                $unavailable[$day] = $dayShifts;
            }

            Employee::create([
                'name' => $faker->name,
                'status' => $faker->randomElement(['active', 'inactive']),
                'unavailable_shift' => json_encode($unavailable),
            ]);
        }
    }
}
