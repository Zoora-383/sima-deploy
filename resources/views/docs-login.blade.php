<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Dokumentasi API</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', -apple-system, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 16px;
            padding: 48px 40px;
            width: 400px;
            max-width: 90vw;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .login-card h1 {
            font-size: 24px;
            color: #1a1a2e;
            margin-bottom: 8px;
            text-align: center;
        }
        .login-card .subtitle {
            color: #666;
            font-size: 14px;
            text-align: center;
            margin-bottom: 32px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.2s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .btn-login:hover { opacity: 0.9; }
        .error { color: #e74c3c; font-size: 13px; margin-top: 4px; }
        .error-box {
            background: #fef0f0;
            border: 1px solid #fde2e2;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h1>Dokumentasi API</h1>
        <p class="subtitle">Masukkan kredensial untuk mengakses dokumentasi</p>

        @if ($errors->any())
            <div class="error-box">
                @foreach ($errors->all() as $error)
                    <p class="error">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('docs.login.submit') }}">
            @csrf
            <div class="form-group">
                <label for="identifier">Identifier</label>
                <input type="text" id="identifier" name="identifier"
                       placeholder="Masukkan identifier" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       placeholder="Masukkan password" required>
            </div>
            <button type="submit" class="btn-login">Masuk</button>
        </form>
    </div>
</body>
</html>
