@props(['article' => null])

{{ session('message') }}

<x-form {{ $attributes }}>
    <x-form-item>
        <x-label required>
            {{ __('Название поста') }}
        </x-label>
        <x-input name="title" autofocus />
        <x-error name="title" />

    </x-form-item>

    <x-form-item>
        <x-label required>
            {{ __('Содержание поста') }}
        </x-label>
        {{--
        <x-textarea name="content" rows="10" /> --}}

        <x-trix name="content" value="{{ $article->content ?? '' }}" />
        <x-error name="content" />

    </x-form-item>

    {{ $slot }}
</x-form>
