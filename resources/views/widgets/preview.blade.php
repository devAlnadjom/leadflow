<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Widget Preview - {{ $leadForm->name }}</title>
    <style>
        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            background: #f8fafc;
            color: #0f172a;
        }

        .container {
            max-width: 980px;
            margin: 0 auto;
            padding: 24px;
        }

        .card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 26px;
        }

        .meta {
            color: #475569;
            margin-bottom: 16px;
        }

        code {
            display: block;
            white-space: pre-wrap;
            word-break: break-word;
            background: #0f172a;
            color: #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            font-size: 13px;
        }

        .host-preview {
            min-height: 520px;
            border: 1px dashed #94a3b8;
            border-radius: 10px;
            padding: 16px;
            background: #ffffff;
            position: relative;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            background: {{ $leadForm->is_active ? '#dcfce7' : '#e2e8f0' }};
            color: {{ $leadForm->is_active ? '#166534' : '#334155' }};
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <h1>{{ $leadForm->name }}</h1>
        <div class="meta">Widget key: <strong>{{ $leadForm->embed_key }}</strong></div>
        <span class="badge">{{ $leadForm->is_active ? 'Active' : 'Inactive' }}</span>
    </div>

    <div class="card">
        <h2>Code d'integration</h2>
        <p>Copiez ce snippet sur votre site web.</p>
        <code>{{ $integrationScript }}</code>
    </div>

    <div class="card">
        <h2>Preview (script reel)</h2>
        <p>La page ci-dessous integre uniquement le script, sans simulation manuelle.</p>
        <div class="host-preview">
            <p>Page client de demonstration...</p>
            {!! $integrationScript !!}
        </div>
    </div>
</div>
</body>
</html>
