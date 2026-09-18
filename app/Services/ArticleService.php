<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ArticleService
{
    private const PAGINATE = 12;
    private static array $selectColumn = ['id', 'title', 'published_at'];

    public function getPublic(?string $search): LengthAwarePaginator
    {
        return Article::published(
                Article::search($search)
            )->paginate(self::PAGINATE, self::$selectColumn);
    }

    public function getNotPublic(): LengthAwarePaginator
    {
        return Article::query()->orderBy('id', 'desc')
            ->where('is_published', false)
            ->paginate(self::PAGINATE, self::$selectColumn);
    }

    public function getByRubric(int $rubricId): LengthAwarePaginator
    {
        return Article::published()
            ->where('rubric_id', $rubricId)
            ->paginate(self::PAGINATE, self::$selectColumn);
    }

    public function getByTag(int $tagId): LengthAwarePaginator
    {
        return Article::published()
            ->whereHas('tags', function (Builder $builder) use ($tagId) {
                $builder->where('tag_id', $tagId);
            })
            ->paginate(self::PAGINATE, self::$selectColumn);
    }

    public function checkAccess(Article $article): void
    {
        /** @var User $user */
        if (!$article->is_published
            && (!($user = Auth::user()) || !$user->isAdmin())
        ) {
            abort(403);
        }
    }

    /**
     * Извлекает подзаголовки (h2/h3) из HTML-контента статьи,
     * добавляет каждому уникальный id-якорь и возвращает:
     *  - contentHtml — HTML с проставленными id у заголовков;
     *  - tocItems — список пунктов оглавления для aside.toc.
     *
     * @return array{contentHtml: string, tocItems: array<int, array{id: string, text: string, level: int}>}
     */
    public function withToc(Article $article): array
    {
        $html = (string) $article->content_html;

        if (trim($html) === '') {
            return ['contentHtml' => $html, 'tocItems' => []];
        }

        $items = [];
        $used  = [];

        $html = preg_replace_callback(
            '/<h([23])([^>]*)>(.*?)<\/h\1>/is',
            static function (array $match) use (&$items, &$used): string {
                [, $level, $attrs, $inner] = $match;

                $text = trim(html_entity_decode(strip_tags($inner), ENT_QUOTES, 'UTF-8'));

                // У заголовка уже есть id — используем его как якорь.
                if (preg_match('/\bid\s*=\s*["\']([^"\']+)["\']/i', $attrs, $matched)) {
                    $id = $matched[1];
                } else {
                    $base = Str::slug($text) ?: 'section';
                    $id   = $base;
                    $i    = 1;

                    while (in_array($id, $used, true)) {
                        $id = $base . '-' . $i++;
                    }

                    $used[] = $id;
                    $attrs  = ' id="' . e($id) . '"' . $attrs;
                }

                $items[] = [
                    'id'    => $id,
                    'text'  => $text !== '' ? $text : trim(strip_tags($inner)),
                    'level' => (int) $level,
                ];

                return "<h{$level}{$attrs}>{$inner}</h{$level}>";
            },
            $html
        );

        return [
            'contentHtml' => $html ?? (string) $article->content_html,
            'tocItems'    => $items,
        ];
    }
}
