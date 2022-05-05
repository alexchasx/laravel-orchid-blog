@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Подтверждение регистрации</div>
                <div class="panel-body">
                    Ваша электронная почта успешно проверена. Нажмите здесь, чтобы <a href="{{url('/login')}}">войти</a>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection