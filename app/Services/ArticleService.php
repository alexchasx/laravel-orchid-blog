<?php

namespace App\Services;

use App\Services\Sidebar;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;

class ArticleService implements ServiceInterface
{
    private const PAGINATE = 12;

    public function __construct(
        private Sidebar $sidebar,
    ) {}

    public function getResponse(
        Builder $builder = null, 
        Model $articles = null,
        ?string $metaTitle = '', 
        ?string $metaRobots = '', 
        ?string $metaDesc = '',
        ?string $view = 'index',
    ): View
    {
        if ($builder) {
            $articles = $builder->paginate(self::PAGINATE);
        }
        $response = $this->sidebar->getData() 
            + [
                'articles' => $articles,
                'metaTitle' => $metaTitle,
                'metaRobots' => $metaRobots,
                'metaDesc' => $metaDesc,
            ];

        return view($view, $response);
    }
}