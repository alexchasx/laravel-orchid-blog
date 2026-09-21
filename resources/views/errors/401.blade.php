@extends('layouts.techlog')

@php
    $metaTitle = '401 — Требуется авторизация';
    $metaDesc = 'Для доступа к этой странице необходимо войти в аккаунт.';
@endphp

@section('content')

@include('errors.template', [
    'code' => '401',
    'statusText' => 'UNAUTHORIZED',
    'title' => 'Требуется <em>авторизация.</em>',
    'lead' => 'Эта страница доступна только авторизованным пользователям.',
    'statusPhrase' => 'Unauthorized',
    'statusKey' => 'auth_required',
    'resolution' => 'login_first',
    'description' => 'У вас нет прав на просмотр этого раздела. Войдите в аккаунт и попробуйте снова — возможно, контент станет доступен.',
    'primaryUrl' => route('login'),
    'primaryText' => 'Войти',
    'ghostUrl' => route('home'),
    'ghostText' => 'На главную',
])

@endsection