<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Issue Tracker')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg: #f8fafc;
            --surface: #ffffff;
            --border: #e2e8f0;
            --text: #1e293b;
            --text-muted: #64748b;
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --danger: #ef4444;
            --success: #22c55e;
            --warning: #f59e0b;
            --radius: 8px;
            --shadow: 0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.04);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -2px rgba(0,0,0,.06);
        }

        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); line-height: 1.5; }

        a { color: var(--primary); text-decoration: none; }
        a:hover { text-decoration: underline; }

        .navbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0 24px;
            display: flex;
            align-items: center;
            gap: 32px;
            height: 56px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow);
        }
        .navbar-brand { font-weight: 700; font-size: 1.1rem; color: var(--text); }
        .navbar-brand:hover { text-decoration: none; color: var(--primary); }
        .navbar-nav { display: flex; gap: 4px; flex: 1; }
        .navbar-user { display: flex; align-items: center; gap: 12px; font-size: .875rem; }
        .navbar-user span { color: var(--text-muted); }
        .navbar-logout { padding: 5px 10px; border-radius: 6px; border: 1px solid var(--border);
                         background: var(--surface); color: var(--text); font-size: .8125rem;
                         cursor: pointer; transition: background .15s; }
        .navbar-logout:hover { background: #f1f5f9; }
        .nav-link {
            padding: 6px 12px; border-radius: 6px; color: var(--text-muted);
            font-size: .875rem; font-weight: 500; transition: all .15s;
        }
        .nav-link:hover, .nav-link.active { background: #f1f5f9; color: var(--text); text-decoration: none; }

        .container { max-width: 1200px; margin: 0 auto; padding: 24px 24px; }

        .card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden;
        }
        .card-header {
            padding: 16px 20px; border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
        }
        .card-body { padding: 20px; }

        .page-header { margin-bottom: 24px; display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; }
        .page-title { font-size: 1.5rem; font-weight: 700; }
        .page-subtitle { color: var(--text-muted); font-size: .875rem; margin-top: 4px; }

        .btn {
            display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px;
            border-radius: 6px; font-size: .875rem; font-weight: 500; cursor: pointer;
            border: 1px solid transparent; transition: all .15s; white-space: nowrap;
            text-decoration: none !important;
        }
        .btn-primary { background: var(--primary); color: #fff; border-color: var(--primary); }
        .btn-primary:hover { background: var(--primary-hover); border-color: var(--primary-hover); }
        .btn-secondary { background: var(--surface); color: var(--text); border-color: var(--border); }
        .btn-secondary:hover { background: #f1f5f9; }
        .btn-danger { background: #fef2f2; color: var(--danger); border-color: #fecaca; }
        .btn-danger:hover { background: #fee2e2; }
        .btn-sm { padding: 5px 10px; font-size: .8125rem; }
        .btn-ghost { background: transparent; border-color: transparent; color: var(--text-muted); }
        .btn-ghost:hover { background: #f1f5f9; color: var(--text); }

        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: .875rem; font-weight: 500; margin-bottom: 6px; }
        .form-control {
            display: block; width: 100%; padding: 8px 12px;
            border: 1px solid var(--border); border-radius: 6px;
            font-size: .875rem; font-family: inherit; color: var(--text);
            background: var(--surface); transition: border-color .15s;
        }
        .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(99,102,241,.15); }
        select.form-control { cursor: pointer; }
        textarea.form-control { min-height: 100px; resize: vertical; }
        .form-error { color: var(--danger); font-size: .8125rem; margin-top: 4px; }

        .badge {
            display: inline-flex; align-items: center; padding: 2px 8px;
            border-radius: 999px; font-size: .75rem; font-weight: 500;
        }
        .badge-open { background: #dcfce7; color: #15803d; }
        .badge-in_progress { background: #dbeafe; color: #1d4ed8; }
        .badge-closed { background: #f1f5f9; color: #475569; }
        .badge-low { background: #f0fdf4; color: #16a34a; }
        .badge-medium { background: #fffbeb; color: #d97706; }
        .badge-high { background: #fef2f2; color: #dc2626; }

        .tag-chip {
            display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px;
            border-radius: 999px; font-size: .75rem; font-weight: 500;
            color: #fff; white-space: nowrap;
        }

        .table { width: 100%; border-collapse: collapse; font-size: .875rem; }
        .table th { padding: 10px 16px; text-align: left; font-weight: 600; color: var(--text-muted); font-size: .8125rem; border-bottom: 1px solid var(--border); }
        .table td { padding: 12px 16px; border-bottom: 1px solid var(--border); vertical-align: middle; }
        .table tr:last-child td { border-bottom: none; }
        .table tbody tr:hover { background: #f8fafc; }

        .alert {
            padding: 12px 16px; border-radius: var(--radius); margin-bottom: 16px;
            font-size: .875rem; border: 1px solid transparent;
            transition: opacity .1s ease, margin .1s ease, padding .1s ease, max-height .1s ease;
            max-height: 80px; overflow: hidden;
        }
        .alert.fade-out { opacity: 0; max-height: 0; margin-bottom: 0; padding-top: 0; padding-bottom: 0; }
        .alert-success { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
        .alert-error { background: #fef2f2; color: #dc2626; border-color: #fecaca; }

        .filter-bar {
            display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin-bottom: 20px;
        }
        .filter-bar select, .filter-bar input {
            padding: 7px 12px; border: 1px solid var(--border); border-radius: 6px;
            font-size: .875rem; font-family: inherit; background: var(--surface);
        }
        .filter-bar select:focus, .filter-bar input:focus {
            outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(99,102,241,.15);
        }

        .pagination nav { display: flex; justify-content: flex-start; }
        .pagination ul { display: flex; gap: 4px; list-style: none; margin: 0; padding: 0; }
        .pagination .page-item .page-link {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 32px; height: 32px; padding: 0 10px;
            border: 1px solid var(--border); border-radius: 6px;
            font-size: .8125rem; color: var(--text); background: var(--surface);
            transition: background .15s, border-color .15s;
        }
        .pagination .page-item .page-link:hover { background: #f1f5f9; text-decoration: none; }
        .pagination .page-item.active .page-link { background: var(--primary); color: #fff; border-color: var(--primary); }
        .pagination .page-item.disabled .page-link { opacity: .45; pointer-events: none; color: var(--text-muted); }

        .modal-backdrop {
            display: none; position: fixed; inset: 0; background: rgba(0,0,0,.4);
            z-index: 1000; align-items: center; justify-content: center;
        }
        .modal-backdrop.open { display: flex; }
        .modal {
            background: var(--surface); border-radius: var(--radius); box-shadow: var(--shadow-md);
            width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto;
        }
        .modal-header {
            padding: 16px 20px; border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
        }
        .modal-title { font-weight: 600; }
        .modal-close { background: none; border: none; cursor: pointer; font-size: 1.25rem; color: var(--text-muted); padding: 4px; }
        .modal-close:hover { color: var(--text); }
        .modal-body { padding: 20px; }

        .grid { display: grid; gap: 16px; }
        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        @media (max-width: 768px) { .grid-2, .grid-3 { grid-template-columns: 1fr; } }

        .flex { display: flex; }
        .flex-wrap { flex-wrap: wrap; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-2 { gap: 8px; }
        .gap-3 { gap: 12px; }
        .mt-2 { margin-top: 8px; }
        .mt-4 { margin-top: 16px; }
        .mb-4 { margin-bottom: 16px; }
        .text-muted { color: var(--text-muted); }
        .text-sm { font-size: .875rem; }
        .font-semibold { font-weight: 600; }
        .w-full { width: 100%; }

        .project-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius); padding: 20px; box-shadow: var(--shadow);
            transition: box-shadow .2s, border-color .2s;
        }
        .project-card:hover { box-shadow: var(--shadow-md); border-color: #c7d2fe; }
        .project-card h3 { font-size: 1rem; font-weight: 600; margin-bottom: 6px; }
        .project-card h3 a { color: var(--text); }
        .project-card h3 a:hover { color: var(--primary); text-decoration: none; }

        .comment-item { padding: 16px 0; border-bottom: 1px solid var(--border); }
        .comment-item:last-child { border-bottom: none; }
        .comment-meta { font-size: .8125rem; color: var(--text-muted); margin-top: 6px; }

        .spinner {
            display: inline-block; width: 16px; height: 16px; border: 2px solid var(--border);
            border-top-color: var(--primary); border-radius: 50%; animation: spin .6s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .tags-panel { display: flex; flex-wrap: wrap; gap: 8px; }

        .issue-detail-grid { display: grid; grid-template-columns: 1fr 280px; gap: 24px; align-items: start; }
        @media (max-width: 900px) { .issue-detail-grid { grid-template-columns: 1fr; } }

        .sidebar-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; }
        .sidebar-card-header { padding: 12px 16px; border-bottom: 1px solid var(--border); font-weight: 600; font-size: .875rem; }
        .sidebar-card-body { padding: 16px; }

        .color-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }

        .empty-state { text-align: center; padding: 48px 24px; color: var(--text-muted); }
        .empty-state p { margin-top: 8px; font-size: .875rem; }

        /* Delete dialog */
        .delete-dialog-backdrop {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.45); z-index: 2000;
            align-items: center; justify-content: center;
            animation: fadeIn .15s ease;
        }
        .delete-dialog-backdrop.open { display: flex; }
        .delete-dialog {
            background: var(--surface); border-radius: var(--radius);
            box-shadow: 0 20px 60px rgba(0,0,0,.2); width: 100%; max-width: 400px;
            padding: 28px; animation: slideUp .18s ease;
        }
        .delete-dialog-icon {
            width: 48px; height: 48px; border-radius: 50%;
            background: #fef2f2; display: flex; align-items: center; justify-content: center;
            margin-bottom: 16px;
        }
        .delete-dialog-icon svg { width: 22px; height: 22px; color: var(--danger); }
        .delete-dialog h3 { font-size: 1rem; font-weight: 600; margin-bottom: 6px; }
        .delete-dialog p { font-size: .875rem; color: var(--text-muted); margin-bottom: 24px; }
        .delete-dialog-actions { display: flex; gap: 8px; justify-content: flex-end; }
        @keyframes fadeIn  { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { transform: translateY(12px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>
    @stack('styles')
</head>
<body>
    <nav class="navbar">
        <a href="{{ route('projects.index') }}" class="navbar-brand">Issue Tracker</a>
        <div class="navbar-nav">
            <a href="{{ route('projects.index') }}" class="nav-link {{ request()->routeIs('projects.*') ? 'active' : '' }}">Projects</a>
            <a href="{{ route('issues.index') }}" class="nav-link {{ request()->routeIs('issues.index') ? 'active' : '' }}">All Issues</a>
            <a href="{{ route('tags.index') }}" class="nav-link {{ request()->routeIs('tags.*') ? 'active' : '' }}">Tags</a>
        </div>
        @auth
        <div class="navbar-user">
            <span>{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="navbar-logout">Sign out</button>
            </form>
        </div>
        @endauth
    </nav>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success js-flash">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error js-flash">{{ session('error') }}</div>
        @endif

        @yield('content')
    </div>

    @stack('scripts')

    {{-- Global delete confirmation dialog --}}
    <div class="delete-dialog-backdrop" id="delete-dialog-backdrop">
        <div class="delete-dialog" role="dialog" aria-modal="true" aria-labelledby="delete-dialog-title">
            <div class="delete-dialog-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
            </div>
            <h3 id="delete-dialog-title">Delete confirmation</h3>
            <p id="delete-dialog-message">Are you sure you want to delete this? This action cannot be undone.</p>
            <div class="delete-dialog-actions">
                <button class="btn btn-secondary" id="delete-dialog-cancel">Cancel</button>
                <button class="btn btn-danger" id="delete-dialog-confirm">Delete</button>
            </div>
        </div>
    </div>

    <script>
    // Auto-dismiss flash messages after 5 seconds
    document.querySelectorAll('.js-flash').forEach(function (el) {
        setTimeout(function () { el.classList.add('fade-out'); }, 2000);
    });
    </script>

    <script>
    (function () {
        const backdrop = document.getElementById('delete-dialog-backdrop');
        const msgEl    = document.getElementById('delete-dialog-message');
        const titleEl  = document.getElementById('delete-dialog-title');
        const confirmBtn = document.getElementById('delete-dialog-confirm');
        const cancelBtn  = document.getElementById('delete-dialog-cancel');
        let pendingForm  = null;

        function openDialog(form) {
            pendingForm = form;
            titleEl.textContent  = form.dataset.dialogTitle   || 'Delete confirmation';
            msgEl.textContent    = form.dataset.dialogMessage || 'Are you sure you want to delete this? This action cannot be undone.';
            confirmBtn.textContent = form.dataset.dialogConfirm || 'Delete';
            backdrop.classList.add('open');
            cancelBtn.focus();
        }

        function closeDialog() {
            backdrop.classList.remove('open');
            pendingForm = null;
        }

        // Intercept any form with data-confirm-delete
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!form.hasAttribute('data-confirm-delete')) return;
            if (form._confirmed) return;
            e.preventDefault();
            openDialog(form);
        });

        confirmBtn.addEventListener('click', function () {
            if (!pendingForm) return;
            const form = pendingForm;
            form._confirmed = true;
            closeDialog();
            form.submit();
        });

        cancelBtn.addEventListener('click', closeDialog);

        backdrop.addEventListener('click', function (e) {
            if (e.target === backdrop) closeDialog();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && backdrop.classList.contains('open')) closeDialog();
        });
    })();
    </script>
</body>
</html>
