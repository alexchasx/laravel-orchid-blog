@extends('layouts.base')

@php
$metaTitle = '503 Service Unavailable';
$metaDesc = __('Сервис недоступен.')
@endphp


@section('content')

@include('errors.template', compact('metaTitle', 'metaDesc'))

@endsection
