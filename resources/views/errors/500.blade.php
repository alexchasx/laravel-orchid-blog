@extends('layouts.techlog')

@php
    $metaTitle = '500 — Внутренняя ошибка сервера';
    $metaDesc = 'На сервере произошла непредвиденная ошибка.';
@endphp

@section('content')

@include('errors.template', [
    'code' => '500',
    'statusText' => 'INTERNAL SERVER ERROR',
    'title' => 'Внутренняя <em>ошибка сервера.</em>',
    'lead' => 'На сервере произошла непредвиденная ошибка.',
    'statusPhrase' => 'Internal Server Error',
    'statusKey' => 'server_error',
    'resolution' => 'try_again',
    'description' => 'Что-то пошло не так на нашей стороне. Мы уже знаем об этом и работаем над исправлением — попробуйте зайти чуть позже.',
])

@endsection