<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Assignment</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #111827; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.07); }
        .header { background: linear-gradient(135deg, #1d4ed8 0%, #4f46e5 100%); padding: 36px 40px; text-align: center; }
        .header-logo { font-size: 22px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px; }
        .header-logo span { opacity: 0.7; }
        .body { padding: 40px; }
        .greeting { font-size: 20px; font-weight: 700; color: #111827; margin-bottom: 12px; }
        .text { font-size: 15px; line-height: 1.7; color: #374151; margin-bottom: 16px; }
        .scope-card { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 20px 24px; margin: 24px 0; }
        .scope-card .type { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.8px; color: #3b82f6; margin-bottom: 4px; }
        .scope-card .name { font-size: 18px; font-weight: 700; color: #1e3a8a; }
        .notice { background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 14px 18px; margin: 20px 0; font-size: 13px; color: #92400e; line-height: 1.6; }
        .notice strong { font-weight: 600; }
        .btn-wrap { text-align: center; margin: 32px 0; }
        .btn { display: inline-block; background: #1d4ed8; color: #ffffff; text-decoration: none; font-size: 15px; font-weight: 600; padding: 14px 36px; border-radius: 10px; }
        .divider { border: none; border-top: 1px solid #e5e7eb; margin: 28px 0; }
        .footer { padding: 0 40px 32px; text-align: center; font-size: 12px; color: #9ca3af; line-height: 1.7; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <div class="header-logo">Uni<span>One</span></div>
        </div>

        <div class="body">
            <p class="greeting">Hello, {{ $user->first_name }}!</p>

            <p class="text">
                You have been assigned as a <strong>{{ $scopeType }} Administrator</strong> on the UniOne platform.
                You now have full administrative access to your assigned {{ strtolower($scopeType) }}.
            </p>

            <div class="scope-card">
                <div class="type">{{ $scopeType }}</div>
                <div class="name">{{ $scopeName }}</div>
            </div>

            <p class="text">
                Use your existing email address and password to log in to the dashboard.
            </p>

            <div class="notice">
                <strong>Action required:</strong> For security, you will be prompted to set a new password the first time you log in with your administrator account.
            </div>

            <div class="btn-wrap">
                <a href="{{ $loginUrl }}" class="btn">Go to Dashboard</a>
            </div>

            <p class="text" style="font-size:13px; color:#6b7280;">
                If you have any questions, please contact the system administrator.
            </p>
        </div>

        <hr class="divider">

        <div class="footer">
            <p>This email was sent by UniOne. Please do not reply directly to this email.</p>
            <p style="margin-top:6px;">Login: <strong>{{ $user->email }}</strong></p>
        </div>
    </div>
</body>
</html>
