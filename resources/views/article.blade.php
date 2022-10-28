@extends('layouts.base')

@section('content')

@unless (empty($article))

<article class="article_card">

    @include('includes/publication_date')
    <br>

    <p>{!! $article->content_raw !!}</p>

    <br>
</article>
<br />

@include('includes/comments_list')

<hr>

@include('includes/comments_form')

@endunless

@push('scripts')
<script type="text/javascript">
</script>
@endpush

@endsection
