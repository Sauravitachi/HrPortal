<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 text-slate-800">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HrPortal - Centralized HRMS</title>
    
    <!-- Modern Premium Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f6f8fb !important;
            color: #1e293b !important;
        }
        .glass-card {
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05) !important;
        }
        .sidebar-item-active {
            background: rgba(32, 107, 196, 0.06) !important;
            border-left: 3px solid #206bc4 !important;
            color: #206bc4 !important;
            font-weight: 600;
        }
        .sidebar-item-active i {
            color: #206bc4 !important;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f6f8fb;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Tabler Overrides for text contrast and form styles */
        .text-slate-100, .text-slate-200, .text-slate-300 {
            color: #1e293b !important;
        }
        .text-slate-400, .text-slate-500 {
            color: #64748b !important;
        }
        .bg-slate-950, .bg-slate-900, .bg-slate-900\/60, .bg-slate-900\/40, .bg-slate-900\/20, .bg-slate-950\/60 {
            background-color: #ffffff !important;
            border-color: #e2e8f0 !important;
        }
        .border-slate-800, .border-slate-800\/80, .border-slate-800\/50, .border-slate-800\/40, .border-slate-800\/60 {
            border-color: #e2e8f0 !important;
        }
        .divide-slate-800\/40 > :not([hidden]) ~ :not([hidden]) {
            border-color: #e2e8f0 !important;
        }
        input, select, textarea {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
            color: #1e293b !important;
        }
        input::placeholder, textarea::placeholder {
            color: #94a3b8 !important;
        }
        .bg-gradient-to-r, .bg-gradient-to-tr {
            background: linear-gradient(135deg, #206bc4 0%, #1a569d 100%) !important;
        }
        .text-indigo-400, .text-indigo-400\/80, .text-indigo-500 {
            color: #206bc4 !important;
        }
        .bg-indigo-500\/10 {
            background-color: rgba(32, 107, 196, 0.08) !important;
        }
        .border-indigo-500\/20, .border-indigo-500\/15, .border-indigo-500\/30 {
            border-color: rgba(32, 107, 196, 0.2) !important;
        }
        .bg-indigo-600 {
            background-color: #206bc4 !important;
        }
        .bg-indigo-600:hover {
            background-color: #1a569d !important;
        }
        .shadow-indigo-600\/35, .shadow-indigo-600\/30 {
            box-shadow: 0 4px 6px -1px rgba(32, 107, 196, 0.15), 0 2px 4px -2px rgba(32, 107, 196, 0.1) !important;
        }
    </style>
</head>
<body class="h-full flex flex-col overflow-hidden antialiased text-slate-800">

    <!-- Top Navigation Bar -->
    <header class="glass-card flex h-16 shrink-0 items-center justify-between px-6 border-b border-slate-800/80 z-20">
        <div class="flex items-center gap-3">
            <div class="bg-gradient-to-tr from-indigo-600 to-purple-600 p-2 rounded-xl text-white shadow-md shadow-indigo-600/35">
                <i class="fa-solid fa-people-roof text-lg"></i>
            </div>
            <div>
                <span class="text-xl text-slate-800">HrPortal</span>                
            </div>
        </div>
        
        <div class="flex items-center gap-4">
            <div class="flex flex-col text-right">
                <span class="text-xs font-semibold text-slate-300">{{ Auth::user()->name }}</span>
                <span class="text-[10px] font-mono text-indigo-400 capitalize px-2 py-0.5 mt-0.5 rounded bg-indigo-500/10 border border-indigo-500/20 inline-block align-middle self-end">
                    {{ str_replace('_', ' ', Auth::user()->role) }}
                </span>
            </div>
            
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="p-2 rounded-xl text-slate-400 hover:text-red-400 hover:bg-red-500/10 border border-transparent hover:border-red-500/20 transition-all duration-300" title="Logout">
                    <i class="fa-solid fa-power-off"></i>
                </button>
            </form>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden">
        <!-- Sidebar Navigation -->
        <aside class="w-64 glass-card border-r border-slate-800/80 flex flex-col justify-between p-4 shrink-0 overflow-y-auto z-10">
            <div class="space-y-6">
                <div>
                    <span class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest block mb-3">Core Hub</span>
                    <nav class="space-y-1">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-800/40 transition-all duration-200 {{ request()->routeIs('dashboard') ? 'sidebar-item-active' : '' }}">
                            <i class="fa-solid fa-chart-line w-5 text-center text-slate-400"></i>
                            <span class="text-sm font-medium">Dashboard</span>
                        </a>
                        <a href="{{ route('attendance.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-800/40 transition-all duration-200 {{ request()->routeIs('attendance.*') ? 'sidebar-item-active' : '' }}">
                            <i class="fa-regular fa-calendar-check w-5 text-center text-slate-400"></i>
                            <span class="text-sm font-medium">Attendance</span>
                        </a>
                        <a href="{{ route('leaves.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-800/40 transition-all duration-200 {{ request()->routeIs('leaves.*') ? 'sidebar-item-active' : '' }}">
                            <i class="fa-solid fa-umbrella-beach w-5 text-center text-slate-400"></i>
                            <span class="text-sm font-medium">Leave Requests</span>
                        </a>
                    </nav>
                </div>

                @if(Auth::user()->isHrManager())
                <div>
                    <span class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest block mb-3">HR Operations</span>
                    <nav class="space-y-1">
                        <a href="{{ route('employees.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-800/40 transition-all duration-200 {{ request()->routeIs('employees.*') ? 'sidebar-item-active' : '' }}">
                            <i class="fa-solid fa-user-tie w-5 text-center text-slate-400"></i>
                            <span class="text-sm font-medium">Employee Profiles</span>
                        </a>
                        <a href="{{ route('payroll.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-800/40 transition-all duration-200 {{ request()->routeIs('payroll.*') ? 'sidebar-item-active' : '' }}">
                            <i class="fa-solid fa-file-invoice-dollar w-5 text-center text-slate-400"></i>
                            <span class="text-sm font-medium">Payroll Engine</span>
                        </a>
                        <a href="{{ route('jobs.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-800/40 transition-all duration-200 {{ request()->routeIs('jobs.index') ? 'sidebar-item-active' : '' }}">
                            <i class="fa-solid fa-briefcase w-5 text-center text-slate-400"></i>
                            <span class="text-sm font-medium">Recruitment Funnel</span>
                        </a>
                        <a href="{{ route('jobs.ai.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-800/40 transition-all duration-200 {{ request()->routeIs('jobs.ai.dashboard') ? 'sidebar-item-active' : '' }}">
                            <i class="fa-solid fa-robot w-5 text-center text-slate-400"></i>
                            <span class="text-sm font-medium">AI Screening Hub</span>
                        </a>
                        <a href="{{ route('jobs.integrations') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-800/40 transition-all duration-200 {{ request()->routeIs('jobs.integrations') ? 'sidebar-item-active' : '' }}">
                            <i class="fa-solid fa-circle-nodes w-5 text-center text-slate-400"></i>
                            <span class="text-sm font-medium">Job Syndication</span>
                        </a>
                    </nav>
                </div>
                @endif

                @if(Auth::user()->isSuperAdmin())
                <div>
                    <span class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest block mb-3">System Administration</span>
                    <nav class="space-y-1">
                        <a href="{{ route('admin.tenants.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-800/40 transition-all duration-200 {{ request()->routeIs('admin.tenants.*') ? 'sidebar-item-active' : '' }}">
                            <i class="fa-solid fa-server w-5 text-center text-slate-400"></i>
                            <span class="text-sm font-medium">Tenant Management</span>
                        </a>
                    </nav>
                </div>
                @endif
            </div>

            <div class="mt-auto border-t border-slate-800/50 pt-4 flex flex-col gap-2">
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-900/60 border border-slate-800/50">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-[10px] text-slate-400 font-mono uppercase tracking-wider">Database Connected</span>
                </div>
            </div>
        </aside>

        <!-- Main Dashboard Viewport -->
        <main class="flex-1 flex flex-col overflow-hidden p-6 gap-6 relative">
            <!-- Toast notification messages -->
            @if(session('success'))
            <div id="toast-success" class="absolute top-6 right-6 glass-card border border-emerald-500/30 bg-slate-950/80 px-4 py-3 rounded-xl flex items-center gap-3 shadow-xl z-50 animate-bounce duration-700">
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-400 border border-emerald-500/20">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <span class="text-xs font-semibold text-slate-200 block">Operation Successful</span>
                    <span class="text-[10px] text-slate-400">{{ session('success') }}</span>
                </div>
                <button onclick="document.getElementById('toast-success').style.display='none'" class="text-slate-500 hover:text-slate-300 ml-4">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            @endif

            @if(session('error'))
            <div id="toast-error" class="absolute top-6 right-6 glass-card border border-red-500/30 bg-slate-950/80 px-4 py-3 rounded-xl flex items-center gap-3 shadow-xl z-50 animate-bounce">
                <div class="w-8 h-8 rounded-lg bg-red-500/10 flex items-center justify-center text-red-400 border border-red-500/20">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <span class="text-xs font-semibold text-slate-200 block">Operation Interrupted</span>
                    <span class="text-[10px] text-slate-400">{{ session('error') }}</span>
                </div>
                <button onclick="document.getElementById('toast-error').style.display='none'" class="text-slate-500 hover:text-slate-300 ml-4">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            @endif

            <div class="flex-1 overflow-y-auto pr-1">
                @yield('content')
            </div>
        </main>
    </div>

</body>
</html>
