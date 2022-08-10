@extends('layouts.base')

@php
$metaTitle = '429 Too Many Requests';
$metaDesc = __('Страница не найдена.')
@endphp


@section('content')

@include('errors.template', compact('metaTitle', 'metaDesc'))

@endsection
