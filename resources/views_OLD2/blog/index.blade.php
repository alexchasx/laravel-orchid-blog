@extends('layouts.main')

@section('page.title', 'Наш блог')

@section('main.content')
<x-title>
    {{ __('Список постов') }}
</x-title>

@include('blog.filter')

@if(empty($articles))
{{ __('Нет постов') }}
@else
<div class="row">
    @foreach($articles as $article)
    <div class="col-12 col-md-4">
        <x-article.card :article="$article" />
    </div>
    @endforeach
</div>
@endif

@endsection
