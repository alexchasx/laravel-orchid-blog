@if($alert = session()->pull('alert'))
<div class="alert alert-{{ session()->pull('type') }} mb-0 rounded-0 text-center small py-2">
    {{ $alert }}
    <br>
    Алерт
</div>
@endif

@if (!empty($message))
    <div class="alert alert-danger text-center">
        {{$message}}
    </div>
@endif
