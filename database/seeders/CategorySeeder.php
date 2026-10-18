<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Content & Blog Writing',
                'description' => 'Articles, blog outlines, catchy headlines, content rephrasing, and editorial tools.'
            ],
            [
                'name' => 'Social Media & Copywriting',
                'description' => 'Engaging LinkedIn posts, Instagram captions, ad copies, viral tweets, and hooks.'
            ],
            [
                'name' => 'Brainstorming & Idea Generation',
                'description' => 'Startup ideas, campaign concepts, product naming, and creative problem solving.'
            ],
            [
                'name' => 'Creative Writing & Storytelling',
                'description' => 'Fiction plots, script writing, character development, world-building, and poetry.'
            ],
            [
                'name' => 'SEO & Marketing Strategy',
                'description' => 'Keyword research, meta tags, content strategy, competitor analysis, and SEO audits.'
            ],
            [
                'name' => 'Coding & Software Development',
                'description' => 'Code generation, bug fixing, SQL queries, code refactoring, and API documentation.'
            ],
            [
                'name' => 'Email Marketing & Cold Outreach',
                'description' => 'High-converting cold emails, newsletters, follow-up sequences, and sales pitches.'
            ],
            [
                'name' => 'Business & Professional',
                'description' => 'SOP creation, proposal writing, meeting summaries, resume reviews, and strategy reports.'
            ],
            [
                'name' => 'Image & Visual Prompts',
                'description' => 'Midjourney, DALL-E, and Stable Diffusion text-to-image artistic prompts.'
            ],
            [
                'name' => 'Education & Academic Research',
                'description' => 'Complex topic explanations, study guides, quiz generators, and research papers.'
            ],
            [
                'name' => 'Customer Support & Sales',
                'description' => 'FAQ generators, objection handling scripts, support ticket replies, and pitch decks.'
            ],
            [
                'name' => 'Personal Productivity & Lifestyle',
                'description' => 'Time management guides, daily task planning, habit trackers, and personal goals.'
            ],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'name' => $cat['name'],
                    'description' => $cat['description'],
                ]
            );
        }
    }
}