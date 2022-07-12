@extends('layouts.base')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-body">
                    <!-- <p class="text-center">
                    <div class="wrap_img">
                        {{ __('Если у вас есть предложения или вопросы, то отправьте мне письмо на') }}
                        <strong>{{ env('CONTACT_EMAIL') }}</strong>
                        {{-- или используйте форму: --}}
                        <br />
                        <br />
                        <img {{-- style="max-width: 700px" --}} class="img_contact" src="{{ asset('images/banner.jpg') }}" alt="image">
                    </div>
                    </p>
                    <br /> -->

                    @if (empty($success))

                    <form action="{{ route('contact.store') }}" id="contact-form" method="POST" class="form-horizontal" role="form">
                        @csrf

                        <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                            <label for="name" class="col-md-4 control-label">Ваше имя (обязательно)</label>
                            <div class="col-md-6">
                                <input id="name" type="name" class="form-control contact" name="name" value="{{ old('name') }}" required @if ($errors->has('name') || !$errors) autofocus @endif>
                                @if ($errors->has('email'))
                                <div class="help-block">
                                    <strong>{{ $errors->first('name') }}</strong>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email" class="col-md-4 control-label">Ваш e-mail (обязательно)</label>
                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control contact" name="email" value="{{ old('email') }}" required @if ($errors->has('name') || !$errors) autofocus @endif>
                                @if ($errors->has('email'))
                                <div class="help-block">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </div>
                                @endif
                            </div>
                        </div>

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
                            <label for="message" class="col-md-4 control-label">Сообщение:</label>
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
                    @else
                    {{ $success }} &nbsp;&nbsp; <button><a href="/">На главную</a></button>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
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
