<?php

namespace App\Orchid\Layouts\Article;

use App\Models\Article;
use App\Models\Rubric;
use Carbon\Carbon;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ArticleListTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'articles';

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
            TD::make('id', 'ID')->sort(),

            TD::make('is_published', 'Видна?')->render(function (Article $article) {
                return CheckBox::make('articles[]')
                    ->value($article->is_published)->disabled();
            })->sort(),

            TD::make('Edit')->render(function (Article $article) {
                return ModalToggle::make('')
                    ->modal('editArticle')
                    ->method('createOrUpdateArticle')
                    ->modalTitle('Редактирование')
                    ->asyncParameters([
                        'article' => $article->id
                    ])->icon('pencil');
            }),

            // TD::make('delete', 'Удалить')->but,

            TD::make('title', 'Заголовок'),

            TD::make('rubric_id', 'ID_К.')
                ->alignRight()
                ->popover('ID Категории (для сортировки по категориям)')
                ->sort(),

            TD::make('rubric_title', 'Категория')->alignLeft()->render(
                function (Article $article) {
                    return $article->rubric->title;
                }
            )->width(300),

            TD::make('published_at', 'Дата публикации')->render(function (Article $article) {
                $carbon = Carbon::create($article->published_at);
                return $carbon->format('d.m.Y');
            }),

            TD::make('created_at', 'Дата создания')->render(function (Article $article) {
                $carbon = Carbon::create($article->created_at);
                return $carbon->format('d.m.Y');
            }),

            TD::make('updated_at', 'Дата обновления')->render(function (Article $article) {
                $carbon = Carbon::create($article->updated_at);
                return $carbon->format('d.m.Y');
            }),
        ];
    }
}
