@extends('layouts.techlog')

@php
    $metaTitle = '404 — Страница не найдена';
    $metaDesc = 'Страница не найдена. Возможно, она перемещена или никогда не существовала.';
@endphp

@section('content')

@include('errors.template', [
    'code' => '404',
    'statusText' => 'NOT FOUND',
    'title' => 'Страница <em>не найдена.</em>',
    'lead' => 'Запрошенный адрес не существует или ресурс был перемещён. Проверьте ссылку или вернитесь на главную.',
    'statusPhrase' => 'Not Found',
    'statusKey' => 'page_missing',
    'resolution' => 'try_home',
    'description' => 'Возможно, страница была удалена, переехала на новый адрес или вы ошиблись в URL. Рекомендуем начать с главной — там всегда свежие материалы.',
])

@endsection