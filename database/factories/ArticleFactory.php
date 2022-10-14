<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Blog\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'rubric_id' => $this->faker->numberBetween(1, 3),
            'user_id' => $this->faker->numberBetween(1, 5),
            'slug' => $this->faker->slug(),
            'title' => $this->faker->text(80),
            'excert' => $this->faker->text(400),
            'content_raw' => $this->faker->text(3000),
            'content_html' => $this->faker->text(3000),
            // 'image' => $this->faker->image('public/storage/posts', 640, 520, null, false),
            'viewed' => $this->faker->numberBetween(1, 10000),
            'keywords' => $this->faker->text(50),
            'meta_desc' => $this->faker->text(50),
            'is_published' => $this->faker->boolean(),
            'published_at' => $this->faker->date(),
        ];
    }
}
