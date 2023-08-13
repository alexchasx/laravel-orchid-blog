<?php

namespace App\Observers;

// use App\Classes\ModelCache;
use App\Models\Article;
use App\Models\Rubric;
use App\Models\Tag;

class ArticleObserver
{
    /**
     * Handle the Article "created" event.
     *
     * @param  \App\Models\Article  $article
     * @return void
     */
    public function created(Article $article)
    {
        Tag::updateCountArticles($article);
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
