<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            'JavaScript',
            'PHP',
            'HTML',
            'CSS',
            'React',
            'Vue',
            'Angular',
            'Laravel',
            'Node.js',
            'Python',
            'Java',
            'C#',
            'Swift',
            'Kotlin',
            'Mobile',
            'Frontend',
            'Backend',
            'Fullstack',
            'UI',
            'UX',
            'Database',
            'API',
            'DevOps',
            'Cloud',
            'Security',
        ];

        foreach ($tags as $tagName) {
            Tag::create([
                'name' => $tagName,
                'slug' => Str::slug($tagName),
            ]);
        }
    }
}