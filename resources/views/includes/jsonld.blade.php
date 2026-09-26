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
        $hasArticle = !empty($article);
    @endphp

<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@graph": [
        {{-- WebSite + SearchAction — на всех публичных страницах --}}
        {
            "@@context": "https://schema.org",
            "@@type": "WebSite",
            "@@id": "{{ $appUrl }}/#website",
            "name": "{{ $siteName }}",
            "url": "{{ $appUrl }}",
            "description": "{{ config('seo.default_description') }}",
            "inLanguage": "{{ $locale }}",
            "publisher": {
                "@@id": "{{ $appUrl }}/#organization"
            },
            "potentialAction": {
                "@@type": "SearchAction",
                "target": "{{ $appUrl }}/?search={search_term_string}",
                "query-input": "required name=search_term_string"
            }
        },
        {{-- Organization / Publisher --}}
        {
            "@@context": "https://schema.org",
            "@@type": "Organization",
            "@@id": "{{ $appUrl }}/#organization",
            "name": "{{ $orgName }}",
            "url": "{{ $orgUrl }}",
            @if($hasLogo)
            "logo": {
                "@@type": "ImageObject",
                "@@id": "{{ $appUrl }}/logo/#image",
                "url": "{{ config('seo.organization_logo') }}",
                "contentUrl": "{{ config('seo.organization_logo') }}",
                "caption": "{{ $orgName }}"
            },
            @endif
            "sameAs": {!! json_encode($orgSocial) !!}
        }
        @if($hasArticle)
        ,
        {{-- Article — только на странице статьи --}}
        {
            "@@context": "https://schema.org",
            "@@type": "Article",
            "headline": "{{ $article->title }}",
            "description": "{{ $article->meta_desc ?: ($article->excert ?: config('seo.default_description')) }}",
            "datePublished": "{{ $article->published_at->toIso8601String() }}",
            "dateModified": "{{ $article->updated_at->toIso8601String() }}",
            "mainEntityOfPage": {
                "@@type": "WebPage",
                "@@id": "{{ url()->current() }}"
            },
            "author": {
                "@@type": "Person",
                "name": "{{ $article->user->name ?? config('app.name') }}"
            },
            "publisher": {
                "@@id": "{{ $appUrl }}/#organization"
            },
            @if(!empty($article->image))
            "image": {
                "@@type": "ImageObject",
                "@@id": "{{ url()->current() }}/primary/#image",
                "url": "{{ Storage::url($article->image) }}",
                "contentUrl": "{{ Storage::url($article->image) }}",
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
