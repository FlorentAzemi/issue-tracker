<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in — Issue Tracker</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg: #f8fafc; --surface: #fff; --border: #e2e8f0; --text: #1e293b;
            --text-muted: #64748b; --primary: #6366f1; --primary-hover: #4f46e5;
            --danger: #ef4444; --radius: 8px;
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -2px rgba(0,0,0,.06);
        }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text);
               min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: var(--surface); border: 1px solid var(--border);
                      border-radius: var(--radius); box-shadow: var(--shadow-md);
                      width: 100%; max-width: 400px; padding: 40px 36px; }
        .login-logo { font-size: 1.5rem; font-weight: 700; margin-bottom: 8px; }
        .login-subtitle { color: var(--text-muted); font-size: .875rem; margin-bottom: 32px; }
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: .875rem; font-weight: 500; margin-bottom: 6px; }
        .form-control { display: block; width: 100%; padding: 9px 12px;
                        border: 1px solid var(--border); border-radius: 6px;
                        font-size: .875rem; font-family: inherit; background: var(--surface); }
        .form-control:focus { outline: none; border-color: var(--primary);
                              box-shadow: 0 0 0 3px rgba(99,102,241,.15); }
        .form-error { color: var(--danger); font-size: .8125rem; margin-top: 4px; }
        .btn { display: block; width: 100%; padding: 10px; border-radius: 6px;
               font-size: .875rem; font-weight: 600; cursor: pointer; border: none;
               background: var(--primary); color: #fff; margin-top: 24px; transition: background .15s; }
        .btn:hover { background: var(--primary-hover); }
        .remember { display: flex; align-items: center; gap: 8px; font-size: .875rem; color: var(--text-muted); }
        .hint { margin-top: 24px; padding: 12px; background: #f8fafc; border: 1px solid var(--border);
                border-radius: 6px; font-size: .8125rem; color: var(--text-muted); line-height: 1.7; }
        code { background: #e2e8f0; padding: 1px 5px; border-radius: 4px; font-size: .8rem; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-logo">Issue Tracker</div>
        <p class="login-subtitle">Sign in to your account</p>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" autofocus required>
                @error('email') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="remember">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember">Remember me</label>
            </div>
            <button type="submit" class="btn">Sign in</button>
        </form>

        <div class="hint">
            <strong>Demo accounts</strong><br>
            <code>alice@example.com</code> admin<br>
            <code>james@example.com</code> admin<br>
            <code>sophie@example.com</code> — member only<br>
            Password for all: <code>Secret123!</code>
        </div>
    </div>
</body>
</html>
