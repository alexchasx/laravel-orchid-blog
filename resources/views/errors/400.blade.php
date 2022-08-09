@extends('layouts.base')

@php
$metaTitle = '400 Bad Request';
$metaDesc = __('Плохой запрос.')
@endphp


@section('content')

@include('errors.template', compact('metaTitle', 'metaDesc'))

@endsection
