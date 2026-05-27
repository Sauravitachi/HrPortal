<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 text-slate-800">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - HrPortal</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f6f8fb;
            color: #1e293b;
        }
        .glass-card {
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03) !important;
        }
        input {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
            color: #1e293b !important;
        }
        input::placeholder {
            color: #94a3b8 !important;
        }
        .text-slate-200, .text-slate-400, .text-slate-500 {
            color: #1e293b !important;
        }
        .text-slate-500 {
            color: #64748b !important;
        }
        .bg-slate-900\/50 {
            background-color: #f8fafc !important;
            border-color: #e2e8f0 !important;
        }
        .bg-gradient-to-r, .bg-gradient-to-tr {
            background: linear-gradient(135deg, #206bc4 0%, #1a569d 100%) !important;
        }
        .text-indigo-400, .text-emerald-400, .text-purple-400 {
            color: #206bc4 !important;
        }
        .hover\:bg-indigo-500\/10:hover, .hover\:bg-emerald-500\/10:hover, .hover\:bg-purple-500\/10:hover {
            background-color: rgba(32, 107, 196, 0.08) !important;
            border-color: rgba(32, 107, 196, 0.2) !important;
        }
    </style>
</head>
<body class="h-full flex items-center justify-center p-6 antialiased bg-slate-50">

    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8 flex flex-col items-center gap-3">
            <div class="bg-gradient-to-tr from-indigo-600 to-purple-600 p-3.5 rounded-2xl text-white shadow-xl shadow-indigo-600/30">
                <i class="fa-solid fa-people-roof text-3xl"></i>
            </div>
            <div>
                <h1 class="text-3xl text-slate-800">HrPortal</h1>
                <p class="text-xs text-slate-500 mt-1 uppercase tracking-widest font-semibold font-mono">Centralized HRMS & Payroll</p>
            </div>
        </div>

        <!-- Card -->
        <div class="glass-card rounded-3xl p-8 relative overflow-hidden">
            <div class="absolute -top-10 -left-10 w-24 h-24 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="absolute -bottom-10 -right-10 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>

            <h2 class="text-lg font-semibold text-slate-200 mb-6">Authenticate Account</h2>

            <!-- Errors -->
            @if ($errors->any())
            <div class="mb-4 bg-red-500/10 border border-red-500/20 text-red-400 text-xs px-4 py-3 rounded-xl flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ $errors->first() }}</span>
            </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wide">Work Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                            <i class="fa-regular fa-envelope text-sm"></i>
                        </span>
                        <input type="email" name="email" id="email" required value="{{ old('email') }}"
                            class="block w-full bg-slate-900/60 border border-slate-800/80 rounded-xl pl-10 pr-4 py-3 text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500/60 focus:ring-1 focus:ring-indigo-500/30 text-sm transition-all duration-300"
                            placeholder="username@company.com">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-xs font-semibold text-slate-400 uppercase tracking-wide">Security Key</label>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                            <i class="fa-solid fa-lock text-sm"></i>
                        </span>
                        <input type="password" name="password" id="password" required
                            class="block w-full bg-slate-900/60 border border-slate-800/80 rounded-xl pl-10 pr-4 py-3 text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500/60 focus:ring-1 focus:ring-indigo-500/30 text-sm transition-all duration-300"
                            placeholder="••••••••••••">
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs py-1">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-400">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-900 border-slate-800 text-indigo-600 focus:ring-indigo-500/50">
                        Keep me verified
                    </label>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold py-3 px-4 rounded-xl shadow-lg shadow-indigo-600/30 transition-all duration-300 text-sm flex items-center justify-center gap-2">
                    <span>Unlock Portal</span>
                    <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i>
                </button>
            </form>

            <!-- Pre-filled explorers helper -->
            <div class="mt-8 pt-6 border-t border-slate-800/50 text-center">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block mb-4">Click to Autofill Demo Credentials</span>
                <div class="flex flex-col gap-2.5">
                    <button type="button" onclick="autofill('super@example.com', 'password')" class="flex items-center justify-between text-left p-2.5 rounded-xl bg-slate-900/50 hover:bg-indigo-500/10 border border-slate-800 hover:border-indigo-500/20 text-xs text-slate-400 hover:text-slate-200 transition-all duration-200">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-shield-halved text-indigo-400"></i>
                            <span class="font-medium">Super Admin</span>
                        </div>
                        <span class="font-mono text-[10px] text-slate-500">super@example.com</span>
                    </button>
                    
                    <button type="button" onclick="autofill('hr@example.com', 'password')" class="flex items-center justify-between text-left p-2.5 rounded-xl bg-slate-900/50 hover:bg-emerald-500/10 border border-slate-800 hover:border-emerald-500/20 text-xs text-slate-400 hover:text-slate-200 transition-all duration-200">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-user-tie text-emerald-400"></i>
                            <span class="font-medium">HR Manager</span>
                        </div>
                        <span class="font-mono text-[10px] text-slate-500">hr@example.com</span>
                    </button>

                    <button type="button" onclick="autofill('employee@example.com', 'password')" class="flex items-center justify-between text-left p-2.5 rounded-xl bg-slate-900/50 hover:bg-purple-500/10 border border-slate-800 hover:border-purple-500/20 text-xs text-slate-400 hover:text-slate-200 transition-all duration-200">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-user text-purple-400"></i>
                            <span class="font-medium">Employee Profile</span>
                        </div>
                        <span class="font-mono text-[10px] text-slate-500">employee@example.com</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function autofill(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }
    </script>
</body>
</html>
