<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ログイン — 日報システム</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="login-page">
    <div class="login-decoration decoration-one"></div>
    <div class="login-decoration decoration-two"></div>
    <main class="login-layout">
        <section class="login-intro">
            <a class="brand login-brand" href="{{ route('login') }}">
                <span class="brand-mark">d<span>.</span></span>
                <span class="brand-copy"><strong>daily</strong><small>日報システム</small></span>
            </a>
            <div class="intro-copy">
                <span class="intro-kicker"><span class="status-pulse"></span> YOUR TEAM, IN SYNC</span>
                <h1>毎日の仕事に、<br>ちょうどいい<br><span>つながりを。</span></h1>
                <p>日々の取り組みを記録し、チームで共有する。<br>日報が、明日の仕事を少しずつ動かします。</p>
            </div>
            <div class="intro-flow">
                <div><span class="flow-number">01</span><strong>記録</strong><small>自分の仕事を残す</small></div>
                <span class="flow-arrow">→</span>
                <div><span class="flow-number">02</span><strong>確認</strong><small>チームで見守る</small></div>
                <span class="flow-arrow">→</span>
                <div><span class="flow-number">03</span><strong>共有</strong><small>組織へ広げる</small></div>
            </div>
            <p class="intro-caption">毎日の記録を、チームの力に。</p>
        </section>

        <section class="login-panel">
            <div class="login-panel-inner">
                <span class="login-icon">✳</span>
                <p class="eyebrow">WELCOME BACK</p>
                <h2>日報システムへログイン</h2>
                <p class="login-description">社員IDとパスワードを入力してください。</p>

                @if($errors->any())
                    <div class="flash flash-error" role="alert">
                        <span>!</span>
                        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="login-form">
                    @csrf
                    <label class="field-label" for="login_id">ログインID</label>
                    <div class="input-with-icon">
                        <span class="input-icon">♙</span>
                        <input id="login_id" name="login_id" type="text" value="{{ old('login_id') }}" placeholder="例：E1024" autocomplete="username" required autofocus>
                    </div>
                    <label class="field-label password-label" for="password">パスワード</label>
                    <div class="input-with-icon">
                        <span class="input-icon">⌑</span>
                        <input id="password" name="password" type="password" placeholder="パスワードを入力" autocomplete="current-password" required>
                    </div>
                    <label class="remember-label"><input type="checkbox" name="remember" value="1"> ログイン状態を保持する</label>
                    <button class="button button-primary login-submit" type="submit">ログイン <span>→</span></button>
                </form>

                <div class="demo-credentials">
                    <div class="demo-title"><span>✦</span> デモアカウント</div>
                    <div class="demo-row"><span>メンバー</span><strong>E1024</strong><small>日報作成・提出</small></div>
                    <div class="demo-row"><span>リーダー</span><strong>E1025</strong><small>確認・コメント</small></div>
                    <div class="demo-row"><span>管理者</span><strong>E1088</strong><small>社員・組織マスタ</small></div>
                    <p>パスワードはすべて <code>password</code> です。</p>
                </div>
            </div>
        </section>
    </main>
    <footer class="login-footer">© DAILY REPORT <span>•</span> 日々の記録を、チームの力に。</footer>
</body>
</html>
