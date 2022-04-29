<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
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
            'user_id' => $this->faker->numberBetween(1, 16),
            'category_id' => $this->faker->numberBetween(1, 8),
            'title' => $this->faker->text(20),
            'description' => $this->faker->text(50),
            'content' => $this->faker->text(1000),
            'image' => $this->faker->image('public/storage/articles', 640, 520, null, false),
            'viewed' => $this->faker->numberBetween(1, 10000),
            'keywords' => $this->faker->text(50),
            'meta_desc' => $this->faker->text(50),
            'published' => $this->faker->boolean(),
            'published_at' => $this->faker->date(),
        ];
    }
}
