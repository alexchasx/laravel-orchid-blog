@extends('layouts.techlog')

@php
    $metaTitle = '404 — Страница не найдена';
    $metaDesc = 'Страница не найдена. Возможно, она перемещена или никогда не существовала.';
@endphp

@section('content')

<article class="article container">
    {{-- Error Head --}}
    <div class="article-head reveal visible">
        <a class="back" href="{{ route('home') }}#articles">← Назад к статьям</a>

        <p class="eyebrow">ERROR / 404 · NOT FOUND</p>

        <h1>Страница <em>не найдена.</em></h1>

        <p class="lead">Запрошенный адрес не существует или ресурс был перемещён. Проверьте ссылку или вернитесь на главную.</p>

        <div class="article-meta">
            HTTP/1.1 404 · {{ request()->getHost() }}{{ request()->getPathInfo() }}
        </div>
    </div>

    {{-- Error Layout: TOC-навигация + Prose --}}
    <div class="article-layout">
        {{-- Sidebar: куда дальше --}}
        <aside class="toc">
            <strong>Что дальше</strong>
            <a href="{{ route('home') }}">На главную</a>
            <a href="{{ route('home') }}#articles">Свежие статьи</a>
            <a href="{{ route('home') }}#topics">Темы блога</a>
            <a href="{{ route('contact') }}">Контакты</a>
        </aside>

        {{-- Prose Content --}}
        <div class="prose">
            {{-- "Терминал" в стиле блока кода статьи --}}
            <pre><code><span class="c-green">$</span> curl {{ request()->getHost() }}{{ request()->getPathInfo() }}
<span class="c-purple">HTTP/1.1</span> <span class="c-orange">404</span> Not Found
<span class="c-blue">status</span>: <span class="c-orange">"page_missing"</span>;
<span class="c-blue">resolution</span>: <span class="c-green">"try_home"</span>;</code></pre>

            <p>Возможно, страница была удалена, переехала на новый адрес или вы ошиблись в URL. Рекомендуем начать с главной — там всегда свежие материалы.</p>

            <div class="error-actions">
                <a class="button primary" href="{{ route('home') }}">На главную <span>→</span></a>
                <a class="button ghost" href="{{ route('home') }}#articles">Все статьи</a>
            </div>
            <br>

            @if (Auth::user() && Auth::user()->isAdmin())
            <p class="error-debug">{{ class_basename($exception->getPrevious() ?? $exception) }}: {{ $exception->getPrevious()?->getMessage() ?? $exception->getMessage() }}</p>
            @endif
        </div>
    </div>
</article>

@endsection
