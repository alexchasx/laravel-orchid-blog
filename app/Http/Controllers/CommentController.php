<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Article;
use App\Models\Comment;
use App\Models\ConsentLog;
use App\Services\ConsentTextBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    /**
     * Сохранить комментарий и зафиксировать согласия.
     */
    public function store(CommentRequest $request, ConsentTextBuilder $textBuilder): RedirectResponse
    {
        $article = Article::findOrFail($request->input('article_id'));

        // Данные для сбора: имя (для гостя — из формы, для авторизованного — из профиля).
        $subjectName = Auth::check()
            ? Auth::user()->name
            : $request->input('name', '');

        $conditions = $request->input('distribution_conditions', '');

        // Собираем тексты согласий.
        $processingResult = $textBuilder->processingText([
            'subject_purpose' => 'модерация и идентификация комментариев',
        ]);
        $distributionResult = $textBuilder->distributionText([
            'subject_name' => $subjectName,
            'conditions' => $conditions,
        ]);

        // Метаданные для логов.
        $ip        = $request->ip();
        $userAgent = $request->userAgent();
        // URL страницы, с которой отправлен комментарий (предыдущая страница — статья).
        $pageUrl = url()->previous() ?: route('articleShow', $article);

        // Данные комментария.
        $commentData = [
            'content' => $request->input('comment'),
            'article_id' => $article->id,
            'ip' => $ip,
        ];

        if (Auth::check()) {
            $commentData['user_id'] = Auth::id();
            $commentData['name'] = Auth::user()->name;
            $commentData['email'] = Auth::user()->email;
            $commentData['active'] = true;
        } else {
            $commentData['user_id'] = null;
            $commentData['name'] = $request->input('name');
            $commentData['email'] = $request->input('email');
            $commentData['active'] = false;
        }

        // Транзакция: создаём комментарий и две записи ConsentLog.
        $result = DB::transaction(function () use (
            $article,
            $commentData,
            $processingResult,
            $distributionResult,
            $ip,
            $userAgent,
            $pageUrl,
            $subjectName,
            $conditions
        ) {
            // 1. Создаём комментарий.
            $comment = $article->comments()->create($commentData);

            // 2. Создаём логи согласий.
            $processingLog = ConsentLog::create([
                'comment_id' => $comment->id,
                'consent_type' => ConsentLog::TYPE_PROCESSING,
                'consent_text' => $processingResult['text'],
                'consent_version' => $processingResult['version'],
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'page_url' => $pageUrl,
                'consented_at' => now(),
            ]);

            $distributionLog = ConsentLog::create([
                'comment_id' => $comment->id,
                'consent_type' => ConsentLog::TYPE_DISTRIBUTION,
                'consent_text' => $distributionResult['text'],
                'consent_version' => $distributionResult['version'],
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'page_url' => $pageUrl,
                'consented_at' => now(),
            ]);

            // 3. Привязываем логи к комментарию.
            $comment->update([
                'consent_processing_log_id' => $processingLog->id,
                'consent_distribution_log_id' => $distributionLog->id,
            ]);

            return $comment;
        });

        $comment = $result;

        // Редирект на страницу статьи вместо url()->previous() —
        // защита от открытого редиректа и сохранение якоря.
        if ($commentData['active']) {
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
