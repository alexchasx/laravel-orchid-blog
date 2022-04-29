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
            'user_id' => 1,
            'category_id' => 1,
            'title' => $this->faker->text(20),
            'description' => $this->faker->text(50),
            'content' => $this->faker->text(),
            'image' => $this->faker->image('articles'),
            'viewed' => $this->faker->numberBetween(1, 500000),
            'keywords' => $this->faker->text(50),
            'meta_desc' => $this->faker->text(50),
            'published' => $this->faker->boolean(),
            'published_at' => $this->faker->date(),
        ];
    }
}
