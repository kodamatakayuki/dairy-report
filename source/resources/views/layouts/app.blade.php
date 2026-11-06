<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '日報') — Daily</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <a class="brand" href="{{ route('dashboard') }}">
            <span class="brand-mark">d<span>.</span></span>
            <span class="brand-copy"><strong>daily</strong><small>日報システム</small></span>
        </a>

        <div class="sidebar-section">
            <p class="sidebar-label">WORKSPACE</p>
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}" href="{{ route('dashboard') }}">
                <span class="nav-icon">⌂</span><span>ホーム</span>
            </a>
            <a class="nav-link {{ request()->routeIs('reports.*') ? 'is-active' : '' }}" href="{{ route('reports.index') }}">
                <span class="nav-icon">▤</span><span>日報</span>
            </a>
            @if(auth()->user()->isLeader())
                <a class="nav-link {{ request()->routeIs('reviews.*') ? 'is-active' : '' }}" href="{{ route('reviews.index') }}">
                    <span class="nav-icon">✓</span><span>確認待ち</span>
                </a>
            @endif
        </div>

        @if(auth()->user()->isAdmin())
            <div class="sidebar-section">
                <p class="sidebar-label">ADMINISTRATION</p>
                <a class="nav-link {{ request()->routeIs('admin.employees.*') ? 'is-active' : '' }}" href="{{ route('admin.employees.index') }}">
                    <span class="nav-icon">♙</span><span>社員マスタ</span>
                </a>
                <a class="nav-link {{ request()->routeIs('admin.organizations.*') ? 'is-active' : '' }}" href="{{ route('admin.organizations.index') }}">
                    <span class="nav-icon">⌘</span><span>組織マスタ</span>
                </a>
            </div>
        @endif

        <div class="sidebar-footer">
            <div class="sidebar-status"><span class="status-pulse"></span> システム稼働中</div>
            <div class="profile-card">
                <span class="avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                <span class="profile-copy"><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->login_id }}</small></span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="icon-button logout-button" type="submit" aria-label="ログアウト" title="ログアウト">↗</button>
                </form>
            </div>
        </div>
    </aside>

    <main class="main-area">
        <header class="topbar">
            <span class="breadcrumb">日報システム <span>/</span> @yield('title', 'ホーム')</span>
            <span class="topbar-date">{{ now()->isoFormat('YYYY年 M月 D日（ddd）') }}</span>
        </header>

        <div class="page-content">
            @if(session('success'))
                <div class="flash flash-success" role="status"><span>✓</span> {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="flash flash-error" role="alert"><span>!</span> {{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="flash flash-error" role="alert">
                    <span>!</span>
                    <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="page-heading">
                <div>
                    @hasSection('eyebrow')
                        <p class="eyebrow">@yield('eyebrow')</p>
                    @endif
                    <h1>@yield('heading', View::yieldContent('title', '日報'))</h1>
                    @hasSection('subtitle')
                        <p class="page-subtitle">@yield('subtitle')</p>
                    @endif
                </div>
                @yield('heading-action')
            </div>

            @yield('content')

            <footer class="page-footer">
                <span>DAILY <span class="footer-dot">●</span> 日々の記録を、チームの力に。</span>
                <span>日報システム</span>
            </footer>
        </div>
    </main>
</div>
@stack('scripts')
</body>
</html>
