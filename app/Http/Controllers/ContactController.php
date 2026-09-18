<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

final class ContactController extends Controller
{
    public function index(): View
    {
        return view('contact', [
            'metaTitle' => __('Обратная связь'),
            'metaDesc' => '',
        ]);
    }

    public function store(ContactRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        $data['title'] = $data['title'] ?? ($data['name'] . ' — ' . $data['email']);

        // Log::info('Сохранение сообщения обратной связи', $data);

        try {
            Contact::create($data);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Сообщение отправлено!'),
                ]);
            }

            return redirect()
                ->route('contact')
                ->with('success', __('Сообщение отправлено!'));
        } catch (\Throwable $e) {
            Log::error('Ошибка сохранения сообщения обратной связи: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Сообщение не получилось отправить. Что-то сломалось.'),
                ], 500);
            }

            return redirect()
                ->route('contact')
                ->with('error', __('Сообщение не получилось отправить. Что-то сломалось.'));
        }
    }
}
