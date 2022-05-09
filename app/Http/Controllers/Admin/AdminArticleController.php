<?php

namespace App\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\ParentController;
use App\Models\Article;
use App\Models\ArticleTag;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Support\Arr;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AdminArticleController extends ParentController
{
    /**
     * Показывает все статьи в админ панели
     *
     * GET /admin/article/index
     *
     * @return View | HttpException
     */
    public function index()
    {
        self::checkAdmin();

        // $articles = Article::withTrashed()
        // $articles = Article::orderBy('created_at', 'desc')->get();
        $articles = Article::all();

        return view('admin.index')->with([
            'articles' => $articles,
            'categories' => Category::all(),
        ]);
    }

    /**
     * Выводит форму для создания статьи
     *
     * GET /admin/article/create
     *
     * @return View | HttpException
     */
    public function create()
    {
        self::checkAdmin();

        return view('admin.article.create')->with([
            'categories' => Category::all(),
            'tags' => Tag::all(),
        ]);
    }

    /**
     * Сохраняет статью и выводит форму с сообщением об успешной операции
     *
     * POST /admin/article/store
     *
     * @param Request $request
     *
     * @return $this | HttpException
     */
    public function store(Request $request)
    {
        self::checkAdmin();

        $this->validate($request, [
            'title' => ['required', 'max:255'],
            'content' => 'required',
            'description' => 'required',
        ]);

        $article = Article::create(
            Arr::except($request->all(), ['tags_id'])
        );

        foreach ($request->input('tags_id') as $tagId) {
            $article->tags()->save(new ArticleTag([
                'article_id' => $article->id,
                'tag_id' => $tagId,
            ]));
        }

        return view('admin.article.create')->with([
            'categories' => Category::all(),
            'tags' => Tag::all(),
            'message' => 'Статья успешно создана.',
        ]);
    }

    /**
     * Выводит форму для редактирования статьи
     *
     * GET /admin/article/update.{id}
     *
     * @var int $id
     *
     * @return View | HttpException
     */
    public function edit($id)
    {
        self::checkAdmin();

        // $article = Article::withTrashed()
        //     ->where('id', $id)
        //     ->first();
        $article = Article::where('id', $id)->first();

        return view('admin.article.update')->with([
            'article' => $article,
            'categories' => Category::all(),
            'tags' => Tag::all(),
        ]);
    }

    /**
     * Редактирует статью
     *
     * POST /admin/article/update
     *
     * @param Request $request
     *
     * @return RedirectResponse | HttpException
     */
    public function update(Request $request)
    {
        self::checkAdmin();

        $this->validate($request, [
            'title' => ['required', 'max:255'],
        ]);

        // $article = Article::withTrashed()
        $article = Article::where('id', $request->id)
            ->update(Arr::except($request->all(), ['tags_id', '_token']));

        $tagIds = $request->input('tags_id');

        if (is_array($tagIds)) {
            foreach ($request->input('tags_id') as $tagId) {
                ArticleTag::create([
                    'article_id' => $request->id,
                    'tag_id' => $tagId,
                ]);
            }
        }

        return redirect()->route('articleEdit', ['id' => $request->id]);
    }

    /**
     * Удаляет статью
     *
     * DELETE /admin/article/delete/{id}
     *
     * @param $id
     *
     * @return RedirectResponse | HttpException
     */
    public function destroy($id)
    {
        self::checkAdmin();

        Article::find($id)->delete();

        return redirect()->back();
    }

    /**
     * Удаляет тег статьи
     *
     * DELETE /admin/article/delete/{article_id}/{tag_id}
     *
     * @param $article
     * @param $tag
     *
     * @return RedirectResponse | HttpException
     */
    public function deleteTag($article, $tag)
    {
        self::checkAdmin();

        ArticleTag::where('article_id', $article)
            ->where('tag_id', $tag)
            ->delete();

        return redirect()->back();
    }

    /**
     * Восстанавливает пользователя
     *
     * GET /admin/article/restore/{id}
     *
     * @param $id
     *
     * @return RedirectResponse | HttpException
     */
    public function restore($id)
    {
        self::checkAdmin();

        Article::withTrashed()
            ->where('id', $id)
            ->restore();

        return redirect()->back();
    }
}
