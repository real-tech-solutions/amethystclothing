<?php
/**
 * Amethystclothing.lk — Unified Customer Auth (Login & Register)
 * Sharp Minimalist Luxury UI — Strictly No AI Slop
 */

if (is_customer_logged_in()) {
    redirect(base_url('account/dashboard'));
}

$pageTitle = 'Account Access // AMETHYST';
$redirect = trim($_GET['redirect'] ?? '');
$activeTab = trim($_GET['tab'] ?? 'login'); // 'login' or 'register'
?>

<section class="section auth-section" style="padding: 70px 0 100px;">
    <div class="container" style="max-width: 520px;">
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 32px;">
            <span style="font-family: var(--font-mono); font-size: 11px; letter-spacing: 0.15em; color: var(--brand-deep); text-transform: uppercase;">
                MEMBER ARCHIVE // SECURE ACCESS
            </span>
            <h1 style="font-size: 26px; font-weight: 800; text-transform: uppercase; margin: 8px 0 4px; letter-spacing: -0.01em;">
                CUSTOMER PORTAL
            </h1>
            <p style="font-size: 13px; color: var(--text-secondary); margin: 0;">
                Sign in to view orders, track dispatches, and manage delivery addresses.
            </p>
        </div>

        <!-- Auth Card -->
        <div style="background: #FFFFFF; border: 1px solid var(--border-crisp); padding: 36px 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <!-- Tabs -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; border-bottom: 2px solid #F0EEF5; margin-bottom: 28px;">
                <button type="button" id="tab-btn-login" class="auth-tab-btn <?= $activeTab !== 'register' ? 'is-active' : '' ?>" 
                        style="background:none; border:none; padding:12px; font-family:var(--font-mono); font-size:12px; font-weight:700; letter-spacing:0.1em; cursor:pointer; color: <?= $activeTab !== 'register' ? 'var(--brand-deep)' : '#8C869B' ?>; border-bottom: 2px solid <?= $activeTab !== 'register' ? 'var(--brand-deep)' : 'transparent' ?>; margin-bottom: -2px;">
                    SIGN IN
                </button>
                <button type="button" id="tab-btn-register" class="auth-tab-btn <?= $activeTab === 'register' ? 'is-active' : '' ?>" 
                        style="background:none; border:none; padding:12px; font-family:var(--font-mono); font-size:12px; font-weight:700; letter-spacing:0.1em; cursor:pointer; color: <?= $activeTab === 'register' ? 'var(--brand-deep)' : '#8C869B' ?>; border-bottom: 2px solid <?= $activeTab === 'register' ? 'var(--brand-deep)' : 'transparent' ?>; margin-bottom: -2px;">
                    CREATE ACCOUNT
                </button>
            </div>

            <!-- Feedback Alert Box -->
            <div id="auth-alert" style="display:none; padding: 12px 14px; font-size: 13px; line-height: 1.4; margin-bottom: 20px;"></div>

            <!-- Form: Login -->
            <form id="form-customer-login" style="<?= $activeTab === 'register' ? 'display:none;' : '' ?>">
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="redirect" value="<?= e($redirect) ?>">

                <div class="form-group" style="margin-bottom: 18px;">
                    <label class="form-label" style="font-size: 11px; letter-spacing: 0.08em; display:block; margin-bottom: 6px;">
                        MOBILE NUMBER OR EMAIL <span style="color:#EF4444;">*</span>
                    </label>
                    <input type="text" name="login_id" class="input" placeholder="07XXXXXXXX or email" required autofocus
                           style="width:100%; padding:12px; border:1px solid var(--border-crisp); font-size:14px; border-radius:0; box-sizing:border-box;">
                </div>

                <div class="form-group" style="margin-bottom: 22px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                        <label class="form-label" style="font-size: 11px; letter-spacing: 0.08em; margin:0;">
                            PASSWORD <span style="color:#EF4444;">*</span>
                        </label>
                    </div>
                    <input type="password" name="password" class="input" placeholder="••••••••" required
                           style="width:100%; padding:12px; border:1px solid var(--border-crisp); font-size:14px; border-radius:0; box-sizing:border-box;">
                </div>

                <button type="submit" id="btn-login-submit" class="btn btn--brand btn--full" style="padding:14px; font-size:12px; letter-spacing:0.12em; border-radius:0;">
                    SIGN IN TO ACCOUNT &rarr;
                </button>
            </form>

            <!-- Form: Register -->
            <form id="form-customer-register" style="<?= $activeTab !== 'register' ? 'display:none;' : '' ?>">
                <input type="hidden" name="action" value="register">
                <input type="hidden" name="redirect" value="<?= e($redirect) ?>">

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-size: 11px; letter-spacing: 0.08em; display:block; margin-bottom: 6px;">
                        FULL NAME <span style="color:#EF4444;">*</span>
                    </label>
                    <input type="text" name="full_name" class="input" placeholder="e.g. Ruwan Wickramasinghe" required
                           style="width:100%; padding:12px; border:1px solid var(--border-crisp); font-size:14px; border-radius:0; box-sizing:border-box;">
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-size: 11px; letter-spacing: 0.08em; display:block; margin-bottom: 6px;">
                        MOBILE NUMBER (PRIMARY IDENTIFIER) <span style="color:#EF4444;">*</span>
                    </label>
                    <input type="tel" name="phone" class="input" placeholder="077XXXXXXX" required
                           style="width:100%; padding:12px; border:1px solid var(--border-crisp); font-size:14px; border-radius:0; box-sizing:border-box;">
                    <span style="font-size: 11px; color: #8C869B; margin-top: 4px; display:block;">Used for SMS delivery updates and order receipts.</span>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-size: 11px; letter-spacing: 0.08em; display:block; margin-bottom: 6px;">
                        EMAIL ADDRESS (OPTIONAL)
                    </label>
                    <input type="email" name="email" class="input" placeholder="ruwan@example.com"
                           style="width:100%; padding:12px; border:1px solid var(--border-crisp); font-size:14px; border-radius:0; box-sizing:border-box;">
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom: 22px;">
                    <div>
                        <label class="form-label" style="font-size: 11px; letter-spacing: 0.08em; display:block; margin-bottom: 6px;">
                            PASSWORD <span style="color:#EF4444;">*</span>
                        </label>
                        <input type="password" name="password" class="input" placeholder="••••••••" required minlength="6"
                               style="width:100%; padding:12px; border:1px solid var(--border-crisp); font-size:14px; border-radius:0; box-sizing:border-box;">
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 11px; letter-spacing: 0.08em; display:block; margin-bottom: 6px;">
                            CONFIRM <span style="color:#EF4444;">*</span>
                        </label>
                        <input type="password" name="confirm_password" class="input" placeholder="••••••••" required minlength="6"
                               style="width:100%; padding:12px; border:1px solid var(--border-crisp); font-size:14px; border-radius:0; box-sizing:border-box;">
                    </div>
                </div>

                <button type="submit" id="btn-register-submit" class="btn btn--brand btn--full" style="padding:14px; font-size:12px; letter-spacing:0.12em; border-radius:0;">
                    CREATE ACCOUNT & CLAIM 100 POINTS &rarr;
                </button>
            </form>
        </div>

        <div style="text-align: center; margin-top: 24px; font-size: 12px; color: var(--text-secondary);">
            Prefer to order without creating an account? 
            <a href="<?= base_url('shop') ?>" style="color: var(--brand-deep); font-weight: 700; text-decoration: none;">
                Fast 60-Second Guest Checkout &rarr;
            </a>
        </div>
    </div>
