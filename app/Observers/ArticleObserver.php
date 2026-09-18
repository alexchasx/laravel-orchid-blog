<?php

namespace App\Observers;

// use App\Classes\ModelCache;
use App\Mail\NewArticleMail;
use App\Models\Article;
use App\Models\Rubric;
use App\Models\Subscriber;
use App\Models\Tag;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ArticleObserver
{
    /**
     * Отправка рассылки подписчикам при публикации статьи.
     */
    private function notifySubscribers(Article $article): void
    {
        if (! $article->is_published) {
            return;
        }

        // Статья может быть запланирована на будущее — уведомляем только после выхода.
        if (empty($article->published_at) || $article->published_at->gt(now())) {
            return;
        }

        try {
            Subscriber::active()->each(function (Subscriber $subscriber) use ($article) {
                Mail::to($subscriber->email)->send(new NewArticleMail($article, $subscriber));
            });
        } catch (\Throwable $e) {
            Log::error('Ошибка отправки рассылки о новой статье: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
    /**
     * Handle the Article "created" event.
     *
     * @param  \App\Models\Article  $article
     * @return void
     */
    public function created(Article $article)
    {
        Tag::updateCountArticles($article);
        $this->notifySubscribers($article);
        // ModelCache::updateCache(Rubric::class);
        // ModelCache::updateCache(Tag::class);
    }

    /**
     * Handle the Article "updated" event.
     *
     * @param  \App\Models\Article  $article
     * @return void
     */
    public function updated(Article $article)
    {
        Tag::updateCountArticles($article);

        // Уведомляем подписчиков, когда статья переходит из черновика в опубликованную.
        if ($article->wasChanged('is_published')) {
            $this->notifySubscribers($article);
        }

        // ModelCache::updateCache(Rubric::class);
        // ModelCache::updateCache(Tag::class);
    }

    /**
     * Handle the Article "deleted" event.
     *
     * @param  \App\Models\Article  $article
     * @return void
     */
    public function deleted(Article $article)
    {
        Tag::updateCountArticles($article);
        // ModelCache::updateCache(Rubric::class);
        // ModelCache::updateCache(Tag::class);
    }

    /**
     * Handle the Article "restored" event.
     *
     * @param  \App\Models\Article  $article
     * @return void
     */
    public function restored(Article $article)
    {
        Tag::updateCountArticles($article);
        // ModelCache::updateCache(Rubric::class);
        // ModelCache::updateCache(Tag::class);
    }

    /**
     * Handle the Article "force deleted" event.
     *
     * @param  \App\Models\Article  $article
     * @return void
     */
    public function forceDeleted(Article $article)
    {
        Tag::updateCountArticles($article);
        // ModelCache::updateCache(Rubric::class);
        // ModelCache::updateCache(Tag::class);
    }
}
