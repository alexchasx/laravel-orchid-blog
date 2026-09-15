<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\RubricResource;
use App\Http\Resources\TagResource;
use App\Models\Article;
use App\Models\Rubric;
use App\Models\Tag;
use App\Services\ArticleService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

final class PublicApiController extends Controller
{
    public function __construct(private ArticleService $service) {}

    public function articles(Request $request): AnonymousResourceCollection
    {
        $articles = $this->service->getPublicForApi(
            search: $request->string('search')->toString() ?: null,
            rubricId: $request->integer('rubric'),
            tagId: $request->integer('tag'),
        );

        return ArticleResource::collection($articles);
    }

    public function article(Article $article): ArticleResource
    {
        // В публичном API неопубликованные статьи не существуют.
        if (! $article->is_published) {
            abort(404);
        }

        $article->loadMissing(['rubric', 'tags']);

        return (new ArticleResource($article))->forDetail();
    }

    public function rubrics(): AnonymousResourceCollection
    {
        $rubrics = Cache::remember('api-rubrics', now()->addDays(2), function () {
            return Rubric::query()
                ->select(['id', 'title', 'slug', 'description'])
                ->whereHas('articles', fn ($query) => Article::published($query))
                ->orderBy('title')
                ->get();
        });

        return RubricResource::collection($rubrics);
    }

    public function tags(): AnonymousResourceCollection
    {
        $tags = Cache::remember('api-tags', now()->addDays(2), function () {
            return Tag::query()
                ->select(['id', 'title', 'slug', 'popular'])
                ->where('active', true)
                ->whereHas('articles', fn ($query) => Article::published($query))
                ->orderBy('title')
                ->get();
        });

        return TagResource::collection($tags);
    }
}
