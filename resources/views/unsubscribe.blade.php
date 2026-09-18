@extends('layouts.techlog')

@section('content')

<section class="section container unsubscribe reveal">
    <div class="section-head">
        <div>
            <p class="eyebrow">SIGNAL / OFF</p>
            <h2>{{ $title }}</h2>
        </div>
    </div>

    <div class="unsubscribe-body">
        <p class="form-message {{ $success ? 'success' : 'error' }}">{{ $message }}</p>
        <a class="button primary" href="{{ route('home') }}">Вернуться на главную <span>→</span></a>
    </div>
</section>

@endsection
