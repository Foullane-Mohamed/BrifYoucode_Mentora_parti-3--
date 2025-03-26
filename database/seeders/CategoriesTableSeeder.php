<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Categories
        $programmingCategory = Category::create([
            'name' => 'Programming',
            'slug' => 'programming',
            'description' => 'Learn programming languages and software development',
        ]);

        $designCategory = Category::create([
            'name' => 'Design',
            'slug' => 'design',
            'description' => 'Learn graphic design, UX/UI, and more',
        ]);

        $businessCategory = Category::create([
            'name' => 'Business',
            'slug' => 'business',
            'description' => 'Learn business skills, marketing, and entrepreneurship',
        ]);

        // Create Subcategories
        $webDevelopment = SubCategory::create([
            'category_id' => $programmingCategory->id,
            'name' => 'Web Development',
            'slug' => 'web-development',
            'description' => 'Learn front-end and back-end web development',
        ]);

        SubCategory::create([
            'category_id' => $programmingCategory->id,
            'name' => 'Mobile Development',
            'slug' => 'mobile-development',
            'description' => 'Learn iOS and Android app development',
        ]);

        SubCategory::create([
            'category_id' => $designCategory->id,
            'name' => 'Graphic Design',
            'slug' => 'graphic-design',
            'description' => 'Learn graphic design principles and tools',
        ]);

        SubCategory::create([
            'category_id' => $designCategory->id,
            'name' => 'UX/UI Design',
            'slug' => 'ux-ui-design',
            'description' => 'Learn user experience and interface design',
        ]);

        SubCategory::create([
            'category_id' => $businessCategory->id,
            'name' => 'Marketing',
            'slug' => 'marketing',
            'description' => 'Learn digital marketing strategies',
        ]);

        SubCategory::create([
            'category_id' => $businessCategory->id,
            'name' => 'Entrepreneurship',
            'slug' => 'entrepreneurship',
            'description' => 'Learn how to start and grow a business',
        ]);
    }
}