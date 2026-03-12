<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Seed exercises first
        $this->call(ExerciseSeeder::class);
        
        // Create default user
        User::factory()->create([
            'name' => 'Meep',
            'email' => 'meep@example.com',
            'password' => bcrypt('GrrMeep#5Dude'),
        ]);
    }
}
