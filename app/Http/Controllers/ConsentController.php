<?php

/*
|--------------------------------------------------------------------------
| Контроллер публичных страниц текстов согласий и отзыва
|--------------------------------------------------------------------------
|
| Отображает страницы с полными текстами согласий на обработку и
| распространение персональных данных. Доступны гостям (без auth).
| Также реализует механизм отзыва согласия (152-ФЗ).
*/

namespace App\Http\Controllers;

use App\Http\Requests\RevokeConsentRequest;
use App\Models\Comment;
use App\Models\ConsentLog;
use App\Services\ConsentTextBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ConsentController extends Controller
{
    /**
     * Показать текст согласия на обработку ПДн.
     */
    public function processing(ConsentTextBuilder $builder): \Illuminate\View\View
    {
        $result = $builder->processingText();

        return view('consent.processing', [
            'title'         => 'Согласие на обработку персональных данных',
            'text'          => $result['text'],
            'version'       => $result['version'],
            'effectiveDate' => ConsentTextBuilder::CONSENT_EFFECTIVE_DATE,
        ]);
    }

    /**
     * Показать текст согласия на распространение ПДн.
     */
    public function distribution(ConsentTextBuilder $builder): \Illuminate\View\View
    {
        // Для публичной страницы имя субъекта — плейсхолдер,
        // чтобы не подставлять чужие данные.
        $result = $builder->distributionText([
            'subject_name' => '[Имя субъекта]',
            'conditions'   => '',
        ]);

        return view('consent.distribution', [
            'title'         => 'Согласие на распространение персональных данных',
            'text'          => $result['text'],
            'version'       => $result['version'],
            'effectiveDate' => ConsentTextBuilder::CONSENT_EFFECTIVE_DATE,
        ]);
    }

    /**
     * Отозвать согласие на обработку/распространение ПДн.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function revoke(RevokeConsentRequest $request): RedirectResponse
    {
        $commentId  = $request->input('comment_id');
        $consentType = $request->input('consent_type');
        $email      = $request->input('email');

        // Находим комментарий.
        /** @var \App\Models\Comment|null $comment */
        $comment = Comment::withTrashed()->find($commentId);

        if (!$comment) {
            return back()->with('error', 'Комментарий не найден.');
        }

        // Проверяем e-mail — только владелец может отозвать согласие.
        if ($comment->email !== $email) {
            return back()->with('error', 'Указанный e-mail не соответствует владельцу комментария.');
        }

        // Находим активную запись согласия.
        $log = ConsentLog::where('comment_id', $commentId)
            ->where('consent_type', $consentType)
            ->active()
            ->first();

        if (!$log) {
            return back()->with('error', 'Согласие уже отозвано или не найдено.');
        }

        // Выполняем отзыв в транзакции.
        DB::transaction(function () use ($log, $comment, $consentType) {
            // 1. Помечаем лог как отозванный.
            $log->revoke();

            if ($consentType === ConsentLog::TYPE_DISTRIBUTION) {
                // 2a. Отзыв на распространение → обезличиваем комментарий.
                $comment->update([
                    'name' => 'Аноним',
                    'is_anonymized' => true,
                ]);
            }

            // 2b. Отзыв на обработку → удаляем комментарий целиком.
            // Лог согласия сохраняется: FK consent_logs.comment_id имеет
            // nullOnDelete, поэтому при forceDelete комментария comment_id
            // обнулится, а факт отзыва (revoked_at) останется зафиксированным.
            if ($consentType === ConsentLog::TYPE_PROCESSING) {
                $comment->forceDelete();
            }
        });

        $stopDays   = config('consent.distribution.stop_days', 3);
        $deleteDays = config('consent.revocation_days', 7);

        if ($consentType === ConsentLog::TYPE_PROCESSING) {
            $message = "Согласие на обработку ПДн отозвано. Персональные данные будут удалены в течение {$deleteDays} рабочих дней.";
        } else {
            $message = "Согласие на распространение ПДн отозвано. Распространение будет прекращено в течение {$stopDays} рабочих дней, обезличивание комментария — в течение {$deleteDays} рабочих дней.";
        }

        session()->flash('success', $message);

        return back();
    }
}
