<?php

namespace App\Orchid\Layouts\Article;

use App\Models\Article;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ArticleTrashTable extends Table
{
    /**
     * Data source.
     */
    protected $target = 'articles';

    /**
     * Обёртка таблицы в responsive-контейнер для резиновой ширины.
     */
    protected $tableResponsive = true;

    protected function striped(): bool
    {
        return true;
    }

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID')->sort()->width(55),

            TD::make('title', 'Заголовок'),

            TD::make('rubric_title', 'Категория')
                ->render(fn (Article $article) => $article->rubric?->title ?? '—')
                ->width(200),

            TD::make('deleted_at', 'Дата удаления')
                ->render(fn (Article $article) => $article->deleted_at?->format('d.m.Y H:i') ?? ''),

            TD::make('Восстановить')
                ->render(function (Article $article) {
                    return Button::make('')
                        ->icon('arrow-repeat')
                        ->method('restore')
                        ->confirm('Восстановить статью «' . $article->title . '»?')
                        ->parameters(['article' => $article->id]);
                }),

            TD::make('Удалить навсегда')
                ->render(function (Article $article) {
                    return Button::make('')
                        ->icon('trash')
                        ->method('forceDelete')
                        ->confirm('Удалить статью «' . $article->title . '» навсегда? Комментарии будут удалены, изображение стёрто с диска. Действие необратимо.')
                        ->parameters(['article' => $article->id]);
                }),
        ];
    }
}
