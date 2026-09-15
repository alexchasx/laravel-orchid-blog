<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;

final class ContactApiController extends Controller
{
    /**
     * Публичная форма обратной связи из Nuxt-фронтенда.
     * Пользователь не обязателен (user_id = null).
     */
    public function store(ContactRequest $request): JsonResponse
    {
        Contact::create([
            'title' => $request->input('title'),
            'message' => $request->input('message'),
            'read' => false,
        ]);

        return response()->json(['message' => __('Сообщение отправлено!')], 201);
    }
}
