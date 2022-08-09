@extends('layouts.base')

@php
$metaTitle = '503 Сервис недоступен.';
$metaDesc = __('Извините, мы проводим кое-какие ремонтные работы. Пожалуйста, приходите попозже.')
@endphp


@section('content')

@include('errors.template', compact('metaTitle', 'metaDesc'))

@endsection
