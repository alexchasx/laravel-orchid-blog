<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;

class ResponseFormat
{
    const PAGINATE = 12;

    public function __construct(
        private Sidebar $sidebar,
    ) {}

    public function getArray(
        ?Builder $builder, 
        string $metaTitle = '', 
        string $metaRobots = '', 
        string $metaDesc = ''
    ): array
    {
        $articles = null;
        if ($builder) {
            $articles = $builder->paginate(self::PAGINATE);
        }
        $responce = $this->sidebar->getData() 
            + [
                'articles' => $articles,
                'metaTitle' => $metaTitle,
                'metaRobots' => $metaRobots,
                'metaDesc' => $metaDesc,
            ];
        return $responce;
    }
}