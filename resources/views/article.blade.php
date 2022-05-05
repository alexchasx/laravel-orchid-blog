@extends('layouts.base')

@section('content')
@unless (empty($article))
<div class="article_card">
    <h3><a href="{{route('articleShow', ['id' => $article->id])}}" class="continue_read">

            {{ $article->title }}
        </a>
    </h3>

    <div class="publication_date">{{ $article->published_at }}</div>

    <p>{!! $article->content !!}</p>

</div>
<br />
<hr />
<br />
@endunless

@endsection
