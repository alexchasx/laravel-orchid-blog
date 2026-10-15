<?php

namespace App\Orchid\Screens\Article;

use App\Models\Article;
use App\Orchid\Layouts\Article\ArticleTrashTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ArticleTrashListScreen extends Screen
{
    /**
     * Query data.
     */
    public function query(): iterable
    {
        return [
            'articles' => Article::onlyTrashed()
                ->with('rubric')
                ->orderBy('deleted_at', 'desc')
                ->paginate(24),
        ];
    }

    /**
     * Display header name.
     */
    public function name(): ?string
    {
        return 'Корзина статей';
    }

    /**
     * Button commands.
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('Назад к статьям')
                ->route('platform.articles')
                ->icon('arrow-left'),
        ];
    }

    /**
     * Views.
     */
    public function layout(): iterable
    {
        return [
            ArticleTrashTable::class,
        ];
    }

    /**
     * Restore a soft-deleted article.
     */
    public function restore(Request $request): void
    {
        Article::onlyTrashed()->findOrFail($request->integer('article'))->restore();

        Toast::info('Статья восстановлена.');
    }

    /**
     * Force delete an article permanently.
     */
    public function forceDelete(Request $request): void
    {
        $article = Article::onlyTrashed()->findOrFail($request->integer('article'));
        $image = $article->image;

        $article->forceDelete();

        // Очистка файла изображения.
        if ($image && Storage::disk('public')->exists($image)) {
            Storage::disk('public')->delete($image);
        }

        Toast::info('Статья удалена навсегда.');
    }
}
