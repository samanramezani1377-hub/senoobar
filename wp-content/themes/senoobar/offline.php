<?php
/**
 * Template: Offline Page
 * Shown when user is offline (PWA)
 */
?><!DOCTYPE html>
<html dir="rtl" lang="fa-IR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>آفلاین - صنوبر</title>
    <style>
        @font-face {
            font-family: 'IRANSansWeb';
            src: url('/wp-content/themes/senoobar/assets/fonts/IRANSansWeb.woff2') format('woff2');
            font-display: swap;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'IRANSansWeb', system-ui, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: #f8f5f2;
            direction: rtl;
            text-align: center;
            padding: 20px;
        }
        .offline-card {
            background: white;
            border-radius: 16px;
            padding: 48px 32px;
            max-width: 400px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }
        .offline-icon { font-size: 64px; margin-bottom: 24px; }
        h1 { font-size: 1.5rem; color: #2d3436; margin-bottom: 12px; }
        p { color: #6b6560; line-height: 1.8; margin-bottom: 24px; }
        .retry-btn {
            display: inline-block;
            background: #d4a574;
            color: white;
            padding: 12px 32px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }
        .retry-btn:hover { background: #c49665; }
    </style>
</head>
<body>
    <div class="offline-card">
        <div class="offline-icon">📡</div>
        <h1>شما آفلاین هستید</h1>
        <p>به نظر می‌رسد ارتباط اینترنت قطع شده است. لطفاً اتصال خود را بررسی کنید و دوباره تلاش کنید.</p>
        <a href="/" class="retry-btn" onclick="window.location.reload()">تلاش مجدد</a>
    </div>
</body>
</html>
