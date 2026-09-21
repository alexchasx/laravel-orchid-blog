@extends('layouts.techlog')

@php
    $metaTitle = '503 — Технические работы';
    $metaDesc = 'Мы проводим плановое обслуживание и скоро вернёмся.';
@endphp

@section('content')

@include('errors.template', [
    'code' => '503',
    'statusText' => 'MAINTENANCE',
    'title' => 'Технические <em>работы.</em>',
    'lead' => 'Мы проводим плановое обслуживание и скоро вернёмся.',
    'statusPhrase' => 'Service Temporarily Unavailable',
    'statusKey' => 'maintenance_mode',
    'resolution' => 'come_back_later',
    'description' => 'Сайт временно недоступен из-за плановых работ. Это не займёт много времени — возвращайтесь чуть позже.',
])

@endsection