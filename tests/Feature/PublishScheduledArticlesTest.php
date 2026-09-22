<?php

namespace Tests\Feature;

use App\Mail\NewArticleMail;
use App\Models\Article;
use App\Models\Rubric;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PublishScheduledArticlesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Создаёт черновик статьи с расписанием публикации.
     */
    private function createScheduledArticle(array $attributes = []): Article
    {
        $rubric = Rubric::factory()->create();
        $user = User::factory()->create(['active' => true]);

        return Article::factory()->create(array_merge([
            'rubric_id' => $rubric->id,
            'user_id' => $user->id,
            'is_published' => false,
            'published_at' => now(),
        ], $attributes));
    }

    public function test_command_publishes_only_due_articles(): void
    {
        $due = $this->createScheduledArticle(['published_at' => now()->subHour()]);
        $laterToday = $this->createScheduledArticle(['published_at' => now()->addMinutes(30)]);
        $future = $this->createScheduledArticle(['published_at' => now()->addDay()]);

        Artisan::call('articles:publish-scheduled');

        $this->assertDatabaseHas('articles', ['id' => $due->id, 'is_published' => true]);
        $this->assertDatabaseHas('articles', ['id' => $laterToday->id, 'is_published' => false]);
        $this->assertDatabaseHas('articles', ['id' => $future->id, 'is_published' => false]);
    }

    public function test_command_does_not_touch_already_published_articles(): void
    {
        $published = $this->createScheduledArticle([
            'is_published' => true,
            'published_at' => now()->subHour(),
        ]);

        Artisan::call('articles:publish-scheduled');

        $this->assertDatabaseHas('articles', ['id' => $published->id, 'is_published' => true]);
    }

    public function test_command_triggers_newsletter_to_active_subscribers(): void
    {
        Mail::fake();

        $active = Subscriber::factory()->create(['email' => 'active@example.com']);
        Subscriber::factory()->create([
            'email' => 'unsub@example.com',
            'status' => Subscriber::STATUS_UNSUBSCRIBED,
            'token' => null,
        ]);

        $this->createScheduledArticle(['published_at' => now()->subHour()]);

        Artisan::call('articles:publish-scheduled');

        // Публикация триггерит ArticleObserver::updated -> рассылка NewArticleMail.
        Mail::assertQueued(NewArticleMail::class, function (NewArticleMail $mail) use ($active) {
            return $mail->hasTo($active->email);
        });

        // Отписавшемуся подписчику письмо не уходит.
        Mail::assertNotQueued(NewArticleMail::class, function (NewArticleMail $mail) {
            return $mail->hasTo('unsub@example.com');
        });
    }
}
