<?php

namespace App\Http\Controllers;

/*
|--------------------------------------------------------------------------
| Контроллер редиректов со старых числовых URL на slug-URL
|--------------------------------------------------------------------------
|
| Выполняет 301-редирект с `/rubric/{id}` и `/tag/{id}` на
| `/rubric/{slug}` и `/tag/{slug}` соответственно. Если запись
| удалена — возвращает 404.
*/

use App\Models\Rubric;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;

class LegacyRedirectController extends Controller
{
    /**
     * Редирект со старого числового ID рубрики на slug-URL.
     */
    public function rubric(int $legacyId): RedirectResponse
    {
        $rubric = Rubric::withTrashed()->find($legacyId);

        if ($rubric?->slug) {
            return redirect()->route('showByRubric', ['rubric' => $rubric->slug], 301);
        }

        abort(404);
    }

    /**
     * Редирект со старого числового ID тега на slug-URL.
     */
    public function tag(int $legacyId): RedirectResponse
    {
        $tag = Tag::withTrashed()->find($legacyId);

        if ($tag?->slug) {
            return redirect()->route('showByTag', ['tag' => $tag->slug], 301);
        }

        abort(404);
    }
}
