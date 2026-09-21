@extends('layouts.techlog')

@php
    $metaTitle = '400 — Плохой запрос';
    $metaDesc = 'Сервер не смог обработать запрос из-за некорректных данных.';
@endphp

@section('content')

@include('errors.template', [
    'code' => '400',
    'statusText' => 'BAD REQUEST',
    'title' => 'Некорректный <em>запрос.</em>',
    'lead' => 'Сервер не смог понять ваш запрос — проверьте адрес и введённые данные.',
    'statusPhrase' => 'Bad Request',
    'statusKey' => 'bad_request',
    'resolution' => 'check_input',
    'description' => 'Запрос был составлен неправильно или нарушает ожидаемый формат. Проверьте корректность данных в адресной строке или форме и попробуйте ещё раз.',
])

@endsection