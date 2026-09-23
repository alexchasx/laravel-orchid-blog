@extends('layouts.techlog')

@section('content')

{{-- Hero Section (не показывается на главной и на странице статей рубрики) --}}
@if ($showHero ?? true)
<section class="hero container reveal">
    <div class="hero-copy">
        <p class="eyebrow"><span class="pulse"></span> SYSTEM ONLINE / 2026</p>
        <h1>Инструменты, архитектура, решения: <em>всё для современного разработчика.</em></h1>
        <p class="lead">Практический ИТ-блог без информационного шума: разбираем архитектуру, инженерные подходы, инструменты и реальные trade-off'ы.</p>
        <div class="hero-actions">
            <a class="button primary" href="#articles">Читать статьи <span>→</span></a>
            <button class="button ghost" type="button" data-modal-open="newsletter">Подписаться</button>
        </div>
        <div class="terminal-note">
            <span>$</span> cat /etc/techlog/mission.txt<br>
            <strong>ship better software.</strong>
        </div>
    </div>
    <div class="hero-visual">
        <div class="glow"></div>
        <div class="code-card">
            <div class="window-bar"><i></i><i></i><i></i><span>architecture.ts</span></div>
            <pre><code><span class="c-purple">type</span> <span class="c-green">System</span> = {
  <span class="c-blue">scale</span>: <span class="c-orange">"elastic"</span>;
  <span class="c-blue">latency</span>: <span class="c-orange">"&lt;100ms"</span>;
  <span class="c-blue">reliability</span>: <span class="c-orange">"99.99%"</span>;
};

<span class="c-purple">const</span> <span class="c-green">ship</span> = (<span class="c-blue">idea</span>) =&gt;
  <span class="c-green">software</span>.<span class="c-blue">deliver</span>(idea);</code></pre>
        </div>
    </div>
</section>
@endif

{{-- Articles Section --}}
<section class="section container" id="articles">
    <div class="section-head reveal">
        <div>
            <h2>{{ $sectionTitle ?? 'Свежие статьи' }}</h2>
        </div>
        <a class="text-link" href="{{ route('home') }}">Все статьи →</a>
    </div>

    <div class="masonry">
        @forelse($articles as $index => $article)
            <article class="post {{ $index === 0 ? 'featured' : '' }} reveal">
                @if($article->image && $index === 0)
                    <div class="post-image image-one">
                        <span>{{ $article->rubric->title ?? 'ARTICLE' }} / {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    </div>
                @endif
                <div class="post-body">
                    <div class="meta">
                        {{ \Carbon\Carbon::parse($article->published_at)->locale('ru')->isoFormat('D MMM YYYY') }} · {{ $article->reading_minutes }} МИН
                    </div>
                    <h3><a href="{{ route('articleShow', ['article' => $article->slug]) }}">{{ $article->title }}</a></h3>
                    <p>{{ Str::limit($article->excert ?? '', 150) }}</p>
                    <a class="read" href="{{ route('articleShow', ['article' => $article->slug]) }}">
                        Читать <span>↗</span>
                    </a>
                </div>
            </article>
        @empty
            <article class="post reveal">
                <div class="post-body">
                    <p>Ничего не нашлось</p>
                </div>
            </article>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($articles->hasPages())
        {{ $articles->links() }}
    @endif
</section>

{{-- Topics Section: рубрики с опубликованными статьями --}}
@if ($rubrics->isNotEmpty())
<section class="section container topics" id="topics">
    <div class="section-head reveal">
        <div>
            <h2>Темы</h2>
        </div>
    </div>
    <div class="topic-grid">
        @foreach($rubrics as $i => $rubric)
        <a class="topic reveal" href="{{ route('showByRubric', $rubric) }}">
            <span>{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
            <strong>{{ $rubric->title }}</strong>
            <small>{{ $rubric->description }}</small>
        </a>
        @endforeach
    </div>
</section>
@endif

{{-- About Section --}}
<section class="about section container reveal" id="about">
    <div>
        <p class="eyebrow">ABOUT / 03</p>
        <h2>Инженерный взгляд<br><em>вместо шума.</em></h2>
    </div>
    <div>
        <p>{{ config('app.name') }} — независимый блог о том, как создавать и поддерживать программные системы. Здесь важны контекст, проверяемость и цена каждого технического решения.</p>
        <p>Материалы написаны для разработчиков, тимлидов и инженеров, которым нужно не просто узнать «как», а понять «почему».</p>
    </div>
</section>

{{-- Newsletter Section (перенесён в модальное окно: includes/newsletter_modal) --}}

@include('includes.newsletter_modal')

@endsection


@endsection
