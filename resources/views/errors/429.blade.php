@extends('layouts.techlog')

@php
    $metaTitle = '429 — Слишком много запросов';
    $metaDesc = 'Вы отправляете запросы слишком часто.';
@endphp

@section('content')

@include('errors.template', [
    'code' => '429',
    'statusText' => 'TOO MANY REQUESTS',
    'title' => 'Слишком много <em>запросов.</em>',
    'lead' => 'Вы отправляете запросы быстрее, чем сервер успевает их обрабатывать.',
    'statusPhrase' => 'Too Many Requests',
    'statusKey' => 'rate_limited',
    'resolution' => 'slow_down',
    'description' => 'Чтобы защитить сервер от перегрузки, есть лимит на частоту запросов. Подождите немного и повторите попытку.',
    'primaryUrl' => url()->current(),
    'primaryText' => 'Обновить страницу',
])

@endsection