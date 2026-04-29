@props(['active'])

@php
$classes = ($active ?? false)
            ? 'portal-mobile-link portal-mobile-link-active block w-full text-start'
            : 'portal-mobile-link block w-full text-start';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
