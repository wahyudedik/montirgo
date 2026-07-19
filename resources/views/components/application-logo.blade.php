@props(['class' => ''])

<img
    src="{{ asset('logo-rm.png') }}"
    alt="{{ config('app.name', 'MontirGo') }}"
    {{ $attributes->merge(['class' => 'h-9 w-auto']) }}
/>
