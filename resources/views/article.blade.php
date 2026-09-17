@extends('layouts.techlog')

@section('content')

<article class="article container">
    {{-- Article Head --}}
    <div class="article-head reveal">
        <a class="back" href="{{ route('home') }}#articles">← Назад к статьям</a>

        @if($article->rubric)
            <p class="eyebrow">{{ strtoupper($article->rubric->title) }} · {{ \Carbon\Carbon::parse($article->published_at)->locale('ru')->isoFormat('D MMM YYYY') }}</p>
        @endif

        <h1>{{ $article->title }}</h1>

        @if($article->excert)
            <p class="lead">{{ $article->excert }}</p>
        @endif

        <div class="article-meta">
            {{ rand(5, 15) }} минут чтения · Автор: {{ $article->user->name ?? 'TECH//LOG' }}
        </div>
    </div>

    {{-- Article Layout: TOC + Prose --}}
    <div class="article-layout">
        {{-- TOC (Table of Contents) --}}
        @if(!empty($article->toc))
        <aside class="toc">
            <strong>В статье</strong>
            {!! $article->toc !!}
        </aside>
        @endif

        {{-- Prose Content --}}
        <div class="prose reveal">
            {{-- Hero Image --}}
            @if($article->image)
                <div class="article-hero" style="background: linear-gradient(135deg, #003322, #08120e 45%, #00ff88 180%); background-size: cover; background-position: center;"></div>
            @endif

            {{-- Article Content (HTML from markdown) --}}
            {!! $article->content_html !!}

            {{-- Tags --}}
            @if($article->tags->isNotEmpty())
            <div class="article-tags">
                @foreach($article->tags as $tag)
                    <span>#{{ $tag->title }}</span>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</article>

{{-- Comments --}}
@include('includes.comments_list')
@include('includes.comments_form')

@endsection
