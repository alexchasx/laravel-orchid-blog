<?php

namespace App\View\Composers;

use App\Services\ArticleService;
use Illuminate\View\View;

/**
 * Внедряет в представление «index» список рубрик с опубликованными
 * статьями (для секции «Темы» на главной странице).
 */
class RubricsComposer
{
    public function __construct(
        private ArticleService $service
    ) {}

    public function compose(View $view): void
    {
        $view->with('rubrics', $this->service->getRubricsWithArticles());
    }
}
