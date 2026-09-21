@extends('layouts.techlog')

@php
    $metaTitle = '502 — Плохой шлюз';
    $metaDesc = 'Сервер получил некорректный ответ от вышестоящего сервиса.';
@endphp

@section('content')

@include('errors.template', [
    'code' => '502',
    'statusText' => 'BAD GATEWAY',
    'title' => 'Плохой <em>шлюз.</em>',
    'lead' => 'Сервер получил некорректный ответ от вышестоящего сервиса.',
    'statusPhrase' => 'Bad Gateway',
    'statusKey' => 'bad_gateway',
    'resolution' => 'retry_in_moment',
    'description' => 'Промежуточный сервер не смог получить корректный ответ. Обычно это временная ошибка — обновите страницу через несколько секунд.',
])

@endsection