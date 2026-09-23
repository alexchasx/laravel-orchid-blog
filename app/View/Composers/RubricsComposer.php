<?php

namespace App\View\Composers;

use App\Services\ArticleService;
use Illuminate\View\View;

/**
 * Внедряет в layout «layouts.techlog» список рубрик с опубликованными
 * статьями (для выпадающего меню «Темы» в хедере).
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
