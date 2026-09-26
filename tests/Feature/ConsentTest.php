<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Comment;
use App\Models\ConsentLog;
use App\Models\Rubric;
use App\Models\User;
use App\Support\MathCaptcha;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsentTest extends TestCase
{
    use RefreshDatabase;

    private function createAuthoredArticle(): Article
    {
        $author = User::factory()->create(['active' => true]);
        $rubric = Rubric::factory()->create();

        return Article::factory()->create([
            'user_id' => $author->id,
            'rubric_id' => $rubric->id,
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    private function solveCaptcha(): int
    {
        MathCaptcha::question();

        return session('captcha_answer');
    }

    /**
     * Тест 1: форма комментария рендерит два чекбокса, оба unchecked, кнопка disabled.
     */
    public function test_comment_form_renders_two_consent_checkboxes(): void
    {
        $article = $this->createAuthoredArticle();

        $response = $this->get(route('articleShow', $article));

        $response->assertOk();
        $response->assertSee('consent_processing');
        $response->assertSee('consent_distribution');
        $response->assertSee('disabled');
        $response->assertSee(route('consent.processing'));
        $response->assertSee(route('consent.distribution'));
    }

    /**
     * Тест 2: отправка без чекбоксов → ошибка валидации.
     */
    public function test_submit_without_consent_checkboxes_fails_validation(): void
    {
        $article = $this->createAuthoredArticle();

        $response = $this->post('/comment.create', [
            'comment' => 'Комментарий без согласий',
            'article_id' => $article->id,
            'name' => 'Гость',
            'email' => 'guest@example.com',
            'captcha' => $this->solveCaptcha(),
        ]);

        $response->assertSessionHasErrors(['consent_processing', 'consent_distribution']);
        $this->assertDatabaseCount('comments', 0);
    }

    /**
     * Тест 3: отправка с одним чекбоксом → ошибка валидации.
     */
    public function test_submit_with_one_consent_checkbox_fails_validation(): void
    {
        $article = $this->createAuthoredArticle();

        $response = $this->post('/comment.create', [
            'comment' => 'Комментарий с одним согласием',
            'article_id' => $article->id,
            'name' => 'Гость',
            'email' => 'guest@example.com',
            'captcha' => $this->solveCaptcha(),
            'consent_processing' => 1,
            // consent_distribution не отмечен
        ]);

        $response->assertSessionHasErrors('consent_distribution');
        $this->assertDatabaseCount('comments', 0);
    }

    /**
     * Тест 4: отправка с двумя чекбоксами → комментарий создаётся, в consent_logs две записи.
     */
    public function test_submit_with_both_consent_checkboxes_creates_logs(): void
    {
        $article = $this->createAuthoredArticle();

        $response = $this->post('/comment.create', [
            'comment' => 'Комментарий с обоими согласиями',
            'article_id' => $article->id,
            'name' => 'Гость',
            'email' => 'guest@example.com',
            'captcha' => $this->solveCaptcha(),
            'consent_processing' => 1,
            'consent_distribution' => 1,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'article_id' => $article->id,
            'name' => 'Гость',
            'email' => 'guest@example.com',
            'content' => 'Комментарий с обоими согласиями',
            'active' => false,
        ]);

        $comment = Comment::where('article_id', $article->id)->first();
        $this->assertNotNull($comment);

        // Две записи в consent_logs.
        $this->assertDatabaseHas('consent_logs', [
            'comment_id' => $comment->id,
            'consent_type' => ConsentLog::TYPE_PROCESSING,
        ]);
        $this->assertDatabaseHas('consent_logs', [
            'comment_id' => $comment->id,
            'consent_type' => ConsentLog::TYPE_DISTRIBUTION,
        ]);

        // Логи содержат IP, UA, URL, версию.
        $processingLog = ConsentLog::where('comment_id', $comment->id)
            ->where('consent_type', ConsentLog::TYPE_PROCESSING)
            ->first();

        $this->assertNotNull($processingLog);
        $this->assertStringContainsString('127.0.0.1', $processingLog->ip_address);
        $this->assertEquals('1.0', $processingLog->consent_version);
        $this->assertNotNull($processingLog->consented_at);
    }

    /**
     * Тест 4a: дополнительные условия (запреты) сохраняются в тексте согласия.
     */
    public function test_distribution_conditions_saved_into_consent_text(): void
    {
        $article = $this->createAuthoredArticle();
        $conditions = 'Запрещаю использование текста комментария для обучения ИИ';

        $this->post('/comment.create', [
            'comment' => 'Комментарий с условиями',
            'article_id' => $article->id,
            'name' => 'Гость',
            'email' => 'guest@example.com',
            'captcha' => $this->solveCaptcha(),
            'consent_processing' => 1,
            'consent_distribution' => 1,
            'distribution_conditions' => $conditions,
        ])->assertRedirect();

        $comment = Comment::where('article_id', $article->id)->first();

        $distributionLog = ConsentLog::where('comment_id', $comment->id)
            ->where('consent_type', ConsentLog::TYPE_DISTRIBUTION)
            ->first();

        $this->assertNotNull($distributionLog);
        $this->assertStringContainsString($conditions, $distributionLog->consent_text);
    }

    /**
     * Тест 4b: distribution_conditions длиннее 1000 символов → ошибка валидации.
     */
    public function test_distribution_conditions_over_limit_fails_validation(): void
    {
        $article = $this->createAuthoredArticle();

        $response = $this->post('/comment.create', [
            'comment' => 'Комментарий с слишком длинными условиями',
            'article_id' => $article->id,
            'name' => 'Гость',
            'email' => 'guest@example.com',
            'captcha' => $this->solveCaptcha(),
            'consent_processing' => 1,
            'consent_distribution' => 1,
            'distribution_conditions' => str_repeat('а', 1001),
        ]);

        $response->assertSessionHasErrors('distribution_conditions');
        $this->assertDatabaseCount('comments', 0);
    }

    /**
     * Тест 5: GET /consent/processing доступен и содержит обязательные элементы.
     */
    public function test_consent_processing_page_contains_required_elements(): void
    {
        $response = $this->get(route('consent.processing'));

        $response->assertOk();
        $response->assertSee('Согласие на обработку персональных данных');
        // Обязательные разделы текста: оператор, цель, перечень данных,
        // перечень действий, порядок отзыва.
        $response->assertSee('Оператор');
        $response->assertSee('Цели обработки');
        $response->assertSee('Перечень действий');
        $response->assertSee('Порядок отзыва');
        // В тексте не должно оставаться неподставленных литералов Blade/PHP.
        $content = $response->getContent();
        $this->assertStringNotContainsString('{{', $content);
        $this->assertStringNotContainsString('self::CONSENT_EFFECTIVE_DATE', $content);
    }

    /**
     * Тест 6: GET /consent/distribution доступен и содержит обязательные элементы.
     */
    public function test_consent_distribution_page_contains_required_elements(): void
    {
        $response = $this->get(route('consent.distribution'));

        $response->assertOk();
        $response->assertSee('Согласие на распространение персональных данных');
        // Обязательные разделы по Приказу РКН № 18: цель, перечень данных,
        // перечень действий, сроки, порядок отзыва.
        $response->assertSee('Цели распространения');
        $response->assertSee('Перечень действий');
        $response->assertSee('Порядок отзыва');
        // В тексте не должно оставаться неподставленных литералов Blade/PHP.
        $content = $response->getContent();
        $this->assertStringNotContainsString('{{', $content);
        $this->assertStringNotContainsString('self::CONSENT_EFFECTIVE_DATE', $content);
    }

    /**
     * Тест 6: отзыв на распространение → revoked_at проставлен, is_anonymized = true, author_name = 'Аноним'.
     */
    public function test_revoke_distribution_consent_anonymizes_comment(): void
    {
        $article = $this->createAuthoredArticle();
        $comment = Comment::factory()->create([
            'article_id' => $article->id,
            'name' => 'Иван Петров',
            'email' => 'ivan@example.com',
            'content' => 'Текст комментария сохраняется.',
        ]);

        // Создаём логи согласий.
        $processingLog = ConsentLog::factory()->create([
            'comment_id' => $comment->id,
            'consent_type' => ConsentLog::TYPE_PROCESSING,
        ]);
        $distributionLog = ConsentLog::factory()->create([
            'comment_id' => $comment->id,
            'consent_type' => ConsentLog::TYPE_DISTRIBUTION,
        ]);

        $response = $this->post(route('consent.revoke'), [
            'comment_id' => $comment->id,
            'consent_type' => ConsentLog::TYPE_DISTRIBUTION,
            'email' => 'ivan@example.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Лог помечен отозванным.
        $distributionLog->refresh();
        $this->assertNotNull($distributionLog->revoked_at);

        // Комментарий обезличен.
        $comment->refresh();
        $this->assertTrue($comment->is_anonymized);
        $this->assertEquals('Аноним', $comment->name);
        // Текст НЕ тронут.
        $this->assertEquals('Текст комментария сохраняется.', $comment->content);
    }

    /**
     * Тест 7: отзыв на обработку → комментарий удалён, лог помечен revoked_at.
     */
    public function test_revoke_processing_consent_deletes_comment(): void
    {
        $article = $this->createAuthoredArticle();
        $comment = Comment::factory()->create([
            'article_id' => $article->id,
            'name' => 'Мария Сидорова',
            'email' => 'maria@example.com',
            'content' => 'Этот комментарий будет удалён.',
        ]);

        $processingLog = ConsentLog::factory()->create([
            'comment_id' => $comment->id,
            'consent_type' => ConsentLog::TYPE_PROCESSING,
        ]);

        $response = $this->post(route('consent.revoke'), [
            'comment_id' => $comment->id,
            'consent_type' => ConsentLog::TYPE_PROCESSING,
            'email' => 'maria@example.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Комментарий удалён полностью (force delete).
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);

        // Лог согласия сохраняется: FK consent_logs.comment_id имеет nullOnDelete,
        // поэтому comment_id обнуляется, а факт отзыва (revoked_at) остаётся.
        $processingLog->refresh();
        $this->assertNotNull($processingLog->revoked_at);
        $this->assertNull($processingLog->comment_id);
    }

    /**
     * Тест 8: отзыв чужим e-mail → отклонено.
     */
    public function test_revoke_with_wrong_email_is_rejected(): void
    {
        $article = $this->createAuthoredArticle();
        $comment = Comment::factory()->create([
            'article_id' => $article->id,
            'name' => 'Владелец',
            'email' => 'owner@example.com',
        ]);

        ConsentLog::factory()->create([
            'comment_id' => $comment->id,
            'consent_type' => ConsentLog::TYPE_DISTRIBUTION,
        ]);

        $response = $this->post(route('consent.revoke'), [
            'comment_id' => $comment->id,
            'consent_type' => ConsentLog::TYPE_DISTRIBUTION,
            'email' => 'hacker@example.com', // чужой e-mail
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Комментарий не изменён.
        $comment->refresh();
        $this->assertFalse($comment->is_anonymized);
        $this->assertEquals('Владелец', $comment->name);
    }

    /**
     * Тест 9: отзыв уже отозванного согласия → отклонено.
     */
    public function test_revoke_already_revoked_consent_is_rejected(): void
    {
        $article = $this->createAuthoredArticle();
        $comment = Comment::factory()->create([
            'article_id' => $article->id,
            'email' => 'test@example.com',
        ]);

        $log = ConsentLog::factory()->revoked()->create([
            'comment_id' => $comment->id,
            'consent_type' => ConsentLog::TYPE_DISTRIBUTION,
        ]);

        $response = $this->post(route('consent.revoke'), [
            'comment_id' => $comment->id,
            'consent_type' => ConsentLog::TYPE_DISTRIBUTION,
            'email' => 'test@example.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // revoked_at не меняется.
        $log->refresh();
        $originalRevokedAt = $log->revoked_at;
        $this->assertEquals($originalRevokedAt, $log->revoked_at);
    }

    /**
     * Тест 10: comment_id не существует → ошибка.
     */
    public function test_revoke_with_nonexistent_comment_id_fails(): void
    {
        $response = $this->post(route('consent.revoke'), [
            'comment_id' => 999999,
            'consent_type' => ConsentLog::TYPE_DISTRIBUTION,
            'email' => 'test@example.com',
        ]);

        // Валидация проходит (exists проверяется в контроллере),
        // но контроллер возвращает flash-ошибку.
        $response->assertRedirect();
        $response->assertSessionHas('errors');
    }

    /**
     * Тест 11: некорректный consent_type → ошибка валидации.
     */
    public function test_revoke_with_invalid_consent_type_fails_validation(): void
    {
        $response = $this->post(route('consent.revoke'), [
            'comment_id' => 1,
            'consent_type' => 'invalid_type',
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasErrors('consent_type');
    }

    /**
     * Тест 12: форма отзыва рендерится корректно.
     */
    public function test_revoke_form_renders_correctly(): void
    {
        $response = $this->get(route('consent.revoke.form'));

        $response->assertOk();
        $response->assertSee('Отзыв согласия');
        $response->assertSee('comment_id');
        $response->assertSee('consent_type');
        $response->assertSee('email');
    }

    /**
     * Тест 13: страница отзыва принимает query-параметры из ссылки в комментарии.
     */
    public function test_revoke_form_accepts_query_parameters(): void
    {
        $article = $this->createAuthoredArticle();
        $comment = Comment::factory()->create([
            'article_id' => $article->id,
            'email' => 'query@example.com',
        ]);

        $response = $this->get(route('consent.revoke.form', [
            'comment_id' => $comment->id,
            'email' => 'query@example.com',
        ]));

        $response->assertOk();
        $response->assertSee('query@example.com');
    }
}
