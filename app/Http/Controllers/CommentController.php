<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Article;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CommentController extends MainController
{
    /**
     * Сохранить комментарий
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CommentRequest $request)
    {
        $comment = Article::find($request->input('article_id'))
            ->comments()
            ->save(new Comment([
                'content' => $request->input('comment'),
                'user_id' => Auth::user()->id,
                'name' => Auth::user()->name,
                'article_id' => $request->input('article_id'),
                'active' => true,
            ]));

        return redirect()->to(url()->previous() . '#comment' . $comment->id );
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
    public function delete(Comment $comment)
    {
        $comment->delete();

        return redirect()->to(url()->previous() . '#comments');
    }
}
