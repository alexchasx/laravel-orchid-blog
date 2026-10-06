@extends('layouts.techlog')

@section('content')

<article class="article container">
    {{-- Article Head --}}
    <div class="article-head reveal">
        <a class="back" href="{{ route('home') }}#articles">← Назад к статьям</a>

        <h1>{{ $article->title }}</h1>

            <p class="lead">{{ $article->excerpt }} Сравнение трёх ИИ-ассистентов для разработки — от российского флагмана Сбера до опенсорс-комьюнити. Разбираем модели, тарифы, агентские режимы и доступность из РФ.</p>

        <div class="article-meta">
            {{ $article->reading_minutes }} минут чтения · Автор: {{ $article->user->name ?? config('app.name') }}
        </div>

        {{-- Tags --}}
        @if($article->tags->isNotEmpty())
        <div class="article-tags">
            @foreach($article->tags as $tag)
                <a href="{{ route('showByTag', $tag) }}">#{{ $tag->title }}</a>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Article Layout: TOC + Prose --}}
    <div @class(['article-layout', 'with-toc' => !empty($tocItems)])>
        {{-- TOC (Table of Contents): якоря на подзаголовки --}}
        @if(!empty($tocItems))
        <aside class="toc">
            @foreach($tocItems as $item)
                <a href="#{{ $item['id'] }}" class="toc_link">{{ $item['text'] }}</a>
            @endforeach
        </aside>
        @endif

        {{-- Prose Content --}}
        <div class="prose reveal">
            {{-- Hero Image --}}
            @if($article->image)
                <div class="article-hero">
                    <img src="{{ Storage::url($article->image) }}" alt="{{ $article->title }}">
                </div>
            @endif

            {{-- Article Content (HTML from markdown, с id у подзаголовков) --}}
            {!! $contentHtml !!}
        </div>
    </div>
</article>

{{-- Похожие статьи --}}
@include('includes.related_articles')

{{-- Comments --}}
<section class="section comments container" id="comments">
    @include('includes.comments_list')
    @include('includes.comments_form')
</section>

{{-- Модальное окно результата отправки комментария --}}
@include('includes.comment_modal')

@endsection
