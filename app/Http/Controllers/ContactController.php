<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Rubric;
use App\Models\Tag;
use App\Models\User;
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
            'tags' => $this->cache->remember(Tag::class),
            'rubrics' => $this->cache->remember(Rubric::class),
            'metaTitle' => __('Обратная связь'),
            'metaDesc' => '',
        ]);
    }

    public function store(ContactRequest $request): RedirectResponse
    {
        $messageType = 'error' ;
        $message = __('Сообщение не получилось отправить. Что-то сломалось.');
        /** @var User $user */
        $user = Auth::user();
        if ($user->saveContact($request->only('title', 'message'))) {
            $messageType = 'success' ;
            $message = __('Сообщение отправлено!');
        }
        
        return redirect()
            ->route('contact')
            ->with($messageType, $message);
    }
}
