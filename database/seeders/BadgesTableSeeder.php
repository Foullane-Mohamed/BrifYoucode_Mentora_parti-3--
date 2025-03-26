<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Student badges
        Badge::create([
            'name' => 'Course Completion',
            'description' => 'Awarded to students who complete a course with 100% progress',
            'type' => 'student',
            'requirements' => ['courses_completed' => 1],
        ]);

        Badge::create([
            'name' => 'Fast Learner',
            'description' => 'Awarded to students who complete a course in less than a week',
            'type' => 'student',
            'requirements' => ['days_to_complete' => 7],
        ]);

        Badge::create([
            'name' => 'Knowledge Explorer',
            'description' => 'Awarded to students who enroll in courses from at least 3 different categories',
            'type' => 'student',
            'requirements' => ['different_categories' => 3],
        ]);

        Badge::create([
            'name' => 'Dedicated Learner',
            'description' => 'Awarded to students who are active on the platform for at least 30 days',
            'type' => 'student',
            'requirements' => ['active_days' => 30],
        ]);

        // Mentor badges
        Badge::create([
            'name' => 'Course Creator',
            'description' => 'Awarded to mentors who create at least 5 courses',
            'type' => 'mentor',
            'requirements' => ['courses_created' => 5],
        ]);

        Badge::create([
            'name' => 'Popular Mentor',
            'description' => 'Awarded to mentors who have at least 50 students enrolled in their courses',
            'type' => 'mentor',
            'requirements' => ['students_enrolled' => 50],
        ]);

        Badge::create([
            'name' => 'Veteran Mentor',
            'description' => 'Awarded to mentors who have been active on the platform for at least 6 months',
            'type' => 'mentor',
            'requirements' => ['months_active' => 6],
        ]);
    }
}