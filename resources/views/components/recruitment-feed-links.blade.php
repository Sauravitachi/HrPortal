<div class="bg-gradient-to-r from-slate-900 to-indigo-950 p-4.5 rounded-2xl text-white flex flex-col md:flex-row md:items-center md:justify-between gap-4 border border-indigo-900/40 shadow-sm relative overflow-hidden">
    <div class="absolute -bottom-12 -right-12 w-32 h-32 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>
    <div class="space-y-1 z-10">
        <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest block">Active External Job Feeds</span>
        <p class="text-xs text-slate-300">Share these auto-updating feed endpoints with external recruiters, job portals, or custom crawlers.</p>
    </div>
    <div class="flex flex-wrap gap-2.5 z-10">
        <a href="{{ route('jobs.feed.xml') }}" target="_blank" class="px-3 py-1.5 bg-slate-800/80 hover:bg-slate-800 border border-slate-700/80 rounded-xl text-[10px] font-bold font-mono text-indigo-300 transition duration-200">
            <i class="fa-solid fa-code text-indigo-400 mr-1"></i> XML Feed
        </a>
        <a href="{{ route('jobs.feed.json') }}" target="_blank" class="px-3 py-1.5 bg-slate-800/80 hover:bg-slate-800 border border-slate-700/80 rounded-xl text-[10px] font-bold font-mono text-purple-300 transition duration-200">
            <i class="fa-solid fa-brackets-curly text-purple-400 mr-1"></i> JSON Feed
        </a>
        <a href="{{ route('jobs.feed.rss') }}" target="_blank" class="px-3 py-1.5 bg-slate-800/80 hover:bg-slate-800 border border-slate-700/80 rounded-xl text-[10px] font-bold font-mono text-amber-300 transition duration-200">
            <i class="fa-solid fa-rss text-amber-400 mr-1"></i> RSS Feed
        </a>
    </div>
</div>
