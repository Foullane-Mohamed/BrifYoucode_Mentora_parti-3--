<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Mentor;
use App\Models\Video;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CoursesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mentor = Mentor::first();

        if (!$mentor) {
            $this->command->info('No mentor found. Please run UsersTableSeeder first.');
            return;
        }

        // Create a few courses
        $course1 = Course::create([
            'mentor_id' => $mentor->id,
            'category_id' => 1, // Programming
            'sub_category_id' => 1, // Web Development
            'title' => 'Introduction to JavaScript',
            'slug' => 'introduction-to-javascript',
            'description' => 'Learn the basics of JavaScript programming language',
            'duration' => 600, // 10 hours in minutes
            'difficulty' => 'beginner',
            'status' => 'published',
            'is_free' => true,
            'published_at' => now(),
        ]);

        $course2 = Course::create([
            'mentor_id' => $mentor->id,
            'category_id' => 1, // Programming
            'sub_category_id' => 1, // Web Development
            'title' => 'Advanced React Development',
            'slug' => 'advanced-react-development',
            'description' => 'Learn advanced techniques for building React applications',
            'duration' => 900, // 15 hours in minutes
            'difficulty' => 'intermediate',
            'status' => 'published',
            'is_free' => false,
            'price' => 49.99,
            'published_at' => now(),
        ]);

        $course3 = Course::create([
            'mentor_id' => $mentor->id,
            'category_id' => 1, // Programming
            'sub_category_id' => 2, // Mobile Development
            'title' => 'iOS App Development with Swift',
            'slug' => 'ios-app-development-with-swift',
            'description' => 'Learn how to build iOS applications using Swift',
            'duration' => 1200, // 20 hours in minutes
            'difficulty' => 'advanced',
            'status' => 'published',
            'is_free' => false,
            'price' => 79.99,
            'discount_price' => 59.99,
            'published_at' => now(),
        ]);

        // Create videos for the first course
        $videos = [
            [
                'title' => 'Introduction to JavaScript',
                'description' => 'An overview of JavaScript and its use cases',
                'url' => 'https://example.com/videos/intro-to-js',
                'duration' => 600, // 10 minutes in seconds
                'order' => 0,
                'is_free_preview' => true,
            ],
            [
                'title' => 'JavaScript Variables and Data Types',
                'description' => 'Learn about variables and data types in JavaScript',
                'url' => 'https://example.com/videos/js-variables',
                'duration' => 900, // 15 minutes in seconds
                'order' => 1,
                'is_free_preview' => false,
            ],
            [
                'title' => 'JavaScript Functions',
                'description' => 'Understanding functions in JavaScript',
                'url' => 'https://example.com/videos/js-functions',
                'duration' => 1200, // 20 minutes in seconds
                'order' => 2,
                'is_free_preview' => false,
            ],
        ];

        foreach ($videos as $videoData) {
            Video::create(array_merge($videoData, ['course_id' => $course1->id]));
        }

        // Attach tags to courses
        $course1->tags()->attach([1, 3, 4, 16]); 
        $course2->tags()->attach([1, 5, 16]); 
        $course3->tags()->attach([13, 15]);
    }
}