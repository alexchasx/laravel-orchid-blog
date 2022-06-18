@extends('layouts.base')

@section('title', 'Главная')

@section('content')

@if (!empty($articles[0]))

@foreach ($articles as $article)

<div class="article_card">
    <h3><a href="{{route('articleShow', ['id' => $article->id])}}" class="continue_read">

            @if (! Auth::guest() && isAdmin())
            [ID={{ $article->id }}]
            @endif

            {{ $article->title }}
        </a>
    </h3>

    <div class="publication_date">{{ $article->published_at }}</div>

    <p>{!! $article->excert !!}</p>

    <a href="{{route('articleShow', ['id' => $article->id])}}" class="continue_read">
        {{ __('Читать далее') }}
    </a>
</div>

@unless ($loop->last) {{-- не показывать последнюю полосу --}}
<br />
<hr />
<br />
@endunless

@endforeach

@else
<div class="article_card">
    {{ __('Ничего не нашлось') }}
</div>
@endif
<nav>
    <ul class="pagination">
        {{ $articles->links('vendor.pagination.default') }}
    </ul>
</nav>

@endsection
