{{-- Общий шаблон страниц ошибок в дизайне «TECH//LOG» (см. errors/404.blade.php) --}}

@php
    $tocLinks = $tocLinks ?? [
        'На главную' => route('home'),
        'Свежие статьи' => route('home').'#articles',
        'Контакты' => route('contact'),
    ];
    $primaryUrl = $primaryUrl ?? route('home');
    $primaryText = $primaryText ?? 'На главную';
    $ghostUrl = $ghostUrl ?? route('home').'#articles';
    $ghostText = $ghostText ?? 'Все статьи';
@endphp

<article class="article container">
    {{-- Error Head --}}
    <div class="article-head reveal visible">
        <a class="back" href="{{ route('home') }}#articles">← Назад к статьям</a>

        <h1>{!! $title !!}</h1>

        <p class="lead">{{ $lead }}</p>

        <div class="article-meta">
            HTTP/1.1 {{ $code }} · {{ request()->getHost() }}{{ request()->getPathInfo() }}
        </div>
    </div>

    {{-- Error Layout: TOC-навигация + Prose --}}
    <div class="article-layout">
        {{-- Sidebar: куда дальше --}}
        <aside class="toc">
            <strong>Что дальше</strong>
            @foreach ($tocLinks as $label => $url)
                <a href="{{ $url }}">{{ $label }}</a>
            @endforeach
        </aside>

        {{-- Prose Content --}}
        <div class="prose">
            {{-- "Терминал" в стиле блока кода статьи --}}
            <pre><code><span class="c-green">$</span> curl {{ request()->getHost() }}{{ request()->getPathInfo() }}
<span class="c-purple">HTTP/1.1</span> <span class="c-orange">{{ $code }}</span> {{ $statusPhrase }}
<span class="c-blue">status</span>: <span class="c-orange">"{{ $statusKey }}"</span>;
<span class="c-blue">resolution</span>: <span class="c-green">"{{ $resolution }}"</span>;</code></pre>

            <p>{{ $description }}</p>

            <div class="error-actions">
                <a class="button primary" href="{{ $primaryUrl }}">{{ $primaryText }} <span>→</span></a>
                <a class="button ghost" href="{{ $ghostUrl }}">{{ $ghostText }}</a>
            </div>
            <br>

            @if (Auth::user() && Auth::user()->isAdmin())
            <p class="error-debug">{{ class_basename($exception->getPrevious() ?? $exception) }}: {{ $exception->getPrevious()?->getMessage() ?? $exception->getMessage() }}</p>
            @endif
        </div>
    </div>
</article>