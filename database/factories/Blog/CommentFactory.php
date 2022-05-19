<?php

namespace Database\Factories\Blog;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Blog\Comment>
 */
class CommentFactory extends Factory
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
            'article_id' => $this->faker->numberBetween(1, 15),
            'content' => $this->faker->text(500),
            'active' => $this->faker->boolean(),
        ];
    }
}
