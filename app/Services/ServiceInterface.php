<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;

interface ServiceInterface
{
    public function getResponse(        
        Builder $builder = null, 
        Model $articles = null,
        ?string $metaTitle = '', 
        ?string $metaRobots = '', 
        ?string $metaDesc = '',
        ?string $view = 'index',
    ): View;
}