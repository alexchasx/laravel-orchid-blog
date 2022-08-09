@extends('layouts.base')

@php
$metaTitle = '502 Bad Gateway';
$metaDesc = __('Плохой шлюз.')
@endphp


@section('content')

@include('errors.template', compact('metaTitle', 'metaDesc'))

@endsection
