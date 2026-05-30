@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header Block -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-100 tracking-tight">SaaS Tenant Management</h1>
            <p class="text-xs text-slate-400 mt-1">Register new client companies, monitor subdomain domains, and handle subscription license plans from the UI.</p>
        </div>
        
        <button onclick="document.getElementById('tenant_modal').classList.remove('hidden')" class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold px-4 py-2 rounded-xl transition duration-200 flex items-center gap-2 shadow shadow-indigo-600/35">
            <i class="fa-solid fa-plus"></i> Add New Tenant
        </button>
    </div>

    <!-- Statistics Panel -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="glass-card p-4.5 rounded-2xl flex items-center justify-between hover:shadow-sm transition">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Total Registered Tenants</span>
                <h3 class="text-2xl font-bold text-slate-800">{{ $tenants->count() }}</h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-600 flex items-center justify-center text-lg shadow-sm border border-blue-500/20">
                <i class="fa-solid fa-server"></i>
            </div>
        </div>

        <div class="glass-card p-4.5 rounded-2xl flex items-center justify-between hover:shadow-sm transition">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Premium Companies</span>
                <h3 class="text-2xl font-bold text-slate-800">
                    {{ $tenants->filter(fn($t) => $t->company && $t->company->subscription_plan === 'premium')->count() }}
                </h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center text-lg shadow-sm border border-amber-500/20">
                <i class="fa-solid fa-crown"></i>
            </div>
        </div>

        <div class="glass-card p-4.5 rounded-2xl flex items-center justify-between hover:shadow-sm transition">
            <div class="space-y-1">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Free / Basic Tier Clients</span>
                <h3 class="text-2xl font-bold text-slate-800">
                    {{ $tenants->filter(fn($t) => !$t->company || $t->company->subscription_plan !== 'premium')->count() }}
                </h3>
            </div>
            <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-600 flex items-center justify-center text-lg shadow-sm border border-purple-500/20">
                <i class="fa-solid fa-seedling"></i>
            </div>
        </div>
    </div>

    <!-- Tenants Database Table -->
    <div class="glass-card rounded-2xl p-5 bg-white">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-200">
            <h2 class="text-xs font-bold text-slate-500 tracking-wider uppercase">Active Scoped Tenants Roster</h2>
        </div>

        <div class="overflow-x-auto">
            @if($tenants->isEmpty())
            <div class="h-48 flex flex-col items-center justify-center text-slate-500 text-xs gap-2">
                <i class="fa-solid fa-folder-open text-2xl text-slate-400"></i>
                <span>No tenants registered in the platform database.</span>
            </div>
            @else
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-[9px] text-slate-500 uppercase tracking-wider font-bold bg-slate-50/50">
                        <th class="p-3.5">Company Branding Name</th>
                        <th class="p-3.5">Subdomain & Router Domain</th>
                        <th class="p-3.5">Current License Plan</th>
                        <th class="p-3.5">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/60 text-xs text-slate-600">
                    @foreach($tenants as $tenant)
                    <tr class="hover:bg-slate-50/30">
                        <td class="p-3.5">
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-800">{{ $tenant->name }}</span>
                                <span class="text-[9px] text-slate-400 font-mono">ID: {{ $tenant->id }} • Created: {{ $tenant->created_at->format('M d, Y') }}</span>
                            </div>
                        </td>
                        <td class="p-3.5 font-mono text-[11px] text-slate-500">
                            <span class="text-indigo-600 font-semibold">{{ $tenant->subdomain }}</span>.hrportal.localhost
                            <a href="http://{{ $tenant->subdomain }}.hrportal.localhost:8000/login" target="_blank" class="text-[9px] bg-blue-500/10 text-blue-600 px-1.5 py-0.5 rounded border border-blue-500/20 font-bold uppercase ml-2" title="Launch Workspace">
                                Launch <i class="fa-solid fa-arrow-up-right-from-square ml-0.5"></i>
                            </a>
                        </td>
                        <td class="p-3.5">
                            <form action="{{ route('admin.tenants.plan', $tenant) }}" method="POST" class="flex items-center gap-1.5">
                                @csrf
                                <select name="subscription_plan" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 rounded-lg p-1 text-[10px] text-slate-600 font-semibold focus:outline-none cursor-pointer">
                                    <option value="free" {{ $tenant->company && $tenant->company->subscription_plan === 'free' ? 'selected' : '' }}>Free Trial</option>
                                    <option value="basic" {{ $tenant->company && $tenant->company->subscription_plan === 'basic' ? 'selected' : '' }}>Basic Tier</option>
                                    <option value="premium" {{ $tenant->company && $tenant->company->subscription_plan === 'premium' ? 'selected' : '' }}>Premium Pro</option>
                                </select>
                            </form>
                        </td>
                        <td class="p-3.5">
                            @if($tenant->subdomain !== 'default')
                            <form action="{{ route('admin.tenants.destroy', $tenant) }}" method="POST" onsubmit="return confirm('WARNING: Are you sure you want to permanently delete this tenant? All employees, attendance, and business data scoped to this tenant will be deleted forever!')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-600 font-bold text-[10px] uppercase flex items-center gap-1 transition" title="Delete Tenant">
                                    <i class="fa-solid fa-trash-can"></i> Terminate
                                </button>
                            </form>
                            @else
                            <span class="text-[9px] text-slate-400 font-mono italic">Primary Context</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

    <!-- Tenant Creation Modal Overlay -->
    <div id="tenant_modal" class="hidden fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 flex items-center justify-center p-6">
        <div class="glass-card rounded-2xl p-6 w-full max-w-md relative bg-white border border-slate-200 shadow-xl">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wide mb-4">Register Client Tenant</h2>
            <form action="{{ route('admin.tenants.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[9px] font-bold text-slate-500 uppercase tracking-wide mb-1.5">Company Name</label>
                    <input type="text" name="name" required placeholder="e.g. Acme Corporation" class="block w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-xs text-slate-800">
                </div>

                <div>
                    <label class="block text-[9px] font-bold text-slate-500 uppercase tracking-wide mb-1.5">Workspace Subdomain</label>
                    <div class="flex items-center">
                        <input type="text" name="subdomain" required placeholder="e.g. acme" class="block w-full bg-slate-50 border border-slate-200 rounded-l-lg px-3 py-2 text-xs text-slate-800 text-right">
                        <span class="bg-slate-100 border border-slate-200 border-l-0 text-slate-500 text-xs px-3 py-2 rounded-r-lg font-mono">.hrportal.localhost</span>
                    </div>
                    <span class="text-[9px] text-slate-400 block mt-1">Use alphanumeric characters and hyphens only.</span>
                </div>

                <div>
                    <label class="block text-[9px] font-bold text-slate-500 uppercase tracking-wide mb-1.5">Initial Subscription Plan</label>
                    <select name="subscription_plan" required class="block w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-xs text-slate-600 font-semibold cursor-pointer">
                        <option value="free">Free Trial (Core Hub features)</option>
                        <option value="basic">Basic License (HR Operations enabled)</option>
                        <option value="premium">Premium Pro (All features + Advanced ATS)</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('tenant_modal').classList.add('hidden')" class="px-4 py-2 bg-slate-50 border border-slate-200 hover:bg-slate-100 text-slate-500 rounded-lg text-xs font-semibold">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-bold shadow transition">
                        Register Client
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
