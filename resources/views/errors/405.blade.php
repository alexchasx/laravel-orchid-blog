@extends('layouts.techlog')

@php
    $metaTitle = '405 — Метод не разрешён';
    $metaDesc = 'Запрос выполнен недопустимым HTTP-методом.';
@endphp

@section('content')

@include('errors.template', [
    'code' => '405',
    'statusText' => 'METHOD NOT ALLOWED',
    'title' => 'Метод <em>не разрешён.</em>',
    'lead' => 'Этот адрес не поддерживает такой HTTP-метод запроса.',
    'statusPhrase' => 'Method Not Allowed',
    'statusKey' => 'method_not_allowed',
    'resolution' => 'use_get',
    'description' => 'Сервер не поддерживает такой метод запроса для этого адреса. Проверьте URL и повторите запрос — например, GET или POST в зависимости от страницы.',
])

@endsection