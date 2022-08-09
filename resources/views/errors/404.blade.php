@extends('layouts.base')

@php
$metaTitle = '404 Not Found';
$metaDesc = __('Страница не найдена.')
@endphp


@section('content')

@include('errors.template', compact('metaTitle', 'metaDesc'))

@endsection
