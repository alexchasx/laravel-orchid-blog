<?php

namespace Tests\Feature;

use App\Mail\NewArticleMail;
use App\Models\Article;
use App\Models\ConsentLog;
use App\Models\Rubric;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class SubscriberTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscription_is_created(): void
    {
        $response = $this->postJson(route('subscribe.store'), [
            'email' => 'developer@example.com',
            'consent_processing' => 1,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('subscribers', [
            'email' => 'developer@example.com',
            'status' => Subscriber::STATUS_ACTIVE,
        ]);
    }

    public function test_subscription_creates_consent_log(): void
    {
        $response = $this->postJson(route('subscribe.store'), [
            'email' => 'consent-log@example.com',
            'consent_processing' => 1,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('consent_logs', [
            'subject_type' => ConsentLog::SUBJECT_SUBSCRIBER,
            'consent_type' => ConsentLog::TYPE_PROCESSING,
        ]);

        $log = ConsentLog::where('subject_type', ConsentLog::SUBJECT_SUBSCRIBER)
            ->where('consent_type', ConsentLog::TYPE_PROCESSING)
            ->first();

        $this->assertNotNull($log);
        $this->assertNotNull($log->subject_id);
        $this->assertNotNull($log->consent_text);
        $this->assertNotNull($log->consented_at);
    }

    public function test_resubscribe_does_not_duplicate_consent_log(): void
    {
        // Первая подписка.
        $this->postJson(route('subscribe.store'), [
            'email' => 'no-dup@example.com',
            'consent_processing' => 1,
        ])->assertOk();

        $this->assertDatabaseCount('consent_logs', 1);

        // Отписка.
        $subscriber = \App\Models\Subscriber::where('email', 'no-dup@example.com')->first();
        $subscriber->status = Subscriber::STATUS_UNSUBSCRIBED;
        $subscriber->token = null;
        $subscriber->save();

        // Повторная подписка — не дублирует лог.
        $this->postJson(route('subscribe.store'), [
            'email' => 'no-dup@example.com',
            'consent_processing' => 1,
        ])->assertOk();

        $this->assertDatabaseCount('consent_logs', 1);
    }

    public function test_email_is_normalized_and_duplicate_is_idempotent(): void
    {
        $this->postJson(route('subscribe.store'), ['email' => 'Dev@Example.com', 'consent_processing' => 1])->assertOk();
        $this->postJson(route('subscribe.store'), ['email' => 'dev@example.com', 'consent_processing' => 1])->assertOk();

        $this->assertDatabaseCount('subscribers', 1);
        $this->assertDatabaseHas('subscribers', ['email' => 'dev@example.com']);
    }

    public function test_invalid_email_is_rejected(): void
    {
        $this->postJson(route('subscribe.store'), ['email' => 'not-an-email'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_missing_consent_processing_fails_validation(): void
    {
        $this->postJson(route('subscribe.store'), [
            'email' => 'no-consent@example.com',
        ])->assertStatus(422)
            ->assertJsonValidationErrors('consent_processing');
    }

    public function test_unsubscribe_deactivates_subscriber(): void
    {
        $subscriber = Subscriber::factory()->create([
            'email' => 'sub@example.com',
        ]);

        $this->get(route('subscribe.unsubscribe', ['token' => $subscriber->token]))
            ->assertOk()
            ->assertViewHas('success', true);

        $this->assertDatabaseHas('subscribers', [
            'email' => 'sub@example.com',
            'status' => Subscriber::STATUS_UNSUBSCRIBED,
            'token' => null,
        ]);
    }

    public function test_unsubscribe_revokes_consent(): void
    {
        $subscriber = Subscriber::factory()->create([
            'email' => 'revoke-consent@example.com',
        ]);

        ConsentLog::factory()->create([
            'subject_type' => ConsentLog::SUBJECT_SUBSCRIBER,
            'subject_id' => $subscriber->id,
            'comment_id' => null,
            'consent_type' => ConsentLog::TYPE_PROCESSING,
        ]);

        $this->get(route('subscribe.unsubscribe', ['token' => $subscriber->token]))
            ->assertOk()
            ->assertViewHas('success', true);

        // Лог согласия должен быть отозван (revoked_at установлен).
        $this->assertDatabaseCount('consent_logs', 1);

        $log = ConsentLog::where('subject_id', $subscriber->id)->first();
        $this->assertNotNull($log);
        $this->assertNotNull($log->revoked_at);
    }

    public function test_unsubscribe_with_invalid_token_shows_error(): void
    {
        $this->get(route('subscribe.unsubscribe', ['token' => Str::random(64)]))
            ->assertOk()
            ->assertViewHas('success', false);
    }

    public function test_resubscribe_after_unsubscribe_reactivates_subscriber(): void
    {
        Subscriber::factory()->create([
            'email' => 'sub@example.com',
            'status' => Subscriber::STATUS_UNSUBSCRIBED,
            'token' => null,
        ]);

        $this->postJson(route('subscribe.store'), ['email' => 'sub@example.com', 'consent_processing' => 1])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseCount('subscribers', 1);
        $this->assertDatabaseHas('subscribers', [
            'email' => 'sub@example.com',
            'status' => Subscriber::STATUS_ACTIVE,
        ]);
    }

    public function test_publishing_article_notifies_only_active_subscribers(): void
    {
        Mail::fake();

        $active = Subscriber::factory()->create(['email' => 'active@example.com']);
        Subscriber::factory()->create([
            'email' => 'unsub@example.com',
            'status' => Subscriber::STATUS_UNSUBSCRIBED,
            'token' => null,
        ]);

        $rubric = Rubric::factory()->create();
        $user = User::factory()->create();

        // Создание опубликованной статьи триггерит рассылку через ArticleObserver.
        Article::factory()->create([
            'rubric_id' => $rubric->id,
            'user_id' => $user->id,
            'is_published' => true,
            'published_at' => now(),
        ]);

        Mail::assertQueued(NewArticleMail::class, function (NewArticleMail $mail) use ($active) {
            return $mail->hasTo($active->email);
        });

        // Отписавшемуся подписчику письмо не уходит.
        Mail::assertNotQueued(NewArticleMail::class, function (NewArticleMail $mail) {
            return $mail->hasTo('unsub@example.com');
        });
    }
}
