<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubscribeRequest;
use App\Models\ConsentLog;
use App\Models\Subscriber;
use App\Services\ConsentTextBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class SubscriberController extends Controller
{
    /**
     * Подписка на новые статьи.
     */
    public function store(SubscribeRequest $request): RedirectResponse|JsonResponse
    {
        $email = Str::lower($request->validated('email'));

        // Быстрая проверка: уже активный подписчик.
        $subscriber = Subscriber::where('email', $email)->first();
        if ($subscriber !== null && $subscriber->isActive()) {
            return $this->respond(
                $request,
                __('Вы уже подписаны на новые статьи. Спасибо!')
            );
        }

        try {
            $alreadySubscribed = DB::transaction(function () use ($email): bool {
                // lockForUpdate защищает от гонки на уникальном email.
                $subscriber = Subscriber::where('email', $email)->lockForUpdate()->first();

                if ($subscriber !== null && $subscriber->isActive()) {
                    return true;
                }

                $created = false;

                if ($subscriber === null) {
                    $subscriber = Subscriber::create([
                        'user_id' => Auth::id(),
                        'email' => $email,
                    ]);
                    $created = true;
                } else {
                    // Повторная подписка после отписки: активируем и обновляем токен.
                    $subscriber->status = Subscriber::STATUS_ACTIVE;
                    $subscriber->token = Str::random(64);
                    $subscriber->user_id = Auth::id();
                    $subscriber->save();
                }

                // Создаём лог согласия на обработку ПДн, если его ещё нет.
                if ($created) {
                    $this->createConsentLog($subscriber);
                }

                return false;
            });

            return $this->respond(
                $request,
                $alreadySubscribed
                    ? __('Вы уже подписаны на новые статьи. Спасибо!')
                    : __('Подписка оформлена! Один полезный email — без спама.')
            );
        } catch (\Throwable $e) {
            Log::error('Ошибка сохранения подписчика: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Не удалось оформить подписку. Попробуйте ещё раз.'),
                ], 500);
            }

            return redirect()
                ->route('home')
                ->withFragment('newsletter')
                ->with('newsletter-error', __('Не удалось оформить подписку. Попробуйте ещё раз.'));
        }
    }

    /**
     * Отписка от рассылки по одноразовой ссылке из письма.
     */
    public function unsubscribe(string $token): View
    {
        $subscriber = Subscriber::where('token', $token)->first();

        if ($subscriber === null) {
            return view('unsubscribe', [
                'title' => __('Ссылка не сработала'),
                'success' => false,
                'message' => __('Ссылка недействительна или уже была использована.'),
                // Служебная страница — не индексируем.
                'metaRobots' => 'noindex, nofollow',
            ]);
        }

        if ($subscriber->isActive()) {
            // Отзываем активное согласие на обработку ПДн.
            $activeLog = ConsentLog::where('subject_type', ConsentLog::SUBJECT_SUBSCRIBER)
                ->where('subject_id', $subscriber->id)
                ->where('consent_type', ConsentLog::TYPE_PROCESSING)
                ->active()
                ->first();

            if ($activeLog) {
                $activeLog->revoke();
            }

            $subscriber->status = Subscriber::STATUS_UNSUBSCRIBED;
            $subscriber->token = null; // ссылка одноразовая
            $subscriber->save();

            return view('unsubscribe', [
                'title' => __('Вы отписались'),
                'success' => true,
                'message' => __('Вы отписались от рассылки. Новые статьи больше не будут приходить на этот email.'),
                'metaRobots' => 'noindex, nofollow',
            ]);
        }

        return view('unsubscribe', [
            'title' => __('Уже отписаны'),
            'success' => false,
            'message' => __('Этот email уже отписан от рассылки.'),
            'metaRobots' => 'noindex, nofollow',
        ]);
    }

    /**
     * Создать лог согласия на обработку ПДн для подписчика.
     */
    private function createConsentLog(Subscriber $subscriber): void
    {
        // Не создаём дубль, если активное согласие уже есть.
        $exists = ConsentLog::where('subject_type', ConsentLog::SUBJECT_SUBSCRIBER)
            ->where('subject_id', $subscriber->id)
            ->where('consent_type', ConsentLog::TYPE_PROCESSING)
            ->active()
            ->exists();

        if ($exists) {
            return;
        }

        $builder = new ConsentTextBuilder();
        $result = $builder->processingText([
            'subject_purpose' => 'отправка информационных и рекламных материалов по адресу электронной почты',
        ]);

        ConsentLog::create([
            'subject_type'    => ConsentLog::SUBJECT_SUBSCRIBER,
            'subject_id'      => $subscriber->id,
            'consent_type'    => ConsentLog::TYPE_PROCESSING,
            'consent_text'    => $result['text'],
            'consent_version' => $result['version'],
            'ip_address'      => request()->ip(),
            'user_agent'      => request()->userAgent(),
            'page_url'        => url()->current(),
            'consented_at'    => now(),
        ]);
    }

    private function respond(SubscribeRequest $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()
            ->route('home')
            ->withFragment('newsletter')
            ->with('newsletter-success', $message);
    }
}
