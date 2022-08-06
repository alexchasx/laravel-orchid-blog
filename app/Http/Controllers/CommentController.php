<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Comment;
use Illuminate\Http\Request;
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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'comment' => ['required', 'max:5000'],
            'author' => ['required', 'max:250'],
            'checkbot' => ['required', 'in:3'],
            // 'email' => ['required', 'max:250', 'email'],
            // 'website' => ['max:250'],
        ], [
            'comment.required' => 'Это поле необходимо для заполнения',
            'author.required' => 'Это поле необходимо для заполнения',
            'checkbot.required' => 'Это поле необходимо для заполнения',
            'comment.max' => 'Количество символов в поле не может превышать 5000',
            'author.max' => 'Количество символов в поле не может превышать 250',
            'checkbot.in' => 'Ответ неверный',
            // 'email.required' => 'Это поле необходимо для заполнения',
            // 'email.max' => 'Количество символов в поле не может превышать 250',
            // 'email.string' => 'Почта должно быть строкой',
            // 'email.email' => 'Ваша почта должна соответствовать формату mail@some.domain',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->to(url()->previous() . '#comments_form')
                ->withErrors($validator)
                ->withInput();
        }

        $comment = Article::find($request->input('article_id'))
            ->comments()
            ->save(new Comment([
                'content' => $request->input('comment'),
                'name' => $request->input('author'),
                // 'email' => $request->input('email'),
                // 'website' => $request->input('website'),
                'article_id' => $request->input('article_id'),
                'active' => true,
            ]));

        // return back()
        //     // ->withErrors(['msg' => 'Запись не найдена.'])
        //     ->withInput();
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
