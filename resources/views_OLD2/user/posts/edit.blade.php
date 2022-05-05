@extends('layouts.main')

@section('page.title', 'Изменить пост')

@section('main.content')

<x-title>
    {{ __('Изменить пост') }}

    <x-slot name="link">
        <x-button-link href="{{ route('user.posts.show', $post->id) }}">
            {{ __('Назад') }}
        </x-button-link>
    </x-slot>
</x-title>

<x-post.form action="{{ route('user.posts.update', $post->id) }}" method="put" :post="$post">
    <x-button type="submit">
        {{ __('Сохранить') }}
    </x-button>
</x-post.form>

@endsection
