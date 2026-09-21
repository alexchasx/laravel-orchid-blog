@extends('layouts.techlog')

@php
    $metaTitle = '408 — Превышено время ожидания';
    $metaDesc = 'Сервер не успел ответить на запрос вовремя.';
@endphp

@section('content')

@include('errors.template', [
    'code' => '408',
    'statusText' => 'REQUEST TIMEOUT',
    'title' => 'Сервер не <em>ответил вовремя.</em>',
    'lead' => 'Запрос занял слишком много времени и был отменён.',
    'statusPhrase' => 'Request Timeout',
    'statusKey' => 'request_timeout',
    'resolution' => 'retry_later',
    'description' => 'Соединение прервано из-за долгого ожидания ответа. Скорее всего, это временная проблема сети — просто попробуйте ещё раз.',
])

@endsection