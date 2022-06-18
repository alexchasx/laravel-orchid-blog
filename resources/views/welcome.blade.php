@extends('layouts.app')

@section('title', 'Главная')

@section('content')

<div class="content">

    @if (!empty($articles[0]))

    <div class="rubric-title">
        <h2>Все статьи</h2>
        <!-- <p>Приятного чтения!</p> -->
    </div>

    <ul>

        @foreach ($articles as $article)
        <li class="article-card">
            <h2 class="reverse-effect">
                <a href="{{route('articleShow', ['id' => $article->id])}}" class="article-title">
                    <div data-hover="{{ $article->title }}">{{ $article->title }}</div>
                </a>
            </h2>
            <div class="article-date">16 января 2021</div>
        </li>
        @endforeach

    </ul>


    @else
    <div class="article_card">
        {{ __('Ничего не нашлось') }}
    </div>
    @endif
    <nav>
        <ul class="pagination">
            pagination
        </ul>
    </nav>

</div>

@endsection
