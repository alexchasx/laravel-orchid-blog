@extends('layouts.main')

@section('page.title', $article->title)

@section('main.content')
<x-title>
    {{ $article->title }}

    <x-slot name="link">
        <a href="{{ route('blog') }}">
            {{ __('Назад') }}
        </a>
    </x-slot>
</x-title>

{!! $article->content !!}

@endsection
