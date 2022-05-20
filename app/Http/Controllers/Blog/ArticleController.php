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
        return view('blog.index')->with([
            'articles' => Article::published()->paginate(self::PAGINATE),
            'rubrics' => Rubric::notEmpties(),
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
            'rubrics' => Rubric::notEmpties(),
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

        return view('blog.index')->with([
            'articles' => Article::byRubric($id)->paginate(self::PAGINATE),
            'rubric' => $rubrics->find($id),
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

        return view('blog.index')->with([
            'articles' => $articlesByTag->paginate(self::PAGINATE),
            'tag' => $tags->find($tagId),
            'rubrics' => Rubric::notEmpties(),
            'tags' => $tags,
        ]);
    }

    /**
     * Поиск статей по словам в контексте
     */
    public function search(Request $request)
    {
        $builder =  Article::published()
            ->where('content', 'LIKE', "%{$request['query']}%");

        return view('blog.index')->with([
            'articles' => $builder->paginate(self::PAGINATE),
            'rubrics' => Rubric::notEmpties(),
            'tags' => Tag::notEmpties(),
        ]);
    }
}
