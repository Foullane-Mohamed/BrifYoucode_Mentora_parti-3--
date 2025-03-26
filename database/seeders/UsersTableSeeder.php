<?php

namespace Database\Seeders;

use App\Models\Mentor;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'biography' => 'Platform administrator',
            'last_active_at' => now(),
        ]);

        // Create Mentor
        $mentor = User::create([
            'name' => 'Mentor User',
            'email' => 'mentor@example.com',
            'password' => Hash::make('password123'),
            'role' => 'mentor',
            'biography' => 'Experienced educator with 5+ years of teaching experience',
            'last_active_at' => now(),
        ]);

        // Create Student
        $student = User::create([
            'name' => 'Student User',
            'email' => 'student@example.com',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'biography' => 'Eager to learn new skills',
            'last_active_at' => now(),
        ]);

        // Create Mentor Profile
        Mentor::create([
            'user_id' => $mentor->id,
            'speciality' => 'Web Development',
            'description' => 'Expert in JavaScript, PHP, and related technologies',
            'experience_level' => 'expert',
            'skills' => ['JavaScript', 'React', 'PHP', 'Laravel'],
        ]);

        // Create Student Profile
        Student::create([
            'user_id' => $student->id,
            'description' => 'Learning web development',
            'level' => 'beginner',
            'badge_count' => 0,
            'interests' => ['Programming', 'Web Development'],
        ]);
    }
}