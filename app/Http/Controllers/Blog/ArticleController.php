<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\BlogArticle;
use App\Models\BlogCategory;
use App\Models\BlogComment;
use App\Models\BlogTag;
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
        dd(__METHOD__);

        $articles = BlogArticle::allPublished();

        return view('blog.index')->with([
            'articles' => $articles->paginate(self::PAGINATE),
            'categories' => BlogCategory::allPublished(),
            // 'popular' => Article::populars($articles),
            'recents' => BlogArticle::recents($articles),
            'tags' => BlogTag::allActive(),
            'empty' => self::EMPTY_IMAGE,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        dd(__METHOD__);

        $builder = BlogArticle::allPublished();
        $recents = BlogArticle::recents($builder);
        $article = $builder->findOrFail($id);
        // $article->viewed += 1;
        // $article->save();

        return view('blog.article')->with([
            'article' => $article,
            // 'popular' => Article::populars($articles),
            'recents' => $recents,
            'categories' => BlogCategory::allPublished(),
            'tags' => BlogTag::allActive(),
            // 'image' => $this->getFiles($id),
            'comments' => $article->comments,
            'empty' => self::EMPTY_IMAGE,
        ]);
    }
    /**
     * Показывает статьи по категории
     */
    public function showByCategory($categoryId)
    {
        $category = (new BlogCategory())->find($categoryId)->first(['title']);

        $builder = BlogArticle::allPublished();
        $recents = BlogArticle::recents($builder);
        $articlesByCategory = BlogArticle::byCategory($builder, $categoryId);

        return view('blog.index')->with([
            'articles' => $articlesByCategory->paginate(self::PAGINATE),
            'category' => $category,
            // 'popular' => Article::populars($articles),
            'recents' => $recents,
            'categories' => BlogCategory::allPublished(),
            'tags' => BlogTag::allActive(),
            'empty' => self::EMPTY_IMAGE,
        ]);
    }

    /**
     * Показывает статьи по тегу
     */
    public function showByTag($tagId)
    {
        $tag = BlogTag::find($tagId);
        $builders = BlogArticle::allPublished();
        $recents = BlogArticle::recents($builders);
        $articlesByTag = $builders->whereHas('tags', function (Builder $builder) use ($tagId) {
            $builder->where('tag_id', $tagId);
        });

        return view('blog.index')->with([
            'articles' => $articlesByTag->paginate(self::PAGINATE),
            'tag' => $tag,
            // 'popular' => Article::populars($articles),
            'recents' => $recents,
            'categories' => BlogCategory::allPublished(),
            'tags' => BlogTag::allActive(),
            'empty' => self::EMPTY_IMAGE,
        ]);
    }

    /**
     * Поиск
     */
    public function search(Request $request)
    {
        $articlesAll = BlogArticle::allPublished();
        $recents = BlogArticle::recents($articlesAll);
        $articles = $articlesAll->where('content', 'LIKE', "%{$request['query']}%");

        return view('blog.index')->with([
            'articles' => $articles->paginate(self::PAGINATE),
            // 'popular' => BlogArticle::populars($articles),
            'recents' => $recents,
            'categories' => BlogCategory::allPublished(),
            'tags' => BlogTag::allActive(),
            'empty' => self::EMPTY_IMAGE,
        ]);
    }
}
