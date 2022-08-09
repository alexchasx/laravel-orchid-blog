@extends('layouts.base')

@php
$metaTitle = '504 Gateway Timeout';
$metaDesc = ''
@endphp


@section('content')

@include('errors.template', compact('metaTitle', 'metaDesc'))

@endsection
