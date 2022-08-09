@extends('layouts.base')

@php
$metaTitle = '500 Internal Server Error';
$metaDesc = __('Внутренняя ошибка сервера.')
@endphp


@section('content')

@include('errors.template', compact('metaTitle', 'metaDesc'))

@endsection
