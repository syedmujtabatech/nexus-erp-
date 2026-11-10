<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign in – Google Accounts</title>
    <link rel="icon" type="image/x-icon" href="https://www.google.com/favicon.ico" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet" />
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f0f4f9;
            color: #1f1f1f;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1rem;
        }
        .google-card {
            background: #ffffff;
            border-radius: 28px;
            width: 100%;
            max-width: 480px;
            padding: 2.5rem 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e0e3e7;
        }
        .google-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .google-header h1 {
            font-size: 1.5rem;
            font-weight: 400;
            margin-top: 1rem;
            color: #1f1f1f;
        }
        .google-header p {
            font-size: 0.95rem;
            color: #444746;
            margin-top: 0.35rem;
        }
        .account-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }
        .account-item {
            display: flex;
            align-items: center;
            padding: 0.85rem 1rem;
            border-radius: 12px;
            border: 1px solid #e0e3e7;
            background: #ffffff;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            color: inherit;
        }
        .account-item:hover {
            background: #f8fafc;
            border-color: #1a73e8;
            box-shadow: 0 2px 6px rgba(26, 115, 232, 0.15);
        }
        .avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #1a73e8;
            color: #ffffff;
            font-size: 1.15rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        .account-info {
            flex-grow: 1;
        }
        .account-name {
            font-size: 0.95rem;
            font-weight: 500;
            color: #1f1f1f;
        }
        .account-email {
            font-size: 0.82rem;
            color: #5f6368;
            margin-top: 2px;
        }
        .account-badge {
            font-size: 0.72rem;
            padding: 2px 8px;
            border-radius: 12px;
            background: #e8f0fe;
            color: #1a73e8;
            font-weight: 500;
        }
        .footer-note {
            font-size: 0.75rem;
            color: #747775;
            line-height: 1.4;
            text-align: center;
        }
        .footer-note a {
            color: #1a73e8;
            text-decoration: none;
        }
        .loading-overlay {
            display: none;
            text-align: center;
            padding: 2rem 0;
        }
        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #e0e3e7;
            border-top: 3px solid #1a73e8;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 1rem;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>

<div class="google-card">
    <!-- Main Account Selector -->
    <div id="accountSelector">
        <div class="google-header">
            <!-- Google Multicolor Logo -->
            <svg width="48" height="48" viewBox="0 0 48 48">
                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
            </svg>
            <h1>Choose an account</h1>
            <p>to continue to <strong>Nexus ERP &amp; CRM</strong></p>
        </div>

        <div class="account-list">
            <!-- Account 1: Syed Mujtaba (Super Admin) -->
            <form action="{{ route('oauth.google.callback') }}" method="POST" id="form-mujtaba">
                @csrf
                <input type="hidden" name="email" value="syedmujtaba.dev@gmail.com">
                <div class="account-item" onclick="submitGoogleAuth('form-mujtaba', 'Syed Mujtaba')">
                    <div class="avatar" style="background: #1a73e8;">S</div>
                    <div class="account-info">
                        <div class="account-name">Syed Mujtaba</div>
                        <div class="account-email">syedmujtaba.dev@gmail.com</div>
                    </div>
                    <span class="account-badge">Super Admin</span>
                </div>
            </form>

            <!-- Account 2: Admin Direct -->
            <form action="{{ route('oauth.google.callback') }}" method="POST" id="form-admin">
                @csrf
                <input type="hidden" name="email" value="admin@nexus-erp.com">
                <div class="account-item" onclick="submitGoogleAuth('form-admin', 'Nexus Enterprise Admin')">
                    <div class="avatar" style="background: #0d652d;">A</div>
                    <div class="account-info">
                        <div class="account-name">Nexus System Admin</div>
                        <div class="account-email">admin@nexus-erp.com</div>
                    </div>
                    <span class="account-badge">Corporate</span>
                </div>
            </form>

            <!-- Account 3: Hammad Sheikh (Sub-User Staff) -->
            <form action="{{ route('oauth.google.callback') }}" method="POST" id="form-staff">
                @csrf
                <input type="hidden" name="email" value="hammad@gmail.com">
                <div class="account-item" onclick="submitGoogleAuth('form-staff', 'Hammad Sheikh')">
                    <div class="avatar" style="background: #b31412;">H</div>
                    <div class="account-info">
                        <div class="account-name">Hammad Sheikh</div>
                        <div class="account-email">hammad@gmail.com</div>
                    </div>
                    <span class="account-badge">Staff Rep</span>
                </div>
            </form>
        </div>

        <div class="footer-note">
            To continue, Google will share your name, email address, and profile picture with Nexus ERP. 
            See Nexus <a href="#">Privacy Policy</a> and <a href="#">Terms of Service</a>.
        </div>
    </div>

    <!-- Realistic Google Loading State -->
    <div class="loading-overlay" id="loadingState">
        <div class="spinner"></div>
        <h2 style="font-size: 1.15rem; font-weight: 500; color: #1f1f1f; margin-bottom: 0.5rem;" id="loadingName">Signing in with Google...</h2>
        <p style="font-size: 0.85rem; color: #5f6368;">Securing Google OAuth session and redirecting to Nexus ERP...</p>
    </div>
</div>

<script>
function submitGoogleAuth(formId, name) {
    document.getElementById('accountSelector').style.display = 'none';
    document.getElementById('loadingState').style.display = 'block';
    document.getElementById('loadingName').innerText = 'Signing in as ' + name + '...';
    setTimeout(function() {
        document.getElementById(formId).submit();
    }, 600);
}
</script>

</body>
</html>
