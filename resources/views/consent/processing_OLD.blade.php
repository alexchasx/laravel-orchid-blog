@extends('layouts.techlog')

@section('content')

<section class="article container legal reveal">
    <div class="article-head">
        <a class="back" href="{{ route('home') }}">← На главную</a>
        <h1>{{ $title }}</h1>
        <p class="lead">Текст согласия версии {{ $version }}. Дата вступления в силу: {{ $effectiveDate }}.</p>
    </div>

    <div class="article-layout">
        <div class="prose">
            {!! $text !!}
        </div>
    </div>

    <div class="text-center" style="margin-top: 2rem;">
        <a href="{{ route('home') }}" class="btn">Вернуться на главную</a>
    </div>
</section>

@endsection
