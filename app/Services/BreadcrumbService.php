<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Rubric;
use App\Models\Tag;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route as RouteFacade;

/**
 * Формирует массив хлебных крошек для публичных страниц блога.
 *
 * Структура возврата:
 * [
 *     ['label' => 'Главная', 'url' => '/'],
 *     ['label' => 'Рубрика', 'url' => '/rubric/slug'],
 *     ['label' => 'Название', 'url' => null],
 * ]
 *
 * На главной и служебных страницах возвращает пустой массив.
 */
class BreadcrumbService
{
    /**
     * Получить хлебные крошки для текущей страницы.
     *
     * @param  array<string, mixed>  $viewData  данные, переданные в представление
     * @return array<array{label: string, url: string|null}>
     */
    public function getBreadcrumbs(array $viewData = []): array
    {
        $routeName = RouteFacade::currentRouteName();

        if ($routeName === null) {
            return [];
        }

        return match ($routeName) {
            // Статья: Главная / Рубрика / Статья
            'articleShow' => $this->forArticle($viewData),

            // Рубрика: Главная / Рубрика
            'showByRubric' => $this->forRubric($viewData),

            // Тег: Главная / Метка / Название
            'showByTag' => $this->forTag($viewData),

            default => [],
        };
    }

    /**
     * Крошки для страницы статьи.
     */
    private function forArticle(array $viewData): array
    {
        $breadcrumbs = [
            ['label' => 'Главная', 'url' => route('home')],
        ];

        // Рубрика статьи
        $article = $viewData['article'] ?? null;

        if ($article instanceof Article && $article->rubric !== null) {
            $breadcrumbs[] = [
                'label' => $article->rubric->title,
                'url' => route('showByRubric', $article->rubric),
            ];
        }

        $breadcrumbs[] = [
            'label' => $article ? $article->title : '',
            'url' => null,
        ];

        return $breadcrumbs;
    }

    /**
     * Крошки для страницы рубрики.
     */
    private function forRubric(array $viewData): array
    {
        $rubric = $viewData['rubric'] ?? null;

        return [
            ['label' => 'Главная', 'url' => route('home')],
            [
                'label' => $rubric instanceof Rubric ? $rubric->title : '',
                'url' => null,
            ],
        ];
    }

    /**
     * Крошки для страницы тега.
     */
    private function forTag(array $viewData): array
    {
        $tag = $viewData['tag'] ?? null;

        return [
            ['label' => 'Главная', 'url' => route('home')],
            [
                'label' => __('Метки'),
                'url' => null,
            ],
            [
                'label' => $tag instanceof Tag ? $tag->title : '',
                'url' => null,
            ],
        ];
    }
}
