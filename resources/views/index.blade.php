@extends('layouts.base')

@section('content')

{{-- {{ memory_get_usage() }} --}}

@if (!empty($articles[0]))

    @foreach ($articles as $article)

        <article class="article_card article_card_index">
            <h3 class="article_title"><a href="{{route('articleShow', $article->id)}}" class="continue_read">
                    {{ $article->title }}
                </a>
            </h3>

            @include('includes/publication_date')

            <!-- <p>{{ Str::limit($article->excert, 200) }}</p> -->

            <!-- <a href="{{route('articleShow', $article->id)}}" class="continue_read">
            {{ __('Читать далее') }}
            </a> -->
        </article>

        @php
            $article = null; // влияет на работу мета-тегов
        @endphp

    @endforeach

    <nav>
        <ul class="pagination">
            {{ $articles->links('vendor.pagination.default') }}
        </ul>
    </nav>

@else

    <div class="article_card">
        {{ __('Ничего не нашлось') }}
    </div>

@endif


@endsection
