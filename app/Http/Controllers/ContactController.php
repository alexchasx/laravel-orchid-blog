<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use App\Models\ConsentLog;
use App\Services\ConsentTextBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

final class ContactController extends Controller
{
    public function index(): View
    {
        return view('contact', [
            'metaTitle' => __('Обратная связь'),
            'metaDesc' => null,
        ]);
    }

    public function store(ContactRequest $request, ConsentTextBuilder $textBuilder): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        unset($data['consent_processing']);
        $data['user_id'] = Auth::id();
        $data['title'] = $data['title'] ?? ($data['name'] . ' — ' . $data['email']);

        // Log::info('Сохранение сообщения обратной связи', $data);

        try {
            $processingResult = $textBuilder->processingText([
                'subject_purpose' => 'обработка обращения через форму обратной связи',
            ]);

            DB::transaction(function () use ($data, $processingResult, $request) {
                $contact = Contact::create($data);

                ConsentLog::create([
                    'comment_id'      => null,
                    'subject_type'    => ConsentLog::SUBJECT_CONTACT,
                    'subject_id'      => $contact->id,
                    'consent_type'    => ConsentLog::TYPE_PROCESSING,
                    'consent_text'    => $processingResult['text'],
                    'consent_version' => $processingResult['version'],
                    'ip_address'      => $request->ip(),
                    'user_agent'      => $request->userAgent(),
                    'page_url'        => url()->previous(route('contact')),
                    'consented_at'    => now(),
                ]);
            });

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
