<?php
/**
 * Amethystclothing.lk — Admin Login Screen
 * Sharp Minimalist / High-Contrast Obsidian & Royal Amethyst
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../core/helpers.php';

// If already logged in, redirect to dashboard
if (is_admin_logged_in()) {
    redirect(base_url('admin/dashboard'));
}

$standalone = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login // AMETHYST</title>
    <link rel="icon" type="image/png" href="<?= asset_url('images/icon_b.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset_url('css/main.css') ?>">
    <style>
        body.admin-login-body {
            background-color: #07060A;
            color: #FFFFFF;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            position: relative;
            overflow-x: hidden;
        }

        /* Architectural background lines */
        .admin-login-body::before {
            content: '';
            position: absolute;
            top: 0; left: 50%;
            transform: translateX(-50%);
            width: 100%;
            max-width: 1200px;
            height: 100%;
            border-left: 1px solid rgba(255, 255, 255, 0.04);
            border-right: 1px solid rgba(255, 255, 255, 0.04);
            pointer-events: none;
        }

        .login-box {
            width: 100%;
            max-width: 440px;
            background: #0E0C14;
            border: 1px solid #231E2C;
            padding: 44px 36px;
            position: relative;
            z-index: 2;
        }

        .login-box::after {
            content: '';
            position: absolute;
            top: -1px; left: -1px; right: -1px;
            height: 3px;
            background: #4e2a84;
        }

        .login-brand {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-brand img {
            max-height: 42px;
            width: auto;
            display: inline-block;
            filter: brightness(0) invert(1);
        }

        .login-badge {
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: #9966cc;
            background: rgba(78, 42, 132, 0.2);
            padding: 4px 10px;
            display: inline-block;
            margin-top: 14px;
            border: 1px solid rgba(153, 102, 204, 0.3);
        }

        .login-title {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin: 20px 0 6px;
            color: #FFFFFF;
        }

        .login-sub {
            font-size: 13px;
            color: #8C869B;
            margin-bottom: 24px;
        }

        .login-form .form-group {
            margin-bottom: 20px;
        }

        .login-form label {
            display: block;
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #B5AFC2;
            margin-bottom: 8px;
        }

        .login-input {
            width: 100%;
            background: #07060A;
            border: 1px solid #2C2638;
            color: #FFFFFF;
            padding: 14px 16px;
            font-size: 14px;
            border-radius: 0;
            outline: none;
            transition: border-color 0.15s ease;
            box-sizing: border-box;
            font-family: inherit;
        }

        .login-input:focus {
            border-color: #9966cc;
            box-shadow: 0 0 0 1px #9966cc;
        }

        .login-btn {
            width: 100%;
            background: #4e2a84;
            color: #FFFFFF;
            border: 1px solid #4e2a84;
            padding: 15px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            font-size: 13px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            cursor: pointer;
            border-radius: 0;
            transition: all 0.2s ease;
            margin-top: 8px;
        }

        .login-btn:hover {
            background: #5e33a0;
            border-color: #5e33a0;
        }

        .login-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .login-alert {
            display: none;
            background: rgba(220, 38, 38, 0.12);
            border: 1px solid rgba(220, 38, 38, 0.4);
            color: #FCA5A5;
            padding: 12px 14px;
            font-size: 13px;
            margin-bottom: 20px;
            line-height: 1.4;
        }

        .login-hint {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #1C1826;
            font-family: 'Space Mono', monospace;
            font-size: 11px;
            color: #716B80;
            text-align: center;
        }

        .login-hint code {
            color: #9966cc;
            background: rgba(255, 255, 255, 0.05);
            padding: 2px 5px;
        }

        .back-to-store {
            display: block;
            text-align: center;
            margin-top: 18px;
            font-size: 12px;
            color: #8C869B;
            text-decoration: none;
            letter-spacing: 0.05em;
            transition: color 0.15s;
        }
        .back-to-store:hover {
            color: #FFFFFF;
        }
    </style>
</head>
<body class="admin-login-body">
    <div class="login-box">
        <div class="login-brand">
            <?php if (file_exists(__DIR__ . '/../../../assets/images/logo_w-color.png')): ?>
                <img src="<?= asset_url('images/logo_w-color.png') ?>" alt="Amethyst">
            <?php else: ?>
                <h1 style="font-size:24px;letter-spacing:0.2em;margin:0;">AMETHYST</h1>
            <?php endif; ?>
            <div>
                <span class="login-badge">SYS // ADMIN CONSOLE</span>
            </div>
            <div class="login-title">Management Gateway</div>
            <div class="login-sub">Sign in with system administrator credentials.</div>
        </div>

        <div id="login-alert" class="login-alert"></div>

        <form id="admin-login-form" class="login-form">
            <div class="form-group">
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username" class="login-input" placeholder="admin" value="admin" required autofocus autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="login-input" placeholder="••••••••" value="Admin@123" required autocomplete="current-password">
            </div>

            <button type="submit" id="login-submit-btn" class="login-btn">
                <span>ENTER ADMIN CONSOLE &rarr;</span>
            </button>
        </form>

        <div class="login-hint">
            Default credentials: <code>admin</code> / <code>Admin@123</code>
        </div>

        <a href="<?= base_url() ?>" class="back-to-store">&larr; Return to Storefront</a>
    </div>

    <script>
        document.getElementById('admin-login-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('login-submit-btn');
            const alertBox = document.getElementById('login-alert');
            
            alertBox.style.display = 'none';
            alertBox.textContent = '';
            
            const formData = new FormData(this);
            btn.disabled = true;
            btn.innerHTML = '<span>AUTHENTICATING...</span>';

            fetch('<?= base_url('ajax/admin_auth.php') ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    btn.style.background = '#10B981';
                    btn.style.borderColor = '#10B981';
                    btn.innerHTML = '<span>ACCESS GRANTED &check;</span>';
                    setTimeout(() => {
                        window.location.href = data.redirect || '<?= base_url('admin/dashboard') ?>';
                    }, 400);
                } else {
                    alertBox.textContent = data.message || 'Authentication failed. Please verify credentials.';
                    alertBox.style.display = 'block';
                    btn.disabled = false;
                    btn.innerHTML = '<span>ENTER ADMIN CONSOLE &rarr;</span>';
                }
            })
            .catch(err => {
                alertBox.textContent = 'Connection error. Please try again.';
                alertBox.style.display = 'block';
                btn.disabled = false;
                btn.innerHTML = '<span>ENTER ADMIN CONSOLE &rarr;</span>';
            });
        });
    </script>
</body>
</html>
