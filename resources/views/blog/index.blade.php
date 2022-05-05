@extends('layouts.main')

@section('page.title', 'Наш блог')

@section('main.content')
{{-- <x-title>
    {{ __('Список постов') }}
</x-title> --}}

{{-- @include('blog.filter') --}}

@if(empty($articles))
{{ __('Нет постов') }}
@else


@foreach($articles as $article)
<div class="article_card">
    <h3>{{ $article->title }}</h3>
    <div class="publication_date">{{ $article->published_at }}</div>

    <p>
        {{ $article->description }}
    </p>
    <a href="{{ route('articleShow', $article->id) }}" class="continue_read">Читать далее</a>

</div>
<br />
@endforeach

@endif

@endsection
