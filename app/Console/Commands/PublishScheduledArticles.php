<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;

class PublishScheduledArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'articles:publish-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Публикует черновики статей, время публикации которых наступило';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = 0;

        Article::query()
            ->where('is_published', false)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at')
            ->each(function (Article $article) use (&$count) {
                // Сохранение триггерит ArticleObserver::updated -> рассылка подписчикам.
                $article->is_published = true;
                $article->save();

                $count++;
            });

        if ($count > 0) {
            $this->info("Опубликовано запланированных статей: {$count}");
        }

        return self::SUCCESS;
    }
}