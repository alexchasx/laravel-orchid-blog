<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Article;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(CommentRequest $request): RedirectResponse
    {
        $article = Article::findOrFail($request->input('article_id'));

        $data = [
            'content' => $request->input('comment'),
            'article_id' => $article->id,
            'ip' => $request->ip(),
        ];

        if (Auth::check()) {
            $data['user_id'] = Auth::id();
            $data['name'] = Auth::user()->name;
            $data['email'] = Auth::user()->email;
            $data['active'] = true;
        } else {
            $data['user_id'] = null;
            $data['name'] = $request->input('name');
            $data['email'] = $request->input('email');
            $data['active'] = false;
        }

        $comment = $article->comments()->create($data);

        // Редирект на страницу статьи вместо url()->previous() —
        // защита от открытого редиректа и сохранение якоря.
        if ($data['active']) {
            return redirect()->to(route('articleShow', $article) . '#comment' . $comment->id);
        }

        session()->flash('success', __('Комментарий отправлен и появится после модерации.'));

        return redirect()->to(route('articleShow', $article) . '#comments');
    }

    public function delete(Comment $comment): RedirectResponse
    {
        $comment->delete();

        $article = $comment->article;

        return $article
            ? redirect()->to(route('articleShow', $article) . '#comments')
            : redirect()->route('home');
    }
}
