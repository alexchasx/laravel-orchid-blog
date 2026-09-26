<?php

namespace App\View\Composers;

use App\Services\BreadcrumbService;
use Illuminate\View\View;

/**
 * Внедряет в layout «layouts.techlog» массив хлебных крошек.
 *
 * На главной, about, contact и других служебных страницах — пустой массив,
 * крошки выводятся только на вложенных страницах: статьи, рубрики, теги.
 */
class BreadcrumbComposer
{
    public function __construct(
        private BreadcrumbService $service
    ) {}

    public function compose(View $view): void
    {
        $view->with('breadcrumbs', $this->service->getBreadcrumbs($view->getData()));
    }
}
