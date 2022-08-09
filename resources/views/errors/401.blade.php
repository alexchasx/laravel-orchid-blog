@extends('layouts.base')

@php
$metaTitle = '401 Unauthorized';
$metaDesc = __('Неавторизованно.')
@endphp


@section('content')

@include('errors.template', compact('metaTitle', 'metaDesc'))

@endsection
