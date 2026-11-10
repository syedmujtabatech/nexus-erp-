@echo off
title Nexus ERP CRM - Live Cloudflare Launcher
color 0A
echo Starting Local Server on Port 8001...
start "" /b php artisan serve --port=8001
echo Starting Cloudflare Tunnel for Nexus ERP & CRM...
"C:\Users\Mujtaba Arshad\.gemini\antigravity\cloudflared.exe" tunnel --url http://127.0.0.1:8001
pause
