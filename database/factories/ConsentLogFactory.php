<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\ConsentLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ConsentLog>
 */
class ConsentLogFactory extends Factory
{
    /**
     * Определить состояние по умолчанию для модели.
     */
    public function definition(): array
    {
        return [
            'comment_id' => Comment::factory(),
            'consent_type' => $this->faker->randomElement([
                ConsentLog::TYPE_PROCESSING,
                ConsentLog::TYPE_DISTRIBUTION,
            ]),
            'consent_text' => $this->faker->paragraph(10),
            'consent_version' => '1.0',
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'page_url' => $this->faker->url(),
            'consented_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'revoked_at' => null,
        ];
    }

    /**
     * Пометить согласие как отозванное.
     */
    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'revoked_at' => $this->faker->dateTimeBetween('consented_at', 'now'),
        ]);
    }
}
