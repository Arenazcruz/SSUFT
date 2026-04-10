@props(['status'])

@php
    $palette = [
        'en_vivo' => 'border-red-200 bg-red-50 text-red-700',
        'programada' => 'border-amber-200 bg-amber-50 text-amber-700',
        'finalizada' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
    ];

    $labels = [
        'en_vivo' => 'En vivo',
        'programada' => 'Programada',
        'finalizada' => 'Finalizada',
    ];
@endphp

<span {{ $attributes->class([
    'inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold',
    $palette[$status] ?? 'border-slate-200 bg-slate-50 text-slate-700',
]) }}>
    {{ $labels[$status] ?? ucfirst((string) $status) }}
</span>
