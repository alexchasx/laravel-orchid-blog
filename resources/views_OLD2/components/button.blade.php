@props(['color' => 'primary'])

<button {{ $attributes->class([
    "btn btn-{$color}",
    ])->merge([
    'type' => 'button',
    ]) }}>
    {{ $slot }}
</button>
