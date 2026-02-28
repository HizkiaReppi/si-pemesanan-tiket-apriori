@props(['subject' => null, 'headerSubtitle' => null])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? config('app.name') }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f5f7;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1a1a2e;
            line-height: 1.6;
            -webkit-text-size-adjust: 100%;
        }
        .email-wrapper {
            width: 100%;
            background-color: #f4f5f7;
            padding: 32px 16px;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        }
        .email-header {
            background: linear-gradient(135deg, #0284c7, #0ea5e9);
            padding: 32px 24px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            color: #ffffff;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }
        .email-header p {
            margin: 6px 0 0;
            color: rgba(255,255,255,0.85);
            font-size: 13px;
        }
        .email-body {
            padding: 32px 24px;
        }
        .email-body h2 {
            margin: 24px 0 12px;
            font-size: 16px;
            font-weight: 700;
            color: #1a1a2e;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 8px;
        }
        .email-body h2:first-child {
            margin-top: 0;
        }
        .email-body p {
            margin: 0 0 12px;
            font-size: 14px;
            color: #334155;
        }
        .info-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 16px;
            margin: 16px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 14px;
        }
        .info-label {
            color: #64748b;
        }
        .info-value {
            color: #1a1a2e;
            font-weight: 600;
            text-align: right;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            font-size: 14px;
        }
        .items-table th {
            background-color: #f1f5f9;
            padding: 10px 12px;
            text-align: left;
            font-weight: 600;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }
        .items-table th:last-child {
            text-align: right;
        }
        .items-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }
        .items-table td:last-child {
            text-align: right;
        }
        .total-row td {
            font-weight: 700;
            color: #1a1a2e;
            border-top: 2px solid #e2e8f0;
            border-bottom: none;
            padding-top: 12px;
        }
        .discount-row td {
            color: #16a34a;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #0284c7, #0ea5e9);
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 32px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            margin: 20px 0;
            text-align: center;
        }
        .btn-block {
            display: block;
            text-align: center;
        }
        .alert-box {
            border-radius: 10px;
            padding: 14px 16px;
            margin: 16px 0;
            font-size: 13px;
        }
        .alert-warning {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
        }
        .alert-info {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
        }
        .alert-success {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }
        .qr-container {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            background-color: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
        }
        .email-footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 24px;
            text-align: center;
        }
        .email-footer p {
            margin: 0;
            font-size: 12px;
            color: #94a3b8;
        }
        .email-footer a {
            color: #0284c7;
            text-decoration: none;
        }
        .divider {
            border: none;
            border-top: 1px solid #e2e8f0;
            margin: 20px 0;
        }
        ol, ul {
            padding-left: 20px;
            margin: 8px 0 16px;
        }
        li {
            font-size: 14px;
            color: #334155;
            margin-bottom: 4px;
        }
        strong {
            color: #1a1a2e;
        }
        .bank-info {
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 10px;
            padding: 16px;
            margin: 12px 0;
        }
        .bank-info p {
            margin: 2px 0;
        }
        .text-center {
            text-align: center;
        }
        .text-muted {
            color: #94a3b8;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                <h1>🏊 Kolam Po'opo</h1>
                <p>{{ $headerSubtitle ?? 'Wisata Air Panas Alami' }}</p>
            </div>

            <div class="email-body">
                {{ $slot }}
            </div>

            <div class="email-footer">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Semua hak dilindungi.</p>
                <p style="margin-top: 4px;">Jl. Raya Kolam Po'opo, Minahasa Utara, Sulawesi Utara</p>
            </div>
        </div>
    </div>
</body>
</html>
