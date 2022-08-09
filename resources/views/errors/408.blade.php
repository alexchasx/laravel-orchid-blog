@extends('layouts.base')

@php
$metaTitle = '408 Request Timeout';
$metaDesc = ''
@endphp


@section('content')

@include('errors.template', compact('metaTitle', 'metaDesc'))

@endsection
