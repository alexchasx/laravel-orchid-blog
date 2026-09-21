@extends('layouts.techlog')

@php
    $metaTitle = '504 — Превышено время ожидания шлюза';
    $metaDesc = 'Промежуточный сервер слишком долго отвечал на запрос.';
@endphp

@section('content')

@include('errors.template', [
    'code' => '504',
    'statusText' => 'GATEWAY TIMEOUT',
    'title' => 'Шлюз не <em>ответил вовремя.</em>',
    'lead' => 'Промежуточный сервер слишком долго отвечал на запрос.',
    'statusPhrase' => 'Gateway Timeout',
    'statusKey' => 'gateway_timeout',
    'resolution' => 'retry_later',
    'description' => 'Запрос не успел пройти через промежуточные сервисы. Это временная проблема — повторите попытку через пару минут.',
])

@endsection