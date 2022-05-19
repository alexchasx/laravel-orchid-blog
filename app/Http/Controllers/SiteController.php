<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Tag;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SiteController extends ParentController
{
    const EMPTY_IMAGE = 'upload/no-image.png';

    /**
     * Показывает все опубликованные статьи
     */
    public function index()
    {
        $articles = Article::allPublished();

        return view('index')->with([
            'articles' => $articles->paginate(self::PAGINATE),
            'categories' => Category::allPublished(),
            // 'popular' => Article::populars($articles),
            'recents' => Article::recents($articles),
            'tags' => Tag::allActive(),
            'empty' => self::EMPTY_IMAGE,
        ]);
    }

    /**
     * Показывает одну статью
     * GET /article.{id}
     */
    public function show($articleId)
    {
        $builder = Article::allPublished();
        $recents = Article::recents($builder);
        $article = $builder->findOrFail($articleId);
        // $article->viewed += 1;
        // $article->save();

        return view('article')->with([
            'article' => $article,
            // 'popular' => Article::populars($articles),
            'recents' => $recents,
            'categories' => Category::allPublished(),
            'tags' => Tag::allActive(),
            // 'image' => $this->getFiles($articleId),
            // 'comments' => $article->comments,
            'empty' => self::EMPTY_IMAGE,
        ]);
    }

    /**
     * Показывает статьи по категории
     */
    public function showByCategory($categoryId)
    {
        $category = (new Category())->find($categoryId)->first(['title']);

        $builder = Article::allPublished();
        $recents = Article::recents($builder);
        $articlesByCategory = Article::byCategory($builder, $categoryId);

        return view('index')->with([
            'articles' => $articlesByCategory->paginate(self::PAGINATE),
            'category' => $category,
            // 'popular' => Article::populars($articles),
            'recents' => $recents,
            'categories' => Category::allPublished(),
            'tags' => Tag::allActive(),
            'empty' => self::EMPTY_IMAGE,
        ]);
    }

    /**
     * Показывает статьи по тегу
     */
    public function showByTag($tagId)
    {
        $tag = Tag::find($tagId);
        $builders = Article::allPublished();
        $recents = Article::recents($builders);
        $articlesByTag = $builders->whereHas('tags', function (Builder $builder) use ($tagId) {
            $builder->where('tag_id', $tagId);
        });

        return view('index')->with([
            'articles' => $articlesByTag->paginate(self::PAGINATE),
            'tag' => $tag,
            // 'popular' => Article::populars($articles),
            'recents' => $recents,
            'categories' => Category::allPublished(),
            'tags' => Tag::allActive(),
            'empty' => self::EMPTY_IMAGE,
        ]);
    }

    /**
     * Страница "Контакты"
     */
    public function contacts($tagId)
    {
        $articles = Article::allPublished();

        return view('index')->with([
            'articles' => $articles->paginate(self::PAGINATE),
            // 'popular' => Article::populars($articles),
            'recents' => Article::recents($articles),
            'categories' => Category::allPublished(),
            'tags' => Tag::allActive(),
            'empty' => self::EMPTY_IMAGE,
        ]);
    }

    /**
     * Поиск
     */
    public function search(Request $request)
    {
        $articlesAll = Article::allPublished();
        $recents = Article::recents($articlesAll);
        $articles = $articlesAll->where('content', 'LIKE', "%{$request['query']}%");

        return view('index')->with([
            'articles' => $articles->paginate(self::PAGINATE),
            // 'popular' => Article::populars($articles),
            'recents' => $recents,
            'categories' => Category::allPublished(),
            'tags' => Tag::allActive(),
            'empty' => self::EMPTY_IMAGE,
        ]);
    }
}
