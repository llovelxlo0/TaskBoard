@props([
    'variant' => 'default', // default, success, warning, info, danger, muted
])

@php
 $styles = match ($variant) {
    'success' => 'background:#e6ffed; color:#036b26; border:1px solid #b7ebc6;',
    'warning' => 'background:#fff7e6; color:#8a5a00; border:1px solid #ffe2a8;',
    'info'    => 'background:#e6f4ff; color:#0b5394; border:1px solid #b6daff;',
    'danger'  => 'background:#ffe6e6; color:#a40000; border:1px solid #ffb3b3;',
    'muted'   => 'background:#f3f4f6; color:#374151; border:1px solid #e5e7eb;',
    default   => 'background:#f5f5f5; color:#111827; border:1px solid #e5e7eb;',
 };
@endphp

<span {{ $attributes->merge(['style' => "display:inline-block; padding:2px 8px; border-radius:999px; font-size:12px; line-height:18px; {$styles}"])}}>
    {{ $slot }}
</span>
