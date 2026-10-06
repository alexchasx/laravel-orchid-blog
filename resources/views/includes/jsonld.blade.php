{{--
    JSON-LD структурированные данные (Schema.org).
    Выводится только на публичных страницах (без noindex).
--}}

@if(empty($metaRobots) || !str_contains($metaRobots, 'noindex'))
    @php
        $orgSocial = array_values(array_filter(config('seo.organization_social', [])));
        $hasLogo   = !empty(config('seo.organization_logo'));
        $siteName  = str_replace('{app_name}', config('app.name'), config('seo.og_site_name'));
        $orgName   = str_replace('{app_name}', config('app.name'), config('seo.organization_name'));
        $orgUrl    = config('seo.organization_url') ?: config('app.url');
        $locale    = config('seo.og_locale', 'ru_RU');
        $appUrl    = config('app.url');
        $hasArticle = request()->routeIs('articleShow') && !empty($article);

        // JSON-LD специальные ключи (экранирование @ от Blade).
        $ctx  = '@context';
        $typ  = '@type';
        $oid  = '@id';
        $typg = '@graph';
    @endphp

<script type="application/ld+json">
{
    "{{ $ctx }}": "https://schema.org",
    "{{ $typg }}": [
        {{-- WebSite + SearchAction — на всех публичных страницах --}}
        {
            "{{ $ctx }}": "https://schema.org",
            "{{ $typ }}": "WebSite",
            "{{ $oid }}": "{{ $appUrl }}/#website",
            "name": "{{ $siteName }}",
            "url": "{{ $appUrl }}",
            "description": "{{ config('seo.default_description') }}",
            "inLanguage": "{{ $locale }}",
            "publisher": {
                "{{ $oid }}": "{{ $appUrl }}/#organization"
            },
            "potentialAction": {
                "{{ $typ }}": "SearchAction",
                "target": "{{ $appUrl }}/?search={search_term_string}",
                "query-input": "required name=search_term_string"
            }
        },
        {{-- Organization / Publisher --}}
        {
            "{{ $ctx }}": "https://schema.org",
            "{{ $typ }}": "Organization",
            "{{ $oid }}": "{{ $appUrl }}/#organization",
            "name": "{{ $orgName }}",
            "url": "{{ $orgUrl }}",
            @if($hasLogo)
            "logo": {
                "{{ $typ }}": "ImageObject",
                "{{ $oid }}": "{{ $appUrl }}/logo/#image",
                "url": "{{ config('seo.organization_logo') }}",
                "contentUrl": "{{ config('seo.organization_logo') }}",
                "caption": "{{ $orgName }}"
            },
            @endif
            "sameAs": {!! json_encode($orgSocial) !!}
        },
        {{-- BreadcrumbList — только если есть крошки --}}
        @if(!empty($breadcrumbs))
        ,
        {
            "{{ $typ }}": "BreadcrumbList",
            "itemListElement": [
                @foreach($breadcrumbs as $index => $item)
                {
                    "{{ $typ }}": "ListItem",
                    "position": {{ $index + 1 }},
                    "name": "{{ $item['label'] }}",
                    @if($item['url'] !== null)
                    "item": "{{ $item['url'] }}"
                    @else
                    "item": "{{ url()->current() }}"
                    @endif
                }{{ $index < count($breadcrumbs) - 1 ? ',' : '' }}
                @endforeach
            ]
        }
        @endif
        @if($hasArticle)
        ,
        {{-- Article — только на странице статьи --}}
        {
            "{{ $ctx }}": "https://schema.org",
            "{{ $typ }}": "Article",
            "headline": "{{ $article->title }}",
            "description": "{{ $article->meta_desc ?: ($article->excerpt ?: config('seo.default_description')) }}",
            "datePublished": "{{ $article->published_at->toIso8601String() }}",
            "dateModified": "{{ $article->updated_at->toIso8601String() }}",
            "mainEntityOfPage": {
                "{{ $typ }}": "WebPage",
                "{{ $oid }}": "{{ url()->current() }}"
            },
            "author": {
                "{{ $typ }}": "Person",
                "name": "{{ $article->user->name ?? config('app.name') }}"
            },
            "publisher": {
                "{{ $oid }}": "{{ $appUrl }}/#organization"
            },
            @if(!empty($article->image))
            "image": {
                "{{ $typ }}": "ImageObject",
                "{{ $oid }}": "{{ url()->current() }}/primary/#image",
                "url": "{{ \App\Support\Seo::absoluteUrl(Storage::url($article->image)) }}",
                "contentUrl": "{{ \App\Support\Seo::absoluteUrl(Storage::url($article->image)) }}",
                "caption": "{{ $article->title }}"
            },
            @endif
            "inLanguage": "{{ $locale }}"
        }
        @endif
    ]
}
</script>
@endif
