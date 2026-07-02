<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Login Credentials - Gayatri Enterprises</title>
    <style>
        body { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; 
            line-height: 1.6; 
            color: #1e293b; 
            background-color: #f8fafc; 
            margin: 0; 
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            background-color: #f8fafc;
            padding: 40px 20px;
            box-sizing: border-box;
        }
        .container { 
            max-width: 580px; 
            margin: 0 auto; 
            background-color: #ffffff;
            border-radius: 16px; 
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.05);
            border: 1px solid #e2e8f0; 
        }
        .header { 
            text-align: center; 
            padding: 35px 30px; 
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: #ffffff;
        }
        .logo {
            width: 72px;
            height: 72px;
            border-radius: 16px;
            background-color: #ffffff;
            padding: 6px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            display: inline-block;
            margin-bottom: 15px;
        }
        .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .header h1 {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.025em;
            line-height: 1.3;
        }
        .header p {
            font-size: 13px;
            color: #bfdbfe;
            margin: 6px 0 0 0;
            font-weight: 500;
        }
        .content { 
            padding: 40px 35px; 
        }
        .greeting {
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
            margin-top: 0;
            margin-bottom: 12px;
        }
        .intro-text {
            font-size: 14px;
            color: #475569;
            margin: 0 0 25px 0;
            line-height: 1.6;
        }
        .credential-card { 
            background: #f0f7ff; 
            padding: 24px; 
            border-radius: 12px; 
            border: 1px solid #d0e7ff; 
            margin-bottom: 30px;
        }
        .credential-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #1d4ed8;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 16px;
            border-bottom: 1px dashed #bfdbfe;
            padding-bottom: 8px;
        }
        .credential-item { 
            margin: 10px 0; 
            font-size: 14px;
            color: #334155;
            display: flex;
            justify-content: space-between;
        }
        .label { 
            font-weight: 600; 
            color: #475569; 
            width: 140px;
            display: inline-block;
        }
        .val {
            color: #0f172a;
            font-weight: 500;
        }
        .password-badge {
            background-color: #ffffff;
            color: #0f172a;
            padding: 4px 8px;
            border-radius: 6px;
            font-family: 'Courier New', Courier, monospace;
            font-weight: 700;
            border: 1px solid #cbd5e1;
            font-size: 14px;
        }
        .btn-container {
            text-align: center;
            margin: 30px 0;
        }
        .btn { 
            display: inline-block; 
            padding: 14px 32px; 
            background-color: #1e3a8a; 
            color: #ffffff !important; 
            text-decoration: none; 
            border-radius: 10px; 
            font-weight: 600; 
            font-size: 14px;
            transition: background-color 0.2s;
            box-shadow: 0 4px 12px rgba(30, 58, 138, 0.2);
        }
        .security-note {
            font-size: 12px;
            color: #64748b;
            background-color: #f8fafc;
            padding: 14px 18px;
            border-radius: 8px;
            border-left: 3px solid #64748b;
            margin-bottom: 0;
        }
        .footer { 
            text-align: center; 
            padding: 30px; 
            font-size: 12px; 
            color: #64748b; 
            border-top: 1px solid #f1f5f9;
        }
        .footer p {
            margin: 5px 0;
        }
        .socials {
            margin-top: 15px;
            font-size: 11px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <!-- Brand Header -->
            <div class="header">
                <div class="logo">
                    <img src="https://gayatrient.com/pwa-icon.png" alt="Gayatri Enterprises Logo">
                </div>
                <h1>Gayatri Enterprises</h1>
                <p>Chartered Accountants &bull; Client Portal Credentials</p>
            </div>
            
            <!-- Email Content -->
            <div class="content">
                <p class="greeting">Dear {{ $user->name }},</p>
                <p class="intro-text">
                    Your personal client portal account with <strong>Gayatri Enterprises</strong> has been set up successfully. You can now securely access your documents, check updates, and track services.
                </p>
                
                <!-- Credentials Card -->
                <div class="credential-card">
                    <h3 class="credential-title">Your Login Credentials</h3>
                    
                    <div class="credential-item">
                        <span class="label">Portal ID (Email):</span>
                        <span class="val">{{ $user->email }}</span>
                    </div>
                    
                    @if(!empty($user->phone))
                    <div class="credential-item">
                        <span class="label">Portal ID (Phone):</span>
                        <span class="val">{{ $user->phone }}</span>
                    </div>
                    @endif
                    
                    <div class="credential-item">
                        <span class="label">Password:</span>
                        <span class="val"><code class="password-badge">{{ $plainPassword }}</code></span>
                    </div>
                </div>

                <!-- Call To Action -->
                <p class="intro-text" style="text-align: center; margin-bottom: 10px;">Click below to access your secure dashboard:</p>
                <div class="btn-container">
                    <a href="https://gayatrient.com/portal" class="btn" target="_blank">Login to Client Portal</a>
                </div>
                
                <!-- Safety advice -->
                <p class="security-note">
                    <strong>Security Notice:</strong> We highly recommend that you log in and change this auto-generated password from your account profile settings page immediately.
                </p>
            </div>
            
            <!-- Footer -->
            <div class="footer">
                <p>&copy; {{ date('Y') }} Gayatri Enterprises. All rights reserved.</p>
                <p>This is an automated administrative notification, please do not reply directly to this mail.</p>
                <div class="socials">
                    Website: <a href="https://gayatrient.com" style="color: #1e3a8a; text-decoration: none; font-weight: 500;">gayatrient.com</a> | Address: Gayatri Enterprises
                </div>
            </div>
        </div>
    </div>
</body>
</html>
