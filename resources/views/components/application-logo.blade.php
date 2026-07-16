@props(['class' => ''])

<img
    src="{{ asset('logo.png') }}"
    alt="{{ config('app.name', 'MontirGo') }}"
    {{ $attributes->merge(['class' => 'h-9 w-auto']) }}
/>
