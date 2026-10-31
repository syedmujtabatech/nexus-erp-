<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexus ERP & CRM Security Verification</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f1f5f9; margin: 0; padding: 40px 20px;">
    <div style="max-width: 520px; margin: 0 auto; background: #ffffff; border-radius: 14px; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08); overflow: hidden; border: 1px solid #e2e8f0;">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #1e1b4b 0%, #2563eb 100%); padding: 32px 25px; text-align: center;">
            <h2 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 800; letter-spacing: -0.5px;">
                NEXUS<span style="color: #38bdf8;">ERP</span>
            </h2>
            <p style="color: #cbd5e1; margin: 6px 0 0; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Enterprise Security</p>
        </div>

        <!-- Body -->
        <div style="padding: 35px 30px; text-align: center;">
            <h3 style="color: #0f172a; margin-top: 0; font-size: 20px; font-weight: 700;">Account Verification Code</h3>
            <p style="color: #475569; font-size: 15px; line-height: 1.6; margin-bottom: 25px;">
                Hello <strong>{{ $userName }}</strong>,<br>
                Please use the following 6-digit One-Time Password (OTP) to verify your identity and unlock your workspace:
            </p>

            <div style="margin: 30px 0;">
                <span style="display: inline-block; font-size: 36px; font-weight: 800; letter-spacing: 10px; color: #2563eb; background: #eff6ff; border: 2px dashed #93c5fd; padding: 14px 28px; border-radius: 12px; font-family: monospace;">
                    {{ $otp }}
                </span>
            </div>

            <p style="color: #64748b; font-size: 13px; margin-bottom: 0;">
                This code is valid for 10 minutes. If you did not initiate this request, please change your password immediately.
            </p>
        </div>

        <!-- Footer -->
        <div style="background: #f8fafc; padding: 20px 30px; text-align: center; border-top: 1px solid #f1f5f9;">
            <p style="color: #94a3b8; font-size: 12px; margin: 0;">
                &copy; {{ date('Y') }} Nexus ERP & CRM — Enterprise Cloud Platform. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
