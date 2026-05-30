@extends('layouts.careers')

@section('content')
<div class="space-y-8">

    <!-- Welcome Hero Section -->
    <div class="glass-card p-8 rounded-3xl bg-white relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="absolute -top-10 -right-10 w-40 h-40 bg-blue-500/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="space-y-3 max-w-xl text-center md:text-left">
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-800">Build Your Career With Us</h1>
            <p class="text-sm text-slate-500 leading-relaxed">
                Explore open positions, join our hyper-growth operations team, and build state-of-the-art products. We look for passionate, dedicated minds.
            </p>
        </div>
        <div class="flex items-center gap-4 text-xs font-mono">
            <span class="px-3 py-1.5 rounded-full bg-blue-500/10 text-blue-600 border border-blue-500/20 font-bold uppercase">
                {{ $jobs->count() }} Open Roles
            </span>
        </div>
    </div>

    <!-- Category Filters -->
    @if($categories->isNotEmpty())
    <div class="flex items-center gap-2 overflow-x-auto pb-2">
        <span class="text-xs font-bold text-slate-500 mr-2">Categories:</span>
        <button onclick="filterCategory('all')" id="cat-btn-all" class="px-3.5 py-1.5 rounded-full text-xs font-semibold bg-blue-600 text-white border border-transparent shadow shadow-blue-600/35 transition">
            All Categories
        </button>
        @foreach($categories as $category)
        <button onclick="filterCategory('{{ $category->slug }}')" id="cat-btn-{{ $category->slug }}" class="px-3.5 py-1.5 rounded-full text-xs font-semibold bg-white text-slate-600 border border-slate-200 hover:border-slate-400 transition">
            {{ $category->name }}
        </button>
        @endforeach
    </div>
    @endif

    <!-- Jobs Roster -->
    <div class="grid grid-cols-1 gap-5" id="jobs-grid">
        @if($jobs->isEmpty())
        <div class="glass-card p-12 text-center text-slate-500 rounded-3xl flex flex-col items-center justify-center gap-3">
            <i class="fa-solid fa-face-smile text-3xl text-slate-300"></i>
            <span class="text-sm font-semibold">There are currently no active openings. Check back later!</span>
        </div>
        @else
            @foreach($jobs as $job)
            <div class="glass-card p-6 rounded-2xl bg-white hover:border-blue-400 hover:shadow-lg hover:shadow-blue-500/5 transition duration-300 flex flex-col md:flex-row md:items-center justify-between gap-6 job-card" data-category="{{ $job->jobCategory ? $job->jobCategory->slug : 'uncategorized' }}">
                <div class="space-y-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-[10px] font-bold text-blue-600 bg-blue-500/10 px-2.5 py-0.5 rounded-full font-mono uppercase tracking-wider">
                            {{ $job->department->name }}
                        </span>
                        @if($job->jobCategory)
                        <span class="text-[10px] font-bold text-indigo-600 bg-indigo-500/10 px-2.5 py-0.5 rounded-full font-mono uppercase tracking-wider">
                            {{ $job->jobCategory->name }}
                        </span>
                        @endif
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">{{ $job->title }}</h3>
                    <div class="flex items-center gap-4 text-xs text-slate-500 font-mono">
                        <span><i class="fa-solid fa-briefcase mr-1 text-slate-400"></i> {{ $job->experience_required }}</span>
                        <span><i class="fa-solid fa-wallet mr-1 text-slate-400"></i> {{ $job->salary_range }}</span>
                    </div>
                </div>
                <div>
                    <a href="{{ route('careers.show', $job) }}" class="inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow shadow-indigo-600/35 transition duration-200 gap-2">
                        View Details <i class="fa-solid fa-angle-right"></i>
                    </a>
                </div>
            </div>
            @endforeach
        @endif
    </div>

    <!-- Dynamic Filtering Script -->
    <script>
        function filterCategory(slug) {
            // Update active buttons styles
            const buttons = document.querySelectorAll('[id^="cat-btn-"]');
            buttons.forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white', 'border-transparent', 'shadow', 'shadow-blue-600/35');
                btn.classList.add('bg-white', 'text-slate-600', 'border-slate-200');
            });

            const activeBtn = document.getElementById('cat-btn-' + slug);
            if (activeBtn) {
                activeBtn.classList.remove('bg-white', 'text-slate-600', 'border-slate-200');
                activeBtn.classList.add('bg-blue-600', 'text-white', 'border-transparent', 'shadow', 'shadow-blue-600/35');
            }

            // Filter jobs
            const cards = document.querySelectorAll('.job-card');
            cards.forEach(card => {
                if (slug === 'all' || card.getAttribute('data-category') === slug) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>

</div>
@endsection
