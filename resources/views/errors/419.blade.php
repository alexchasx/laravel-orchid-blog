@extends('layouts.base')

@php
$metaTitle = '419';
$metaDesc = __('Страница устарела.')
@endphp


@section('content')

@include('errors.template', compact('metaTitle', 'metaDesc'))

@endsection
