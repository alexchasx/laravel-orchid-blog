<?php

namespace App\Http\Controllers\Blog;

use App\Models\Blog\Article;
use App\Models\Blog\Comment;
use Illuminate\Http\Request;

class CommentController extends BaseController
{

    /**
     * Сохранить комментарий
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // "comment" => "asdad"
        // "author" => "asdas"
        // "email" => "asd"
        // "check_bot" => "yes"
        // "submit" => "Отправить"
        // "article_id" => "25"

        // dd($request->all());

        $this->validate($request, [
            'comment' => ['required', 'max:5000'],
            'author' => ['required', 'max:250'],
            'email' => ['required', 'max:250'],
            // 'check_bot' => ['required'],
            'website' => ['max:250'],
        ]);

        if (!$request->input('check_bot')) {
            return redirect()->back();
        }

        Article::find($request->input('article_id'))
            ->comments()
            ->save(new Comment([
                'content' => $request->input('comment'),
                'name' => $request->input('author'),
                'email' => $request->input('email'),
                'website' => $request->input('website'),
                'article_id' => $request->input('article_id'),
                'active' => true,
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
        dd(__METHOD__);

        $this->validate($request, [
            'content' => ['required', 'max:255'],
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
        dd(__METHOD__);

        Comment::find($commentId)->delete();

        return redirect()->back();
    }
}
