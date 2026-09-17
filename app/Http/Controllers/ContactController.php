<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use App\Models\Rubric;
use App\Models\Tag;
use App\Services\CacheService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class ContactController extends Controller
{
    public function __construct(private CacheService $cache) {}

    public function index(): View
    {
        return view('contact', [
            'metaTitle' => __('Обратная связь'),
            'metaDesc' => '',
        ]);
    }

    public function store(ContactRequest $request): RedirectResponse
    {
        try {
            Contact::create([
                'name' => $request->name,
                'email' => $request->email,
                'message' => $request->message,
                'user_id' => Auth::id(),
                'title' => $request->name . ' — ' . $request->email,
            ]);

            return redirect()
                ->route('contact')
                ->with('success', __('Сообщение отправлено!'));
        } catch (\Exception $e) {
            return redirect()
                ->route('contact')
                ->with('error', __('Сообщение не получилось отправить. Что-то сломалось.'));
        }
    }
}
