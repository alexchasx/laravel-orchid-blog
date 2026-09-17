@extends('layouts.techlog')

@section('content')

<section class="contact container">
    {{-- Contact Intro --}}
    <div class="contact-intro reveal">
        <p class="eyebrow">CONTACT / 01</p>
        <h1>Давайте обсудим<br><em>технологии.</em></h1>
        <p>Есть тема для статьи, идея сотрудничества или нашли неточность? Напишите — читаем каждое сообщение.</p>
        <div class="contact-links">
            <a href="mailto:mail@yandex.ru">mail@yandex.ru</a>
            <a href="https://github.com/alexchasx" target="_blank" rel="noopener">github.com/alexchasx</a>
        </div>
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
                <input id="name" name="name" type="text" autocomplete="name" placeholder="Алексей" required minlength="2">
            </label>

            <label for="contact-email">
                Email
                <input id="contact-email" name="email" type="email" autocomplete="email" placeholder="you@example.com" required>
            </label>
        @endauth

        <label for="message">
            Сообщение
            <textarea id="message" name="message" rows="7" placeholder="Расскажите, чем можем помочь…" required minlength="10"></textarea>
        </label>

        <button class="button primary" type="submit">Отправить сообщение →</button>
        <p class="form-message" aria-live="polite"></p>
    </form>
</section>

@endsection
