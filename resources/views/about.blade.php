@extends('layouts.techlog')

@section('content')

<section class="article container ">
    <div class="article-head reveal">
        <a class="back" href="{{ route('home') }}">← На главную</a>

        <h1>О блоге</em></h1>
    </div>

    <div class="prose">
        <p>{{ config('app.name') }} — независимый блог о том, как создавать и поддерживать веб-приложения. Здесь важны контекст, проверяемость и цена каждого технического решения.</p>
        <p>Материалы написаны для разработчиков, тимлидов и инженеров, которым нужно не просто узнать «как», а понять «почему».</p>
        <p class="text-muted small">
            Проект построен на шаблоне
            <a href="https://github.com/alexchasx/laravel-orchid-blog" target="_blank" rel="noopener noreferrer">
                laravel-orchid-blog
            </a>.
            Поддержите развитие шаблона: поставьте ⭐ на GitHub.
        </p>

        <div>
        <!-- <div class="callout"> -->
            <p>Хотите пообщаться? Напишите нам через <b><a href="{{ route('contact') }}">форму обратной связи</a></b> или подпишитесь на рассылку новых статей.</p>
        </div>
    </div>
</section>

@endsection
