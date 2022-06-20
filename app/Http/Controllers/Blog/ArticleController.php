<?php

namespace App\Http\Controllers\Blog;

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
        // $dd = Article::published()->paginate(self::PAGINATE);
        // dd($dd);

        return view('welcome')->with([
            'articles' => Article::published()->paginate(self::PAGINATE, [
                'id',
                'title',
                'excert',
                'published_at',
            ]),
            // 'rubrics' => Rubric::notEmpties(),
            'currentTagId' => 'all',
            'tags' => Tag::notEmpties(),
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
        $article =  Article::findOrFail($id);
        // $article->comments_count = $article->comments()->count();
        // dd($article->comments_count);

        return view('blog.article')->with([
            'article' => $article,
            // 'rubrics' => Rubric::notEmpties(),
            'tags' => Tag::notEmpties(),
            'comments' => $article->comments,
        ]);
    }

    /**
     * Показывает статьи по категории
     */
    public function showByRubric($id)
    {
        $rubrics = Rubric::notEmpties();

        return view('welcome')->with([
            'articles' => Article::byRubric($id)->paginate(self::PAGINATE),
            'currentRubric' => $rubrics->find($id),
            'rubrics' => $rubrics,
            'tags' => Tag::notEmpties(),
        ]);
    }

    /**
     * Показывает статьи по тегу
     */
    public function showByTag($tagId)
    {
        $tags = Tag::notEmpties();
        $articlesByTag = Article::published()
            ->whereHas('tags', function (Builder $builder) use ($tagId) {
                $builder->where('tag_id', $tagId);
            });

        return view('welcome')->with([
            'articles' => $articlesByTag->paginate(self::PAGINATE),
            'currentTagId' => $tagId,
            // 'rubrics' => Rubric::notEmpties(),
            'tags' => $tags,
        ]);
    }

    /**
     * Поиск статей по словам в контексте
     */
    public function search(Request $request)
    {
        $query = $request->query();

        dd($query);

        $articles = [];
        if ($query) {
            $articles = Article::published()
                ->where('title', 'LIKE', "%{$query}%")
                ->orWhere('excert', 'LIKE', "%{$query}%")
                ->orWhere('content_raw', 'LIKE', "%{$query}%")
                ->paginate(self::PAGINATE);
        }

        return view('welcome')->with([
            'articles' => $articles,
            // 'rubrics' => Rubric::notEmpties(),
            'tags' => Tag::notEmpties(),
        ]);
    }
}
