<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Finance — Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:opsz,wght@9..40,400;9..40,500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center" style="background: linear-gradient(135deg, #0d1b2a 0%, #1a0a2e 50%, #0a1f14 100%);">

<div style="width:100%;max-width:420px;padding:0 16px">

    {{-- Logo --}}
    <div style="text-align:center;margin-bottom:32px">
        <div style="font-family:'Bebas Neue',sans-serif;font-size:32px;letter-spacing:3px;background:linear-gradient(90deg,#c8ff80,#80ffea);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
            Personal Finance
        </div>
        <p style="font-size:13px;color:rgba(255,255,255,0.35);margin-top:6px">Sign in to your account</p>
    </div>

    {{-- Card --}}
    <div class="glass-card" style="padding:32px">

        @if($errors->any())
            <div style="background:rgba(255,100,100,0.1);border:1px solid rgba(255,100,100,0.2);border-radius:10px;padding:12px;margin-bottom:20px">
                <ul style="margin:0;padding:0;list-style:none">
                    @foreach($errors->all() as $error)
                        <li style="font-size:12px;color:#ff9090">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div style="margin-bottom:18px">
                <label class="glass-label">Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       placeholder="you@example.com"
                       class="glass-input" required autofocus>
            </div>

            <div style="margin-bottom:18px">
                <label class="glass-label">Password</label>
                <input type="password" name="password"
                       placeholder="••••••••"
                       class="glass-input" required>
            </div>

            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                    <input type="checkbox" name="remember"
                           style="width:14px;height:14px;accent-color:#c8ff80">
                    <span style="font-size:12px;color:rgba(255,255,255,0.4)">Remember me</span>
                </label>
                @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       style="font-size:12px;color:#c8ff80;text-decoration:none">
                        Forgot password?
                    </a>
                @endif
            </div>

            <button type="submit" class="btn-cta">
                Sign in
            </button>
        </form>
    </div>

    {{-- Register link --}}
    <p style="text-align:center;margin-top:20px;font-size:13px;color:rgba(255,255,255,0.35)">
        Don't have an account?
        <a href="{{ route('register') }}" style="color:#c8ff80;text-decoration:none;font-weight:500">
            Register here
        </a>
    </p>

</div>
</body>
</html>