</section>

<script>
    // Tab switching
    const loginTabBtn = document.getElementById('tab-btn-login');
    const regTabBtn = document.getElementById('tab-btn-register');
    const loginForm = document.getElementById('form-customer-login');
    const regForm = document.getElementById('form-customer-register');
    const alertBox = document.getElementById('auth-alert');

    function switchTab(tab) {
        alertBox.style.display = 'none';
        if (tab === 'login') {
            loginTabBtn.style.color = 'var(--brand-deep)';
            loginTabBtn.style.borderBottomColor = 'var(--brand-deep)';
            regTabBtn.style.color = '#8C869B';
            regTabBtn.style.borderBottomColor = 'transparent';
            loginForm.style.display = 'block';
            regForm.style.display = 'none';
        } else {
            regTabBtn.style.color = 'var(--brand-deep)';
            regTabBtn.style.borderBottomColor = 'var(--brand-deep)';
            loginTabBtn.style.color = '#8C869B';
            loginTabBtn.style.borderBottomColor = 'transparent';
            loginForm.style.display = 'none';
            regForm.style.display = 'block';
        }
    }

    loginTabBtn.addEventListener('click', () => switchTab('login'));
    regTabBtn.addEventListener('click', () => switchTab('register'));

    // Handle AJAX Form Submissions
    function handleAuthSubmit(form, btn, defaultText) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            alertBox.style.display = 'none';
            btn.disabled = true;
            btn.textContent = 'AUTHENTICATING...';

            const formData = new FormData(this);

            fetch('<?= base_url('ajax/customer_auth.php') ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.textContent = defaultText;

                if (data.success) {
                    alertBox.style.background = '#D1FAE5';
                    alertBox.style.border = '1px solid #A7F3D0';
                    alertBox.style.color = '#065F46';
                    alertBox.textContent = data.message;
                    alertBox.style.display = 'block';
                    setTimeout(() => {
                        window.location.href = data.redirect || '<?= base_url('account/dashboard') ?>';
                    }, 500);
                } else {
                    alertBox.style.background = '#FEE2E2';
                    alertBox.style.border = '1px solid #FECACA';
                    alertBox.style.color = '#991B1B';
                    alertBox.textContent = data.message || 'Authentication error.';
                    alertBox.style.display = 'block';
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.textContent = defaultText;
                alertBox.style.background = '#FEE2E2';
                alertBox.style.border = '1px solid #FECACA';
                alertBox.style.color = '#991B1B';
                alertBox.textContent = 'Network error. Please try again.';
                alertBox.style.display = 'block';
            });
        });
    }

    handleAuthSubmit(loginForm, document.getElementById('btn-login-submit'), 'SIGN IN TO ACCOUNT →');
    handleAuthSubmit(regForm, document.getElementById('btn-register-submit'), 'CREATE ACCOUNT & CLAIM 100 POINTS →');
</script>
