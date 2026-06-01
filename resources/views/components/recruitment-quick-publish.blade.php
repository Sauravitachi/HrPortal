@props(['jobs', 'platforms'])

<div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
    <h2 class="text-sm font-bold text-slate-800 tracking-wide uppercase pb-3 border-b border-slate-100">Quick Syndicate Requisition</h2>
    
    @if($jobs->isEmpty())
    <p class="text-[10px] text-slate-400 mt-3 text-center">No active job posts found to syndicate.</p>
    @else
    <form action="" id="quick_publish_form" method="POST" class="mt-4 space-y-4" onsubmit="return validateQuickPublishForm()">
        @csrf
        <div>
            <label class="block text-[9px] font-bold text-slate-400 uppercase mb-1.5">Select Requisition</label>
            <select id="quick_job_select" class="block w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-600 focus:outline-none focus:ring-1 focus:ring-indigo-500" onchange="updatePublishFormAction()">
                <option value="">Choose active job...</option>
                @foreach($jobs as $job)
                <option value="{{ $job->id }}">{{ $job->title }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-[9px] font-bold text-slate-400 uppercase mb-1.5">Syndicate To</label>
            <div class="grid grid-cols-2 gap-2">
                @foreach($platforms as $p)
                <label class="flex items-center gap-1.5 p-2 rounded-lg border border-slate-100 bg-slate-50/50 hover:bg-slate-100/50 cursor-pointer">
                    <input type="checkbox" name="platforms[]" value="{{ $p }}" class="rounded text-indigo-600 quick-platform-checkbox" onchange="validateSelections()">
                    <span class="text-[10px] font-semibold text-slate-600 capitalize">{{ $p }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <button type="submit" id="quick_publish_submit_btn" disabled class="w-full py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-xl text-xs font-bold shadow-md transition duration-200 opacity-50 cursor-not-allowed">
            Trigger Publishing Queue
        </button>
    </form>
    @endif
</div>

<script>
    function updatePublishFormAction() {
        const jobId = document.getElementById('quick_job_select').value;
        const form = document.getElementById('quick_publish_form');
        if (jobId) {
            form.action = `/jobs/${jobId}/publish`;
        } else {
            form.action = '';
        }
        validateSelections();
    }

    function validateSelections() {
        const jobId = document.getElementById('quick_job_select').value;
        const checkboxes = document.querySelectorAll('.quick-platform-checkbox');
        let isAnyChecked = false;
        
        checkboxes.forEach(cb => {
            if (cb.checked) {
                isAnyChecked = true;
            }
        });

        const submitBtn = document.getElementById('quick_publish_submit_btn');
        if (jobId && isAnyChecked) {
            submitBtn.removeAttribute('disabled');
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            submitBtn.classList.add('hover:from-indigo-700', 'hover:to-indigo-800');
        } else {
            submitBtn.setAttribute('disabled', 'disabled');
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            submitBtn.classList.remove('hover:from-indigo-700', 'hover:to-indigo-800');
        }
    }

    function validateQuickPublishForm() {
        const jobId = document.getElementById('quick_job_select').value;
        if (!jobId) {
            alert('Please select a job requisition first.');
            return false;
        }
        return true;
    }
</script>
