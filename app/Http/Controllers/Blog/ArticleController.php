<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\Blog\Article;
use App\Models\Blog\Rubric;
use App\Models\Blog\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ArticleController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $articlesAll = Article::allPublished();

        $articles = $articlesAll->paginate(self::PAGINATE);
        $recents = Article::recents($articlesAll);
        $rubrics = Rubric::all();
        $tags = Tag::allActive();

        return view('blog.index', compact('articles', 'recents', 'rubrics', 'tags'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $articlesAll = Article::allPublished();

        $article = $articlesAll->findOrFail($id);
        $recents = Article::recents($articlesAll);
        $rubrics = Rubric::all();
        $tags = Tag::allActive();
        $comments = $article->comments;

        return view('blog.article', compact(
            'article',
            'recents',
            'rubrics',
            'tags',
            'comments'
        ));
    }

    /**
     * Показывает статьи по категории
     */
    public function showByRubric($id)
    {
        $articlesAll = Article::allPublished();
        $articlesByRubruc = Article::byRubric($articlesAll, $id);

        $articles = $articlesByRubruc->paginate(self::PAGINATE);
        $recents = Article::recents($articlesAll);
        $rubric = Rubric::find($id)->first(['title']);
        $rubrics = Rubric::all();
        $tags = Tag::allActive();

        return view('blog.index', compact('articles', 'recents', 'rubric', 'rubrics', 'tags'));
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

        return view('blog.index')->with([
            'articles' => $articlesByTag->paginate(self::PAGINATE),
            'tag' => $tag,
            // 'popular' => Article::populars($articles),
            'recents' => $recents,
            'rubrics' => Rubric::allPublished(),
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

        return view('blog.index')->with([
            'articles' => $articles->paginate(self::PAGINATE),
            // 'popular' => Article::populars($articles),
            'recents' => $recents,
            'rubrics' => Rubric::allPublished(),
            'tags' => Tag::allActive(),
            'empty' => self::EMPTY_IMAGE,
        ]);
    }
}
