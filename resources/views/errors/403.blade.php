@extends('layouts.app')

@section('content-title', '403 Error Page')
@section('content-subtitle', '')

@section('content')
<div class="error-page" style="text-align: center;">
    <h2 class="headline text-yellow">Forbidden (#403)</h2>

    <div class="error-content">
        <h3><i class="fa fa-warning text-yellow"></i> {{ __('Нет доступа!') }} {{-- class_basename($exception->getPrevious() ? : $exception) --}}</h3>

        <p>{{ $exception->getPrevious() ? $exception->getPrevious()->getMessage() : $exception->getMessage() }}</p>

    </div>
    <!-- /.error-content -->
</div>
<!-- /.error-page -->

<br />

@endsection
