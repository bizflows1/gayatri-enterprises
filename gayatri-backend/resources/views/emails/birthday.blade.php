<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8fafc; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%); padding: 40px 20px; text-align: center; color: white; }
        .header h1 { margin: 0; font-size: 28px; font-weight: 800; }
        .content { padding: 40px 30px; text-align: center; color: #334155; }
        .content p { font-size: 16px; line-height: 1.6; margin-bottom: 20px; }
        .name { font-size: 24px; font-weight: bold; color: #0f172a; margin-bottom: 10px; }
        .footer { background: #f1f5f9; padding: 20px; text-align: center; font-size: 12px; color: #64748b; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1> Happy Birthday! </h1>
        </div>
        <div class="content">
            <div class="name">Dear {{ explode(' ', $user->name)[0] }},</div>
            <p>Wishing you a very Happy Birthday! May your special day be filled with joy, laughter, and wonderful memories.</p>
            <p>We hope the year ahead brings you success, health, and happiness.</p>
            <p>Best Wishes,<br><strong>Gayatri Enterprises</strong></p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Gayatri Enterprises. All rights reserved.
        </div>
    </div>
</body>
</html>
