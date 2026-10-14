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
use App\Models\Subscriber;
use App\Services\ConsentTextBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ConsentController extends Controller
{
    /**
     * Показать текст согласия на обработку ПДн.
     */
    public function processing(ConsentTextBuilder $builder): \Illuminate\View\View
    {
        $result = $builder->processingText();

        $effectiveDate = config('consent.effective_date', '13 октября 2026 г.');

        return view('consent.processing', [
            'title'         => 'Согласие на обработку персональных данных',
            'metaTitle'     => 'Согласие на обработку персональных данных',
            'metaDesc'      => 'Текст согласия на обработку персональных данных в ' . config('app.name', '') . ' — версия ' . $result['version'] . '. Дата вступления в силу: ' . $effectiveDate . '.',
            'text'          => $result['text'],
            'version'       => $result['version'],
            'effectiveDate' => $effectiveDate,
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

        $effectiveDate = config('consent.effective_date', '13 октября 2026 г.');

        return view('consent.distribution', [
            'title'         => 'Согласие на распространение персональных данных',
            'metaTitle'     => 'Согласие на распространение персональных данных',
            'metaDesc'      => 'Текст согласия на распространение персональных данных в ' . config('app.name', '') . ' — версия ' . $result['version'] . '. Дата вступления в силу: ' . $effectiveDate . '.',
            'text'          => $result['text'],
            'version'       => $result['version'],
            'effectiveDate' => $effectiveDate,
        ]);
    }

    /**
     * Показать форму отзыва согласия.
     */
    public function revokeForm(): View
    {
        return view('consent.revoke', [
            'metaRobots' => 'noindex, nofollow',
        ]);
    }

    /**
     * Отозвать согласие на обработку/распространение ПДн.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function revoke(RevokeConsentRequest $request): RedirectResponse
    {
        $subjectType = $request->input('subject_type');
        $consentType = $request->input('consent_type');
        $email       = $request->input('email');

        // Отзыв для подписчика.
        if ($subjectType === ConsentLog::SUBJECT_SUBSCRIBER) {
            return $this->revokeForSubscriber($email, $consentType);
        }

        // Отзыв для комментария.
        $commentId  = $request->input('comment_id');
        /** @var \App\Models\Comment|null $comment */
        $comment = Comment::withTrashed()->find($commentId);

        if (!$comment) {
            return back()->with('error', 'Комментарий не найден.');
        }

        // Проверяем e-mail — только владелец может отозвать согласие.
        if (Str::lower($comment->email) !== Str::lower($email)) {
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

            // 1a. Мгновенная обработка (удаление/обезличивание) —
            // фиксируем processed_at, чтобы фоновая команда не трогала лог.
            $log->processed_at = now();
            $log->save();

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

    /**
     * Отзыв согласия для подписчика.
     */
    private function revokeForSubscriber(string $email, string $consentType): RedirectResponse
    {
        // Для подписчика доступен только тип «processing».
        if ($consentType !== ConsentLog::TYPE_PROCESSING) {
            return back()->with('error', 'Для подписки доступен только отзыв согласия на обработку ПДн.');
        }

        /** @var \App\Models\Subscriber|null $subscriber */
        $subscriber = Subscriber::where('email', $email)->first();

        if (!$subscriber) {
            return back()->with('error', 'Подписчик с таким e-mail не найден.');
        }

        // Находим активную запись согласия.
        $log = ConsentLog::where('subject_type', ConsentLog::SUBJECT_SUBSCRIBER)
            ->where('subject_id', $subscriber->id)
            ->where('consent_type', ConsentLog::TYPE_PROCESSING)
            ->active()
            ->first();

        if (!$log) {
            return back()->with('error', 'Согласие уже отозвано или не найдено.');
        }

        // Выполняем отзыв в транзакции.
        DB::transaction(function () use ($log, $subscriber) {
            $log->revoke();

            // Мгновенная обработка — фиксируем processed_at.
            $log->processed_at = now();
            $log->save();

            // Отзыв согласия на обработку → отписка.
            if ($subscriber->isActive()) {
                $subscriber->status = Subscriber::STATUS_UNSUBSCRIBED;
                $subscriber->token = null;
                $subscriber->save();
            }
        });

        $deleteDays = config('consent.revocation_days', 7);

        $message = "Согласие на обработку ПДн отозвано. Подписка отменена, данные будут удалены в течение {$deleteDays} рабочих дней.";

        session()->flash('success', $message);

        return back();
    }
}
