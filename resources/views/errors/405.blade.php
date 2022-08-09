@extends('layouts.base')

@php
$metaTitle = '405 Method Not Allowed';
$metaDesc = __('Метод не разрешён.')
@endphp


@section('content')

@include('errors.template', compact('metaTitle', 'metaDesc'))

@endsection
