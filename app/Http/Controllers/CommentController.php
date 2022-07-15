<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Comment;
use Illuminate\Http\Request;

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
        $this->validate($request, [
            'comment' => ['required', 'max:5000'],
            'author' => ['required', 'max:250'],
            'email' => ['required', 'max:250', 'email'],
            // 'checkbot' => ['required'],
            'website' => ['max:250'],
        ]);

        if ($request->input('checkbot') != 3) {
            return back()
                ->withErrors(['msg' => 'Не правильно.']);
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

        return back()
            // ->withErrors(['msg' => 'Запись не найдена.'])
            ->withInput();
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

    // /**
    //  * Display a listing of the myformPost.
    //  *
    //  * @return \Illuminate\Http\Response
    //  */
    // public function ajaxValidation()
    // {
    //     return view('post.ajaxValidation.ajaxValidation');
    // }

    // /**
    //  * Display a listing of the myformPost.
    //  *
    //  * @return \Illuminate\Http\Response
    //  */
    // public function ajaxValidationStore(Request $request)
    // {
    //     $validator = Validator::make(
    //         $request->all(),
    //         [
    //             'comment' => ['required'],
    //             'author' => ['required'],
    //             'check_bot' => ['required'],

    //             // TODO: образать строки
    //             // 'email' => ['required', 'max:250', 'email'],
    //             // 'website' => ['max:250'],
    //         ],
    //         // [
    //         //     'required' => 'Обязательное поле (с символом *) не заполнено.',
    //         // ]
    //     );

    //     // if ($validator->fails()) {
    //     //     return back()
    //     //         ->withErrors($validator)
    //     //         ->withInput();
    //     // }

    //     // $validated = $validator->validated();

    //     // if ($validator->validated()) {
    //     //     return response()->json(['success' => 'Added new records.']);
    //     // }

    //     return response()->json(['error' => $validator->errors()]);
    // }
}
