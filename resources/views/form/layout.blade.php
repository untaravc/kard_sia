<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $form->title ?? 'Formulir')</title>
    <style>
        :root {
            --brand: #014385;
            --brand-dark: #01305f;
            --bg: #f0ebf8;
            --card: #ffffff;
            --text: #202124;
            --muted: #5f6368;
            --border: #dadce0;
            --danger: #d93025;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: 'Roboto', 'Segoe UI', Helvetica, Arial, sans-serif;
            line-height: 1.5;
            padding: 24px 12px 80px;
        }

        .fw {
            max-width: 640px;
            margin: 0 auto;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 24px;
            margin-bottom: 14px;
            box-shadow: 0 1px 2px rgba(60, 64, 67, .1);
        }

        .card.header {
            border-top: 10px solid var(--brand);
            padding-top: 22px;
        }

        .card.header h1 {
            font-size: 26px;
            font-weight: 500;
            margin: 0 0 8px;
        }

        .card.header p {
            color: var(--muted);
            margin: 0;
            white-space: pre-line;
        }

        .badge-login {
            display: inline-block;
            margin-top: 14px;
            font-size: 13px;
            color: var(--muted);
            background: #f1f3f4;
            border-radius: 16px;
            padding: 4px 12px;
        }

        .field label.q {
            display: block;
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .field .help {
            color: var(--muted);
            font-size: 13px;
            margin: 0 0 12px;
        }

        .req { color: var(--danger); margin-left: 3px; }

        input[type=text],
        input[type=email],
        input[type=number],
        input[type=date],
        select,
        textarea {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 15px;
            font-family: inherit;
            color: var(--text);
            background: #fff;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--brand);
            box-shadow: 0 0 0 1px var(--brand);
        }

        textarea { min-height: 96px; resize: vertical; }

        .opt {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 7px 0;
            font-size: 15px;
            cursor: pointer;
        }

        .opt input { width: 18px; height: 18px; accent-color: var(--brand); }

        .scale { display: flex; flex-wrap: wrap; gap: 8px; }

        .scale-item { cursor: pointer; }
        .scale-item input { position: absolute; opacity: 0; width: 0; height: 0; }

        .scale-num {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 44px;
            height: 44px;
            padding: 0 8px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 15px;
            font-weight: 500;
            color: var(--text);
            background: #fff;
            transition: border-color .1s, background .1s, color .1s;
        }

        .scale-item:hover .scale-num { border-color: var(--brand); }
        .scale-item input:focus + .scale-num { box-shadow: 0 0 0 2px rgba(1, 67, 133, .25); }
        .scale-item input:checked + .scale-num {
            border-color: var(--brand);
            background: var(--brand);
            color: #fff;
        }

        .invalid { color: var(--danger); font-size: 13px; margin-top: 6px; }

        .actions { display: flex; justify-content: space-between; align-items: center; margin-top: 6px; }

        .btn {
            border: none;
            border-radius: 6px;
            padding: 11px 26px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            background: var(--brand);
            color: #fff;
        }
        .btn:hover { background: var(--brand-dark); }

        .btn-link {
            background: none;
            color: var(--brand);
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
        }

        .note { color: var(--muted); font-size: 12px; text-align: center; margin-top: 20px; }

        .state { text-align: center; padding: 40px 24px; }
        .state .emoji { font-size: 44px; }
        .state h2 { font-weight: 500; margin: 12px 0 6px; }
        .state p { color: var(--muted); margin: 0; }
    </style>
</head>
<body>
<div class="fw">
    @yield('content')
    <div class="note">Powered by {{ setting('app.name', 'App - Name') }} &middot; Formulir</div>
</div>
</body>
</html>
