@props(['topCandidates'])

<div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
        <div>
            <h2 class="text-sm font-bold text-slate-800 tracking-wide uppercase">Top Ranked AI Matches</h2>
            <p class="text-[10px] text-slate-400 mt-0.5">Top candidate submissions sorted by real-time AI matching percentage.</p>
        </div>
    </div>
    
    <div class="mt-4 overflow-x-auto">
        @if($topCandidates->isEmpty())
        <div class="h-64 flex flex-col items-center justify-center text-slate-400 text-xs gap-2">
            <i class="fa-solid fa-address-book text-3xl text-slate-300"></i>
            <span>No highly matched candidates found. Submit resumes to test AI screening.</span>
        </div>
        @else
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-100 text-[9px] text-slate-400 uppercase font-bold tracking-wider bg-slate-50/50">
                    <th class="py-2.5 px-3">Candidate</th>
                    <th class="py-2.5 px-3">Position</th>
                    <th class="py-2.5 px-3 text-center">Score</th>
                    <th class="py-2.5 px-3">Recommendation</th>
                    <th class="py-2.5 px-3 text-right">Details</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-xs text-slate-600">
                @foreach($topCandidates as $app)
                <tr class="hover:bg-slate-50/60 transition duration-150">
                    <td class="py-3.5 px-3">
                        <div class="flex flex-col">
                            <span class="font-bold text-slate-700">{{ $app->full_name }}</span>
                            <span class="text-[9px] text-slate-400 font-mono mt-0.5">{{ $app->email }}</span>
                        </div>
                    </td>
                    <td class="py-3.5 px-3">
                        <div class="flex flex-col">
                            <span class="font-semibold text-slate-600">{{ $app->jobPost->title }}</span>
                            <span class="text-[9px] text-indigo-500 font-bold uppercase tracking-wider mt-0.5">{{ $app->jobPost->department->name ?? '' }}</span>
                        </div>
                    </td>
                    <td class="py-3.5 px-3 text-center">
                        <span class="inline-block px-2.5 py-1 rounded-lg text-[10px] font-bold font-mono
                            {{ $app->matchScore->match_score >= 90 ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-indigo-50 text-indigo-600 border border-indigo-100' }}">
                            {{ $app->matchScore->match_score }}%
                        </span>
                    </td>
                    <td class="py-3.5 px-3">
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider
                            {{ $app->matchScore->hiring_recommendation === 'Strongly Recommended' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-indigo-500/10 text-indigo-500' }}">
                            {{ $app->matchScore->hiring_recommendation }}
                        </span>
                    </td>
                    <td class="py-3.5 px-3 text-right">
                        <a href="{{ route('jobs.candidate.ai', $app->id) }}" class="p-1 px-3 bg-slate-100 hover:bg-indigo-600 hover:text-white rounded-lg text-[10px] font-bold text-slate-600 transition duration-200">
                            <i class="fa-solid fa-robot"></i> Report
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
