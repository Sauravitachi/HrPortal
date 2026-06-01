@props(['tenant'])

<tr id="details-{{ $tenant->id }}" class="hidden bg-slate-50/20">
    <td colspan="5" class="p-4 border-b border-slate-200 bg-slate-50/20">
        <div class="p-4.5 rounded-2xl bg-white border border-slate-200/80 shadow-inner">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- HR Admins Section -->
                <div class="md:col-span-1 border-r border-slate-100 pr-0 md:pr-6">
                    <div class="flex items-center gap-2 mb-3.5">
                        <div class="w-7 h-7 rounded-lg bg-indigo-500/10 text-indigo-600 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-user-shield"></i>
                        </div>
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">HR Administrators</h3>
                    </div>

                    @php
                        $hrAdmins = $tenant->users->filter(fn($u) => in_array($u->role, ['company_admin', 'hr_manager']));
                    @endphp

                    @if($hrAdmins->isEmpty())
                        <div class="py-6 text-center text-slate-400 text-xs flex flex-col items-center justify-center gap-1.5 border border-dashed border-slate-200 rounded-xl">
                            <i class="fa-solid fa-user-xmark text-lg text-slate-300"></i>
                            <span>No HR Admins assigned.</span>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($hrAdmins as $admin)
                                <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200/60 hover:border-slate-300 transition-all duration-200">
                                    <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-500 to-indigo-600 text-white flex items-center justify-center text-xs font-bold uppercase shrink-0 shadow-sm shadow-indigo-500/25">
                                        {{ substr($admin->name, 0, 2) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h4 class="text-xs font-bold text-slate-800 truncate">{{ $admin->name }}</h4>
                                        <p class="text-[10px] text-slate-500 truncate mt-0.5">{{ $admin->email }}</p>
                                        <span class="inline-block text-[9px] font-mono text-indigo-600 bg-indigo-500/10 border border-indigo-500/20 px-2 py-0.5 rounded-full mt-2 uppercase font-bold">
                                            {{ str_replace('_', ' ', $admin->role) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Employees Roster -->
                <div class="md:col-span-2">
                    <div class="flex items-center justify-between mb-3.5">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-purple-500/10 text-purple-600 flex items-center justify-center text-xs">
                                <i class="fa-solid fa-users"></i>
                            </div>
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Employee Directory</h3>
                        </div>
                        <span class="text-[10px] font-bold bg-purple-500/10 text-purple-600 border border-purple-500/20 px-2.5 py-0.5 rounded-full">
                            {{ $tenant->employees->count() }} Total
                        </span>
                    </div>

                    @if($tenant->employees->isEmpty())
                        <div class="py-10 text-center text-slate-400 text-xs flex flex-col items-center justify-center gap-2 border border-dashed border-slate-200 rounded-xl">
                            <i class="fa-solid fa-users-slash text-2xl text-slate-300"></i>
                            <span>No employee records registered.</span>
                        </div>
                    @else
                        <div class="overflow-x-auto border border-slate-200/80 rounded-xl max-h-[260px] overflow-y-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-200 text-[9px] text-slate-400 uppercase tracking-wider font-bold bg-slate-50/80 sticky top-0 z-10">
                                        <th class="p-2.5">ID</th>
                                        <th class="p-2.5">Full Name</th>
                                        <th class="p-2.5">Email</th>
                                        <th class="p-2.5">Dept / Designation</th>
                                        <th class="p-2.5 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-[11px] text-slate-600">
                                    @foreach($tenant->employees as $emp)
                                        <tr class="hover:bg-slate-50/50 transition">
                                            <td class="p-2.5 font-mono text-[10px] text-indigo-600 font-semibold">{{ $emp->employee_id }}</td>
                                            <td class="p-2.5 font-bold text-slate-800">{{ $emp->full_name }}</td>
                                            <td class="p-2.5 truncate max-w-[140px]" title="{{ $emp->email }}">{{ $emp->email }}</td>
                                            <td class="p-2.5">
                                                <div class="flex flex-col">
                                                    <span class="font-semibold text-slate-700">{{ $emp->department ? $emp->department->name : 'N/A' }}</span>
                                                    <span class="text-[9px] text-slate-400">{{ $emp->designation ? $emp->designation->name : 'N/A' }}</span>
                                                </div>
                                            </td>
                                            <td class="p-2.5 text-center">
                                                @if($emp->employment_status === 'Active')
                                                    <span class="inline-block text-[9px] font-bold text-emerald-600 bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded-full uppercase">Active</span>
                                                @else
                                                    <span class="inline-block text-[9px] font-bold text-slate-500 bg-slate-500/10 border border-slate-500/20 px-2 py-0.5 rounded-full uppercase">{{ $emp->employment_status }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </td>
</tr>
