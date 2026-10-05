<?php

namespace App\Orchid\Layouts;

use Illuminate\Contracts\View\View;
use Orchid\Screen\Layout;
use Orchid\Screen\Repository;

/**
 * Лейаут для внедрения произвольного HTML/JS в экран Orchid.
 * Используется вместо HtmlString, который не поддерживается ядром Orchid.
 */
class Html extends Layout
{
    /**
     * {@inheritdoc}
     */
    public function build(Repository $repository): View
    {
        return view('orchid.includes.edit-article-scroll-btn');
    }
}
