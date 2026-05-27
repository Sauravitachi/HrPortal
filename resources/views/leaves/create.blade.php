@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-xl mx-auto">
    
    <!-- Top Bar -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-100 tracking-tight">Apply for Leave</h1>
            <p class="text-xs text-slate-400 mt-1">Submit leave details for approval. Ensure sufficient balances exist.</p>
        </div>
        <a href="{{ route('leaves.index') }}" class="bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-800 text-xs font-semibold px-4 py-2 rounded-xl transition-all duration-200">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back
        </a>
    </div>

    <!-- Application Form -->
    <div class="glass-card rounded-2xl p-6 relative overflow-hidden">
        <div class="absolute -top-10 -left-10 w-20 h-20 bg-indigo-500/5 rounded-full blur-xl pointer-events-none"></div>

        <form action="{{ route('leaves.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <!-- Leave Type -->
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Leave Category *</label>
                <select name="leave_type_id" required class="block w-full bg-slate-950 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-indigo-500/60 transition-all duration-200">
                    @foreach($leaveTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }} (Max: {{ $type->max_days }} days/yr)</option>
                    @endforeach
                </select>
            </div>

            <!-- Date Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Start Date -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Start Date *</label>
                    <input type="date" name="start_date" id="start_date" required onchange="calculateDays()"
                        class="block w-full bg-slate-950 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-indigo-500/60 font-mono">
                </div>

                <!-- End Date -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">End Date *</label>
                    <input type="date" name="end_date" id="end_date" required onchange="calculateDays()"
                        class="block w-full bg-slate-950 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-indigo-500/60 font-mono">
                </div>
            </div>

            <!-- Total Estimated days tracker -->
            <div id="days_badge" class="hidden p-3 bg-indigo-500/10 border border-indigo-500/20 rounded-xl flex items-center justify-between text-xs text-slate-300 transition duration-300">
                <span>Calculated Leave Duration:</span>
                <span class="font-bold text-indigo-400"><span id="days_count">0</span> Days</span>
            </div>

            <!-- Reason -->
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Reason for Leave *</label>
                <textarea name="reason" required rows="3" placeholder="Describe the reason for applying..."
                    class="block w-full bg-slate-950 border border-slate-800/80 rounded-xl px-3 py-2 text-xs text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500/60"></textarea>
            </div>

            <!-- Attachment -->
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Support Attachment (Optional)</label>
                <input type="file" name="attachment" accept="image/*,application/pdf"
                    class="block w-full bg-slate-950 border border-slate-800/80 rounded-xl px-3 py-1.5 text-xs text-slate-400 focus:outline-none focus:border-indigo-500/60 file:bg-slate-900 file:border-none file:text-[10px] file:text-indigo-400 file:px-2.5 file:py-1 file:rounded file:mr-2">
            </div>

            <!-- Submit -->
            <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-indigo-600/30 transition-all duration-300 text-xs flex items-center justify-center gap-1.5">
                <span>Submit Leave Application</span>
                <i class="fa-solid fa-paper-plane text-[10px]"></i>
            </button>
        </form>
    </div>

    <script>
        function calculateDays() {
            const startStr = document.getElementById('start_date').value;
            const endStr = document.getElementById('end_date').value;
            const badge = document.getElementById('days_badge');
            const count = document.getElementById('days_count');

            if (startStr && endStr) {
                const start = new Date(startStr);
                const end = new Date(endStr);
                
                if (end >= start) {
                    const diffTime = Math.abs(end - start);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                    
                    badge.classList.remove('hidden');
                    count.textContent = diffDays;
                } else {
                    badge.classList.add('hidden');
                }
            } else {
                badge.classList.add('hidden');
            }
        }
    </script>

</div>
@endsection
