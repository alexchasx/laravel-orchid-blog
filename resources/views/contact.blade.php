@extends('layouts.base')

@section('content')

@if (session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@elseif(session('error'))
<div class="alert alert-error">
    {{ session('error') }}
</div>
@endif

@guest

<p>
    Добро пожаловать на мой блог. Меня зовут Александр Часовников. 
    Я публикую здесь свои шпаргалки и конспекты, связанные с веб-разработкой. 
    На это странице вы можете оставить мне сообщение, если <a href="{{ route('login') }}" class="link login">
        <span>{{ 'авторизуетесь.' }}</span>
    </a> Или пишите письмо на 
    <a rel="nofollow" href="mailto:alexchasx@yandex.ru" class="link">alexchasx@yandex.ru</a>.
</p>

@else

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-body">

                    <form action="{{ route('contact.store') }}" id="contact-form" method="POST" class="form-horizontal" role="form">
                        @csrf

                        <div class="form-group{{ $errors->has('title') ? ' has-error' : '' }}">
                            <label for="title" class="col-md-4 control-label">Тема</label>
                            <div class="col-md-8">
                                <input id="title" type="title" class="form-control contact" name="title" value="{{ old('title') }}" required @if ($errors->has('name') || !$errors) autofocus @endif>
                                @if ($errors->has('title'))
                                <div class="help-block">
                                    <strong>{{ $errors->first('title') }}</strong>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('message') ? ' has-error' : '' }}">
                            <label for="message" class="col-md-4 control-label">Сообщение (обязательно)</label>
                            <div class="col-md-6">
                                <textarea name="message" id="message" rows="20" required @if ($errors->has('name')) autofocus @endif>
                                            {{ old('message') }}
                                        </textarea>
                                @if ($errors->has('message'))
                                <div class="help-block">
                                    <strong>{{ $errors->first('message') }}</strong>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-8 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    Отправить
                                </button>
                            </div>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

@endguest
{{-- <br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br /> --}}

@endsection
