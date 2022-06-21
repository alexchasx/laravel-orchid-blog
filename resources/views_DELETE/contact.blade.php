@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <br/>
                <br/>
                <br/>
                <div class="panel panel-default">
                    <div class="panel-body">
                        <p class="text-center">&nbsp;&nbsp;&nbsp;&nbsp;Здравствуйте, меня зовут
                            Часовников Александр.
                            Я - автор блога BeOnTopic.
                            Если у вас есть предложения или вопросы, то отправьте мне сообщение на
                            <strong>aleksandr.chasovnikov@gmail.com</strong> или используйте форму:
                        </p>
                        <br/>

                        @if (empty($success))

                            <form action="{{ route('simplePHPEmail') }}" id="contact-form"
                                  method="POST" class="form-horizontal" role="form">
                                {{ csrf_field() }}
                                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">

                                    <label for="email" class="col-md-4 control-label">Ваш
                                        e-mail</label>

                                    <div class="col-md-6">
                                        <input id="email" type="email" class="form-control"
                                               name="email" value="{{ old('email') }}" required
                                               @if ($errors->has('name') || !$errors) autofocus @endif>

                                        @if ($errors->has('email'))
                                            <div class="help-block">
                                                <strong>{{ $errors->first('email') }}</strong></div>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group{{ $errors->has('message') ? ' has-error' : '' }}">
                                    <label for="message"
                                           class="col-md-4 control-label">Сообщение:</label>

                                    <div class="col-md-6">
                                        <textarea style="border-color: #2e3436;" name="message"
                                                  id="message" rows="10"
                                                  required
                                                  @if ($errors->has('name')) autofocus @endif> {{ old('message') }} </textarea>

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
    <br/>
    <br/>
    <br/>
    <br/>
    <br/>
    <br/>
    <br/>
    <br/>
    <br/>
    <br/>

@endsection