<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubscribeRequest;
use App\Models\Subscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
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
        $subscriber = Subscriber::where('email', $email)->first();

        // Уже подписан — отвечаем без ошибки, чтобы не пугать пользователя.
        if ($subscriber !== null && $subscriber->isActive()) {
            return $this->respond(
                $request,
                __('Вы уже подписаны на новые статьи. Спасибо!')
            );
        }

        try {
            if ($subscriber !== null) {
                // Повторная подписка после отписки: активируем и обновляем токен.
                $subscriber->status = Subscriber::STATUS_ACTIVE;
                $subscriber->token = Str::random(64);
                $subscriber->user_id = Auth::id();
                $subscriber->save();
            } else {
                Subscriber::create([
                    'user_id' => Auth::id(),
                    'email' => $email,
                ]);
            }

            return $this->respond(
                $request,
                __('Подписка оформлена! Один полезный email — без спама.')
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

            return redirect()->back()->with('error', __('Не удалось оформить подписку. Попробуйте ещё раз.'));
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
                'success' => false,
                'message' => __('Ссылка недействительна или уже была использована.'),
            ]);
        }

        if ($subscriber->isActive()) {
            $subscriber->status = Subscriber::STATUS_UNSUBSCRIBED;
            $subscriber->token = null; // ссылка одноразовая
            $subscriber->save();

            return view('unsubscribe', [
                'success' => true,
                'message' => __('Вы отписались от рассылки. Новые статьи больше не будут приходить на этот email.'),
            ]);
        }

        return view('unsubscribe', [
            'success' => false,
            'message' => __('Этот email уже отписан от рассылки.'),
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
            ->with('success', $message);
    }
}
