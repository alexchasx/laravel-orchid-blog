<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParentController extends Controller
{
    /**
     * Количество статей на странице
     */
    const PAGINATE = 7; //TODO Создать AdminProfileController для настройки админ-панели из браузера

    /**
     * Проверяет пользователя на наличие администраторских прав
     *
     * @return true
     *
     * @throws HttpException
     */
    public static function checkAdmin()
    {

        if (
            // Auth::check() &&
            isAdmin()
        ) {
            return true;
        }

        abort(403, 'Доступ запрещён!');
    }

    /**
     * Возращает список категорий
     *
     * @return Category[] | Collection
     */
    protected function showCategories()
    {
        return Category::select(['id', 'title'])
            ->orderBy('title')
            ->where('published', true)
            ->get();
    }

    /**
     * Возращает список тегов
     *
     * @return Tag[] | Collection
     */
    protected function showTags()
    {
        return Tag::select()
            ->orderBy('title')
            ->where('active', true)
            ->get();
    }

    /**
     * Возращает список статей, разрешенных к показу
     *
     * @param string $tagId
     * @param string $categoryId
     *
     * @return Builder
     */
    protected function allArticles($tagId = null, $categoryId = null)
    {
        $articles = Article::latest()
            //            ->where('published_at', '<=', Carbon::now()) //TODO Реализовать постепенную самопубликацию по устанновленным датам
            ->where('published', true);

        if (!empty($categoryId)) {
            $articles = $articles->where('category_id', $categoryId);
        }

        if (!empty($tagId)) {
            $articles = $articles->whereHas('tags', function (Builder $builder) use ($tagId) {
                $builder->where('tag_id', $tagId);
            });
        }

        return $articles;
    }

    /**
     * Возращает три последние статьи
     *
     * @param Builder $articles
     *
     * @return Article[] | Collection
     */
    protected function recentArticles(Builder $articles)
    {
        return $articles->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
    }

    /**
     * Возращает три самые популярные статьи
     *
     * @param Builder $articles
     *
     * @return Article[] | Collection
     */
    protected function popularArticles(Builder $articles)
    {
        return $articles->orderBy('viewed', 'desc')
            ->limit(3)
            ->get();
    }

    /**
     * Возращает последний файл к статье
     *
     * @param $id
     *
     * @return File[]
     */
    // protected function getFiles($id)
    // {
    //     return Article::find($id)
    //         ->files
    //         ->last();
    // }

    /**
     * Возращает комментарии к статье
     *
     * @param $articleId
     *
     * @return Comment[] | Collection
     */
    protected function getComments($articleId)
    {
        return Article::find($articleId)->comments;
    }
}
