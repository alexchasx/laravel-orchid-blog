@extends('layouts.techlog')

@php
    $metaTitle = '403 — Доступ запрещён';
    $metaDesc = 'У вас нет прав для просмотра этой страницы.';
@endphp

@section('content')

@include('errors.template', [
    'code' => '403',
    'statusText' => 'FORBIDDEN',
    'title' => 'Доступ <em>запрещён.</em>',
    'lead' => 'У вас нет прав на просмотр этой страницы.',
    'statusPhrase' => 'Forbidden',
    'statusKey' => 'access_denied',
    'resolution' => 'try_another',
    'description' => 'Сервер понял запрос, но отказывается его выполнять. Если вы уверены, что доступ должен быть открыт — обратитесь к администратору блога.',
])

@endsection