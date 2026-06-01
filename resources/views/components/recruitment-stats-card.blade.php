@props(['title', 'count', 'subtitle', 'color', 'icon'])

@php
    $bgGradient = [
        'emerald' => 'bg-emerald-500/5',
        'indigo' => 'bg-indigo-500/5',
        'amber' => 'bg-amber-500/5',
        'red' => 'bg-red-500/5',
    ][$color] ?? 'bg-slate-500/5';

    $textClass = [
        'emerald' => 'text-emerald-600',
        'indigo' => 'text-indigo-600',
        'amber' => 'text-amber-600',
        'red' => 'text-red-600',
    ][$color] ?? 'text-slate-600';

    $iconBg = [
        'emerald' => 'bg-emerald-50 text-emerald-500 border-emerald-100',
        'indigo' => 'bg-indigo-50 text-indigo-500 border-indigo-100',
        'amber' => 'bg-amber-50 text-amber-500 border-amber-100',
        'red' => 'bg-red-50 text-red-500 border-red-100',
    ][$color] ?? 'bg-slate-50 text-slate-500 border-slate-100';
@endphp

<div class="bg-white border border-slate-200/80 p-5 rounded-2xl flex items-center justify-between hover:shadow-md transition duration-300 relative overflow-hidden group">
    <div class="absolute -top-10 -left-10 w-20 h-20 {{ $bgGradient }} rounded-full blur-xl group-hover:scale-155 transition duration-500"></div>
    <div>
        <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider block">{{ $title }}</span>
        <span class="text-2xl font-extrabold text-slate-800 mt-1 block">{{ $count }}</span>
        <span class="text-[10px] {{ $textClass }} font-semibold block mt-1">{!! $subtitle !!}</span>
    </div>
    <div class="w-12 h-12 {{ $iconBg }} border rounded-2xl flex items-center justify-center text-lg shadow-sm">
        <i class="fa-solid {{ $icon }}"></i>
    </div>
</div>
