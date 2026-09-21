@extends('layouts.techlog')

@php
    $metaTitle = '503 — Сервис недоступен';
    $metaDesc = 'Сервер временно не может обработать запрос.';
@endphp

@section('content')

@include('errors.template', [
    'code' => '503',
    'statusText' => 'SERVICE UNAVAILABLE',
    'title' => 'Сервис <em>недоступен.</em>',
    'lead' => 'Сервер временно не может обработать запрос.',
    'statusPhrase' => 'Service Unavailable',
    'statusKey' => 'service_unavailable',
    'resolution' => 'retry_later',
    'description' => 'Возможно, идут технические работы или нагрузка временно слишком высокая. Загляните к нам немного позже.',
])

@endsection