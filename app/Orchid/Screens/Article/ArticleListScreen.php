<?php

namespace App\Orchid\Screens\Article;

use App\Models\Article;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;

class ArticleListScreen extends Screen
{
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'articles' => Article::paginate(24),
        ];
    }

    /**
     * Display header name.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Статьи';
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('articles', [
                TD::make('id', 'ID'),
                TD::make('title', 'Заголовок'),
                TD::make('published', 'Видна?')->render(function (Article $article) {
                    return $article->published ? 'Видна' : 'Скрыта';
                }),
                // TD::make('user_id', 'Автор'),
                TD::make('category_id', 'Категория'),
                TD::make('created_at', 'Дата создания')->defaultHidden(),
                TD::make('updated_at', 'Дата обновления')->defaultHidden(),
            ])
        ];
    }
}
