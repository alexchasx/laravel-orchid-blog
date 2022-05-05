@props(['method' => 'GET'])

@php($method = strtoupper($method))
@php($checkMethod = in_array($method, ['GET', 'POST']))

<form {{ $attributes }} method="{{ $checkMethod ? $method : 'POST' }}">
    @unless($checkMethod)
    @method($method)
    @endunless

    @if ($method !== 'GET')
    @csrf
    @endif

    {{ $slot }}

</form>
