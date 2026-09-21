@extends('layouts.techlog')

@php
    $metaTitle = '419 — Страница устарела';
    $metaDesc = 'Сессия истекла, форма или страница устарела.';
@endphp

@section('content')

@include('errors.template', [
    'code' => '419',
    'statusText' => 'PAGE EXPIRED',
    'title' => 'Сессия <em>истекла.</em>',
    'lead' => 'Страница устарела из-за длительного простоя.',
    'statusPhrase' => 'Page Expired',
    'statusKey' => 'session_expired',
    'resolution' => 'refresh_page',
    'description' => 'Обычно это происходит, когда форма была открыта слишком долго или токен безопасности устарел. Обновите страницу и повторите действие.',
])

@endsection