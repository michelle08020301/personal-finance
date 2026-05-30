
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Finance — @yield('title', 'Dashboard')</title>
   <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:opsz,wght@9..40,400;9..40,500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-body antialiased">

    <nav class="glass-nav sticky top-0 z-50 flex items-center justify-between h-12 px-6 border-b border-white/8">
        <a href="{{ route('dashboard') }}" class="brand-text text-sm font-extrabold tracking-tight">
            Personal Finance
        </a>

        <div class="flex items-center gap-0.5 bg-white/6 border border-white/8 rounded-xl p-1">
            <a href="{{ route('dashboard') }}"
               class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}">
                Dashboard
            </a>
            <a href="{{ route('transactions.index') }}"
               class="nav-link {{ request()->routeIs('transactions.*') ? 'nav-link-active' : '' }}">
                Transactions
            </a>
            <a href="{{ route('budgets.index') }}"
               class="nav-link {{ request()->routeIs('budgets.*') ? 'nav-link-active' : '' }}">
                Budget
            </a>
            <a href="{{ route('debts.index') }}"
               class="nav-link {{ request()->routeIs('debts.*') ? 'nav-link-active' : '' }}">
                Debts
            </a>
<a href="{{ route('savings.index') }}"
   class="nav-link {{ request()->routeIs('savings.*') ? 'nav-link-active' : '' }}">
    Savings
</a>
        </div>

        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold"
                     style="background: linear-gradient(135deg, #c8ff80, #80ffea); color: #071a04;">
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </div>
                <span class="text-xs" style="color: rgba(255,255,255,0.35);">{{ Auth::user()->name }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-xs transition-colors"
                        style="color: rgba(255,255,255,0.3);">
                    Logout
                </button>
            </form>
        </div>
    </nav>

    @if(session('success'))
        <div class="max-w-7xl mx-auto px-6 pt-4">
            <div class="glass-card glass-green text-sm px-4 py-3 flex items-center gap-2"
                 style="color: #c8ff80;">
                ✓ {{ session('success') }}
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="max-w-7xl mx-auto px-6 pt-4">
            <div class="glass-card glass-red text-sm px-4 py-3" style="color: #ff9090;">
                <ul class="space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <main class="max-w-7xl mx-auto px-6 py-5">
        @yield('content')
    </main>

</body>
</html>