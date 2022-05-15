<?php

namespace App\Orchid\Screens\Article;

use App\Http\Requests\ArticleRequest;
use App\Models\Article;
use App\Orchid\Layouts\Article\ArticleListTable;
use App\Orchid\Layouts\CreateOrUpdateArticle;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Modal;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert as FacadesAlert;
use Orchid\Support\Facades\Toast;

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
            'articles' => Article::filters()->defaultSort('published', 'desc')
                ->paginate(14),
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
        return [
            ModalToggle::make('Создать статью')->modal('createArticle')
                ->method('createOrUpdateArticle'),
        ];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            ArticleListTable::class,

            Layout::modal('createArticle', CreateOrUpdateArticle::class)
                ->title('Создание статьи')
                ->size(Modal::SIZE_LG)
                ->applyButton('Создать'),

            Layout::modal('editArticle', CreateOrUpdateArticle::class)
                ->size(Modal::SIZE_LG)
                ->async('asyncGetArticle')
        ];
    }

    public function asyncGetArticle(Article $article): array
    {
        return ['article' => $article];
    }

    public function createOrUpdateArticle(ArticleRequest $request): void
    {
        $articleId = $request->input('article.id');
        Article::updateOrCreate([
            'id' => $articleId,
        ], [
            'title' => $request->input('article.title'),
            'description' => $request->input('article.description'),
            'content' => $request->input('article.content'),
            'user_id' => auth()->user()->id,
            'category_id' => $request->input('article.category_id'),
            'keywords' => $request->input('article.keywords'),
            'meta_desc' => $request->input('article.meta_desc'),
            'published' => $request->boolean('article.published'),
            'published_at' => $request->input('article.published_at'),
        ]);

        is_null($articleId) ? Toast::info('Статья создана') : Toast::info('Статья обновлена');
    }
}