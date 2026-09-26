<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0a0a0a">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Title & Description --}}
    @if(!empty($article))
        <title>{{ $article->title }} — {{ config('app.name') }}</title>
        <meta name="description" content="{{ $article->meta_desc ?: config('seo.default_description') }}">
    @else
        <title>{{ $metaTitle ?: config('seo.default_title', str_replace('{app_name}', config('app.name'), config('seo.default_title'))) }}</title>
        <meta name="description" content="{{ $metaDesc ?: config('seo.default_description') }}">
    @endif

    {{-- Canonical --}}
    @php
        $canonicalUrl = $canonical ?? request()->url();
        // Для 1-й страницы пагинации убираем ?page=N
        $canonicalUrl = (string) \Illuminate\Support\Uri::of($canonicalUrl)->withoutQuery('page');
    @endphp
    <link rel="canonical" href="{{ $canonicalUrl }}">

    {{-- Robots --}}
    <meta name="robots" content="{{ $metaRobots ?? 'index,follow' }}">

    {{-- Open Graph --}}
    @if(!empty($article))
        <meta property="og:type" content="article">
        <meta property="og:title" content="{{ $article->title }}">
        <meta property="og:description" content="{{ $article->meta_desc ?: config('seo.default_description') }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:locale" content="{{ config('seo.og_locale', 'ru_RU') }}">
        <meta property="og:site_name" content="{{ str_replace('{app_name}', config('app.name'), config('seo.og_site_name')) }}">
        @if(!empty($article->image))
            <meta property="og:image" content="{{ Storage::url($article->image) }}">
        @else
            @if(!empty(config('seo.og_image')))
                <meta property="og:image" content="{{ config('seo.og_image') }}">
            @endif
        @endif
    @else
        <meta property="og:type" content="website">
        <meta property="og:title" content="{{ $metaTitle ?: str_replace('{app_name}', config('app.name'), config('seo.default_title')) }}">
        <meta property="og:description" content="{{ $metaDesc ?: config('seo.default_description') }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:locale" content="{{ config('seo.og_locale', 'ru_RU') }}">
        <meta property="og:site_name" content="{{ str_replace('{app_name}', config('app.name'), config('seo.og_site_name')) }}">
        @if(!empty(config('seo.og_image')))
            <meta property="og:image" content="{{ config('seo.og_image') }}">
        @endif
    @endif

    {{-- Twitter Card --}}
    @if(!empty($article))
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $article->title }}">
        <meta name="twitter:description" content="{{ $article->meta_desc ?: config('seo.default_description') }}">
        @if(!empty($article->image))
            <meta name="twitter:image" content="{{ Storage::url($article->image) }}">
        @elseif(!empty(config('seo.og_image')))
            <meta name="twitter:image" content="{{ config('seo.og_image') }}">
        @endif
        @if(!empty(config('seo.twitter_handle')))
            <meta name="twitter:site" content="@{{ config('seo.twitter_handle') }}">
        @endif
    @else
        <meta name="twitter:card" content="{{ config('seo.twitter_card', 'summary') }}">
        <meta name="twitter:title" content="{{ $metaTitle ?: str_replace('{app_name}', config('app.name'), config('seo.default_title')) }}">
        <meta name="twitter:description" content="{{ $metaDesc ?: config('seo.default_description') }}">
        @if(!empty(config('seo.og_image')))
            <meta name="twitter:image" content="{{ config('seo.og_image') }}">
        @endif
        @if(!empty(config('seo.twitter_handle')))
            <meta name="twitter:site" content="@{{ config('seo.twitter_handle') }}">
        @endif
    @endif

    {{-- JSON-LD структурированные данные --}}
    @include('includes.jsonld')

    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="shortcut icon" href="/favicon.ico">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/sass/techlog/index.scss', 'resources/js/cookie-banner.js', 'resources/js/techlog.js'])

    @stack('styles')
</head>
<body>
<a class="skip-link" href="#main">К содержимому</a>

<header class="site-header" id="top">
    <nav class="nav container" aria-label="Основная навигация">
        <a class="brand" href="{{ route('home') }}" aria-label="{{ config('app.name') }} — главная">
            <span>TECH</span><b>//</b>LOG
        </a>
        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-menu">
            <span></span><span></span><span></span>
            <span class="sr-only">Открыть меню</span>
        </button>
        <div class="nav-links" id="primary-menu">
            <a href="{{ route('home') }}#articles">Статьи</a>
            @if(($rubrics ?? collect())->isNotEmpty())
            <div class="nav-dropdown">
                <button class="nav-dropdown-toggle" type="button" aria-expanded="false" aria-controls="nav-topics-menu">
                    Темы <span class="nav-dropdown-caret" aria-hidden="true">▾</span>
                </button>
                <div class="nav-dropdown-menu" id="nav-topics-menu">
                    @foreach($rubrics as $navRubric)
                        <a href="{{ route('showByRubric', $navRubric) }}">{{ $navRubric->title }}</a>
                    @endforeach
                </div>
            </div>
            @endif
            <a href="{{ route('about') }}">О блоге</a>
            <a href="{{ route('contact') }}">Контакты</a>
            <button class="theme-toggle" type="button" aria-label="Переключить тему" title="Переключить тему">☼</button>

            @auth
            <button class="nav-logout" type="button" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                {{ __('Выйти') }}
            </button>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
            @endauth
        </div>
    </nav>
</header>

<main id="main">
    @include('includes.breadcrumbs')
    @yield('content')
</main>

<footer class="footer">
    <div class="container footer-inner">
        <span>&copy; 2026 {{ config('app.name') }}. {{ config('my_config.slogan') }}.</span>
        <div>
            @if(config('my_config.my_github'))
                <a href="{{ config('my_config.my_github') }}" target="_blank" rel="noopener">GitHub</a>
            @endif
            @if(config('my_config.my_telegram'))
                <a href="{{ config('my_config.my_telegram') }}" target="_blank" rel="noopener">Telegram</a>
            @endif
            <a href="{{ route('contact') }}">Контакты</a>
            <a href="{{ route('privacy') }}">Конфиденциальность</a>
        </div>
    </div>
</footer>

<button class="to-top" type="button" aria-label="Наверх">↑</button>

{{-- Cookie-баннер: подключается последним в <body> (см. resources/js/cookie-banner.js) --}}
@include('includes.cookie_banner')

@stack('scripts')
</body>
</html>
