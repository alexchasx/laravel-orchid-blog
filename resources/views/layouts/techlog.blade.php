<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0a0a0a">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if(!empty($article))
        <title>{{ $article->title }} — TECH//LOG</title>
        <meta name="description" content="{{ $article->meta_desc }}">
        <meta property="og:type" content="article">
        <meta property="og:title" content="{{ $article->title }}">
        <meta property="og:description" content="{{ $article->meta_desc }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:locale" content="ru-RU">
    @else
        <title>{{ $metaTitle ?? 'TECH//LOG — ИТ-блог' }}</title>
        <meta name="description" content="{{ $metaDesc ?? 'ИТ-блог о технологиях, архитектуре, инструментах и практических решениях для современного разработчика.' }}">
        <meta property="og:type" content="website">
        <meta property="og:title" content="{{ $metaTitle ?? 'TECH//LOG — ИТ-блог' }}">
        <meta property="og:description" content="{{ $metaDesc ?? 'ИТ-блог о технологиях, архитектуре, инструментах и практических решениях для современного разработчика.' }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:locale" content="ru-RU">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/sass/techlog/index.scss', 'resources/js/techlog.js'])

    @stack('styles')
</head>
<body>
<a class="skip-link" href="#main">К содержимому</a>

<header class="site-header" id="top">
    <nav class="nav container" aria-label="Основная навигация">
        <a class="brand" href="{{ route('home') }}" aria-label="TECH//LOG — главная">
            <span>TECH</span><b>//</b>LOG
        </a>
        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-menu">
            <span></span><span></span><span></span>
            <span class="sr-only">Открыть меню</span>
        </button>
        <div class="nav-links" id="primary-menu">
            <a href="{{ route('home') }}#articles">Статьи</a>
            <a href="{{ route('home') }}#topics">Темы</a>
            <a href="{{ route('home') }}#about">О блоге</a>
            <a href="{{ route('contact') }}">Контакты</a>
            <button class="theme-toggle" type="button" aria-label="Переключить тему" title="Переключить тему">☼</button>
        </div>
    </nav>
</header>

<main id="main">
    @yield('content')
</main>

<footer class="footer">
    <div class="container footer-inner">
        <span>&copy; 2026 TECH//LOG. Built for engineers.</span>
        <div>
            <a href="https://github.com/" target="_blank" rel="noopener">GitHub</a>
            <a href="https://t.me/" target="_blank" rel="noopener">Telegram</a>
            <a href="https://www.linkedin.com/" target="_blank" rel="noopener">LinkedIn</a>
            <a href="{{ route('contact') }}">Контакты</a>
        </div>
    </div>
</footer>

<button class="to-top" type="button" aria-label="Наверх">↑</button>

@stack('scripts')
</body>
</html>
