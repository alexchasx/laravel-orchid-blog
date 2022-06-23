@extends('layouts.base')

@section('content-title', '404 Error Page')
@section('content-subtitle', '')

@section('content')

<div class="error-page">
    <h2 class="headline text-yellow"> Not Found (#404)</h2>

    <div class="error-content">
        <h3><i class="fa fa-warning text-yellow"></i>{{ __('Страница не найдена.') }}</h3>
    </div>

    <a href="{{ route('home') }}" class="link">{{ __('На главную') }}</a>

    @if (! Auth::guest())

    <p>{{ class_basename($exception->getPrevious() ? : $exception) }}</p>

    <p>{{ $exception->getPrevious() ? $exception->getPrevious()->getMessage()
        : $exception->getMessage() }}</p>

    @endif

    <!-- /.error-content -->
</div>
<!-- /.error-page -->

<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />

@endsection
