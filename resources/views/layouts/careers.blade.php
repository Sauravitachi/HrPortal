<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 text-slate-800">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Careers Portal</title>

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
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important;
        }
        .text-slate-100, .text-slate-200, .text-slate-300 {
            color: #1e293b !important;
        }
        .text-slate-400, .text-slate-500 {
            color: #64748b !important;
        }
        .bg-indigo-500\/10 {
            background-color: rgba(32, 107, 196, 0.08) !important;
        }
        .border-indigo-500\/20, .border-indigo-500\/15 {
            border-color: rgba(32, 107, 196, 0.2) !important;
        }
        .bg-indigo-600 {
            background-color: #206bc4 !important;
        }
        .bg-indigo-600:hover {
            background-color: #1a569d !important;
        }
        input, select, textarea {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
            color: #1e293b !important;
        }
    </style>
</head>
<body class="h-full flex flex-col antialiased text-slate-800 overflow-y-auto">

    <!-- Careers Header -->
    <header class="glass-card flex h-16 items-center justify-between px-6 md:px-12 border-b border-slate-200 bg-white sticky top-0 z-20">
        <div class="flex items-center gap-3">
            <div class="bg-gradient-to-tr from-blue-600 to-indigo-600 p-2 rounded-xl text-white shadow-md shadow-blue-500/35">
                <i class="fa-solid fa-briefcase text-lg"></i>
            </div>
            <div>
                <span class="text-xl font-bold tracking-tight text-slate-800">
                    {{ app('currentTenant') ? app('currentTenant')->name : 'Company' }} Careers
                </span>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs font-semibold text-slate-500">Careers Portal</span>
        </div>
    </header>

    <!-- Content Area -->
    <main class="flex-1 max-w-5xl w-full mx-auto p-6 md:p-12">
        <!-- Notification Toasts -->
        @if(session('success'))
        <div id="toast-success" class="mb-6 glass-card border border-emerald-500/30 bg-emerald-500/5 px-4 py-3 rounded-xl flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                <span class="text-xs font-semibold text-slate-700">{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="text-center py-8 text-xs text-slate-400 border-t border-slate-200 mt-12 bg-white w-full">
        <span>&copy; {{ date('Y') }} {{ app('currentTenant') ? app('currentTenant')->name : 'Company' }} • Scoped to Secure SaaS Multi-Tenant Platform</span>
    </footer>

</body>
</html>
