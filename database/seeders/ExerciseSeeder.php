<?php

namespace Database\Seeders;

use App\Models\Exercise;
use Illuminate\Database\Seeder;

class ExerciseSeeder extends Seeder
{
    public function run(): void
    {
        $exercises = [
            // Chest
            ['name' => 'Bench Press', 'category' => 'Chest'],
            ['name' => 'Incline Bench Press', 'category' => 'Chest'],
            ['name' => 'Decline Press', 'category' => 'Chest'],
            ['name' => 'Dumbbell Fly', 'category' => 'Chest'],
            ['name' => 'Pec Fly', 'category' => 'Chest'],
            ['name' => 'Chest Press', 'category' => 'Chest'],
            
            // Back
            ['name' => 'Barbell Row', 'category' => 'Back'],
            ['name' => 'Deadlift', 'category' => 'Back'],
            ['name' => 'Lat Pulldown', 'category' => 'Back'],
            ['name' => 'Diverging Lat Pulldown', 'category' => 'Back'],
            ['name' => 'Diverging Seated Row', 'category' => 'Back'],
            ['name' => 'Pull Up', 'category' => 'Back'],
            ['name' => 'Romanian Deadlift', 'category' => 'Back'],
            ['name' => 'Back Extension', 'category' => 'Back'],
            ['name' => 'Low Back - Roc It', 'category' => 'Back'],
            
            // Shoulders
            ['name' => 'Overhead Press', 'category' => 'Shoulders'],
            ['name' => 'Shoulder Press - Machine', 'category' => 'Shoulders'],
            ['name' => 'Lateral Raise', 'category' => 'Shoulders'],
            ['name' => 'Face Pull', 'category' => 'Shoulders'],
            
            // Arms
            ['name' => 'Bicep Curl', 'category' => 'Arms'],
            ['name' => 'Hammer Curl', 'category' => 'Arms'],
            ['name' => 'Preacher Curl', 'category' => 'Arms'],
            ['name' => 'Forearm Curl', 'category' => 'Arms'],
            ['name' => 'Tricep Pushdown', 'category' => 'Arms'],
            ['name' => 'Skull Crusher', 'category' => 'Arms'],
            ['name' => 'Seated Dips', 'category' => 'Arms'],
            
            // Legs
            ['name' => 'Squat', 'category' => 'Legs'],
            ['name' => 'Leg Press', 'category' => 'Legs'],
            ['name' => 'Leg Curl', 'category' => 'Legs'],
            ['name' => 'Leg Extension', 'category' => 'Legs'],
            ['name' => 'Calf Raise', 'category' => 'Legs'],
            
            // Core
            ['name' => 'Plank', 'category' => 'Core'],
            ['name' => 'Cable Crunch', 'category' => 'Core'],
            ['name' => 'Abdominal Crunch', 'category' => 'Core'],
            ['name' => 'Rotary Torso', 'category' => 'Core'],
        ];

        foreach ($exercises as $exercise) {
            Exercise::firstOrCreate($exercise);
        }
    }
}
