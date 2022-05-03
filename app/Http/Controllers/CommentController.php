<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends ParentController
{

    /**
     * Показать все комментарии
     *
     * GET /admin/comment/index
     *
     * @return View | HttpException
     */
    public function index()
    {
        self::checkAdmin();

        $comments = Comment::withTrashed()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.comment.index')->with([
            'comments' => $comments,
        ]);
    }

    /**
     * Сохранить комментарий
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'content' => 'required',
        ]);

        Article::find($request->input('article_id'))
            ->comments()
            ->save(new Comment([
                'content' => $request->input('content'),
                'user_id' => $request->input('user_id'),
            ]));

        return redirect()->back();
    }

    /**
     * Редактировать комментарий
     *
     * @param Request $request
     *
     * @return $this
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'content' => 'required|max:255',
        ]);

        Comment::find($request->id)
            ->update($request->all());

        return redirect()->back();
    }

    /**
     * Удалить комментарий
     *
     * DELETE /admin/comment/delete/{id}
     *
     * @param $commentId
     *
     * @return RedirectResponse | HttpException
     */
    public function delete($commentId)
    {
        Comment::find($commentId)->delete();

        return redirect()->back();
    }

    /**
     * Восстанавливает комментарий
     *
     * GET /admin/comment/restore/{id}
     *
     * @param $id
     *
     * @return RedirectResponse | HttpException
     */
    public function restore($id)
    {
        self::checkAdmin();

        Comment::withTrashed()
            ->where('id', $id)
            ->restore();

        return redirect()->back();
    }
}
