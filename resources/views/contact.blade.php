@extends('layouts.techlog')

@section('content')

<section class="contact container contact--compact">
    {{-- Contact Intro --}}
    <div class="contact-intro reveal">
        <!-- <h1>Давайте обсудим<br><em>технологии</em></h1> -->
        <p>Есть тема для статьи, идея сотрудничества или нашли неточность? Напишите</p>
        @if(config('my_config.contact_email') || config('my_config.my_github'))
            {{-- Контакты берутся из .env (CONTACT_EMAIL, MY_GITHUB), см. config/my_config.php --}}
            <div class="contact-links">
                @if(config('my_config.contact_email'))
                    <a href="mailto:{{ config('my_config.contact_email') }}">{{ config('my_config.contact_email') }}</a>
                @endif
            </div>
            <br />
        @endif
    </div>

    {{-- Contact Form --}}
    <form class="contact-form reveal" data-contact-form action="{{ route('contact.store') }}" method="POST">
        @csrf

        @auth
            <input type="hidden" name="name" value="{{ auth()->user()->name }}">
            <input type="hidden" name="email" value="{{ auth()->user()->email }}">
        @else
            <label for="name">
                Имя
                <input id="name" name="name" type="text" autocomplete="name" placeholder="Как к вам обращаться" required minlength="2">
            </label>

            <label for="contact-email">
                Email
                <input id="contact-email" name="email" type="email" autocomplete="email" placeholder="you@example.com" required>
            </label>
        @endauth

        <label for="message">
            <div class="form-label">Сообщение</div>
            <textarea id="message" name="message" rows="7" placeholder="Расскажите, чем можем помочь…" required minlength="10"></textarea>
        </label>

        <p class="form-privacy">
            Отправляя форму, вы соглашаетесь с <a href="{{ route('privacy') }}">Политикой конфиденциальности</a>.
        </p>
        <button class="button primary" type="submit">Отправить сообщение →</button>
        <p class="form-message" aria-live="polite"></p>
    </form>
</section>

@endsection
