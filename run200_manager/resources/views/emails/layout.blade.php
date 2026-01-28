<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Run200 Manager' }}</title>
    <style>
        /* Reset & Base */
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #E0E0E0;
            background-color: #121212;
            margin: 0;
            padding: 20px;
        }

        /* Email Container */
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #1E1E1E;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #333333;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.4);
        }

        /* Header with Racing Gradient */
        .email-header {
            background: linear-gradient(135deg, #E53935 0%, #B71C1C 50%, #8E0000 100%);
            padding: 30px;
            text-align: center;
            position: relative;
        }
        .email-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 0h20v20H0V0zm20 20h20v20H20V20z' fill='%23000000' fill-opacity='0.1'/%3E%3C/svg%3E");
            opacity: 0.3;
        }
        .email-header-content {
            position: relative;
            z-index: 1;
        }
        .email-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .email-header p {
            margin: 8px 0 0;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.85);
        }

        /* Body */
        .email-body {
            padding: 40px 30px;
            background-color: #1E1E1E;
        }
        .email-body h2 {
            color: #FFFFFF;
            font-size: 22px;
            margin-top: 0;
            margin-bottom: 20px;
        }
        .email-body p {
            color: #BDBDBD;
            margin: 15px 0;
        }
        .email-body strong {
            color: #FFFFFF;
        }

        /* Footer */
        .email-footer {
            background-color: #161616;
            padding: 25px 30px;
            text-align: center;
            border-top: 1px solid #333333;
        }
        .email-footer p {
            font-size: 12px;
            color: #757575;
            margin: 5px 0;
        }
        .email-footer a {
            color: #E53935;
            text-decoration: none;
        }
        .email-footer a:hover {
            text-decoration: underline;
        }

        /* Racing Button */
        .button {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #E53935 0%, #C62828 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 20px 0;
            box-shadow: 0 4px 14px rgba(229, 57, 53, 0.4);
            transition: all 0.3s ease;
        }
        .button:hover {
            background: linear-gradient(135deg, #C62828 0%, #B71C1C 100%);
            box-shadow: 0 6px 20px rgba(229, 57, 53, 0.5);
        }

        /* Secondary Button */
        .button-secondary {
            background: linear-gradient(135deg, #FFD600 0%, #FFC400 100%);
            color: #121212 !important;
            box-shadow: 0 4px 14px rgba(255, 214, 0, 0.3);
        }
        .button-secondary:hover {
            background: linear-gradient(135deg, #FFC400 0%, #FFAB00 100%);
        }

        /* Info Box - Racing Blue */
        .info-box {
            background: linear-gradient(135deg, rgba(0, 176, 255, 0.1) 0%, rgba(0, 176, 255, 0.05) 100%);
            border-left: 4px solid #00B0FF;
            padding: 20px;
            margin: 25px 0;
            border-radius: 0 8px 8px 0;
        }
        .info-box h3 {
            margin-top: 0;
            color: #00B0FF;
            font-size: 16px;
        }
        .info-box p {
            color: #BDBDBD;
            margin: 10px 0 0;
        }

        /* Warning Box - Racing Yellow */
        .warning-box {
            background: linear-gradient(135deg, rgba(255, 214, 0, 0.15) 0%, rgba(255, 214, 0, 0.05) 100%);
            border-left: 4px solid #FFD600;
            padding: 20px;
            margin: 25px 0;
            border-radius: 0 8px 8px 0;
        }
        .warning-box h3 {
            margin-top: 0;
            color: #FFD600;
            font-size: 16px;
        }
        .warning-box p, .warning-box li {
            color: #BDBDBD;
        }

        /* Success Box - Racing Green */
        .success-box {
            background: linear-gradient(135deg, rgba(0, 200, 83, 0.15) 0%, rgba(0, 200, 83, 0.05) 100%);
            border-left: 4px solid #00C853;
            padding: 20px;
            margin: 25px 0;
            border-radius: 0 8px 8px 0;
        }
        .success-box h3 {
            margin-top: 0;
            color: #00C853;
            font-size: 16px;
        }

        /* Danger Box - Racing Red */
        .danger-box {
            background: linear-gradient(135deg, rgba(255, 23, 68, 0.15) 0%, rgba(255, 23, 68, 0.05) 100%);
            border-left: 4px solid #FF1744;
            padding: 20px;
            margin: 25px 0;
            border-radius: 0 8px 8px 0;
        }
        .danger-box h3 {
            margin-top: 0;
            color: #FF1744;
            font-size: 16px;
        }

        /* Detail Lines */
        .detail-line {
            padding: 12px 0;
            border-bottom: 1px solid #333333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .detail-line:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #9E9E9E;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .detail-value {
            color: #FFFFFF;
            font-weight: 500;
        }

        /* Racing Card */
        .racing-card {
            background: linear-gradient(135deg, #252525 0%, #1E1E1E 100%);
            border: 1px solid #333333;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
        }
        .racing-card-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #333333;
        }
        .racing-card-title {
            color: #FFFFFF;
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }

        /* QR Code Container */
        .qr-container {
            background: linear-gradient(135deg, #E53935 0%, #B71C1C 100%);
            padding: 25px;
            border-radius: 12px;
            margin: 25px 0;
            text-align: center;
        }
        .qr-container h3 {
            color: #ffffff;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .qr-code-box {
            background: #ffffff;
            padding: 15px;
            border-radius: 8px;
            display: inline-block;
        }
        .qr-container p {
            color: rgba(255, 255, 255, 0.9);
            margin-top: 15px;
            margin-bottom: 0;
            font-size: 14px;
        }

        /* Race Number Badge */
        .race-number {
            display: inline-block;
            background: linear-gradient(135deg, #FFD600 0%, #FFC400 100%);
            color: #121212;
            font-weight: 700;
            font-size: 18px;
            padding: 8px 16px;
            border-radius: 6px;
            font-family: 'Monaco', 'Consolas', monospace;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-success {
            background: rgba(0, 200, 83, 0.2);
            color: #00C853;
        }
        .status-warning {
            background: rgba(255, 145, 0, 0.2);
            color: #FF9100;
        }
        .status-danger {
            background: rgba(255, 23, 68, 0.2);
            color: #FF1744;
        }
        .status-pending {
            background: rgba(124, 77, 255, 0.2);
            color: #7C4DFF;
        }

        /* Lists */
        ul, ol {
            color: #BDBDBD;
            padding-left: 20px;
        }
        li {
            margin: 8px 0;
        }
        li strong {
            color: #FFFFFF;
        }

        /* Divider */
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #333333, transparent);
            margin: 30px 0;
        }

        /* Signature */
        .signature {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #333333;
            color: #9E9E9E;
        }
        .signature strong {
            color: #E53935;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-header">
            <div class="email-header-content">
                <h1>🏁 Run200</h1>
                <p>Système de gestion de courses automobiles</p>
            </div>
        </div>

        <div class="email-body">
            @yield('content')
        </div>

        <div class="email-footer">
            <p><strong style="color: #E53935;">Run200 Manager</strong></p>
            <p>Vous recevez cet email car vous êtes inscrit sur notre plateforme.</p>
            <p style="margin-top: 15px;">
                <a href="{{ config('app.url') }}">Accéder à la plateforme</a>
            </p>
        </div>
    </div>
</body>
</html>
