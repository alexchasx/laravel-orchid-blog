<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Rubric;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

abstract class MainController extends Controller
{
    const PAGINATE = 12;

    public function setLocale($locale)
    {
        session(['user_locale' => $locale]);

        return redirect()->back();
    }

    protected function accessToNotPublic()
    {
        /** @var User $user */
        $user = Auth::user();
        if (($user && !$user->isAdmin()) || !$user) {
            abort(403);
        }
    }

    protected function getSideBar(): array
    {
        $tags = Cache::remember(
            'sidebar-tags',
            now()->addDays(1),
            fn () => Tag::articlePublished()->get()
        );

        $rubrics = Cache::remember(
            'sidebar-rubrics',
            now()->addDays(1),
            fn () => Rubric::articlePublished()->get()
        );

        return compact('tags', 'rubrics');
    }

    protected function getResponseArray(
        ?Builder $builder,
        string $metaTitle = '',
        string $metaRobots = '',
        string $metaDesc = '',
    ): array
    {
        $articles = null;
        if ($builder) {
            $articles = $builder->paginate(self::PAGINATE);
        }

        return $this->getSideBar() + [
            'articles' => $articles,
            'metaTitle' => $metaTitle,
            'metaRobots' => $metaRobots,
            'metaDesc' => $metaDesc,
        ];
    }
}
