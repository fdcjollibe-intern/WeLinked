<div class="login-container">
    <div class="login-book">
        <div class="login-inner">
            <div class="left-col">
                <div class="login-page">
                    <div class="form-flipper">
                        <div class="login-content">
                            <div class="form-header" style="--brand-gap:16px; justify-content:center;">
                                <picture>
                                    <source srcset="/assets/logo.avif" type="image/avif">
                                    <img src="/assets/logo.png" alt="eLinked logo" />
                                </picture>
                                <div class="brand-name header-name">eLinked</div>
                            </div>

                            <h1>Forgot Password</h1>
                            <p class="subtitle">Don’t worry, happens to all of us. Enter your email below to recover your password</p>

                            <div id="fp-alert" class="alert" style="display:none"></div>

                            <form id="forgotForm" class="login-form">
                                <div class="form-group">
                                    <label for="fp-email">Email</label>
                                    <input type="email" id="fp-email" name="email" placeholder="Enter your email here" required />
                                </div>

                                <button type="submit" class="btn-submit" id="fp-submit">Send verification code</button>
                            </form>

                            <p class="register-link">
                                Remembered your password? <a href="/login">Sign in</a>
                            </p>
                            <p class="register-link">
                                Don't have an account? <a href="/register">Sign up</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Minimal styles to match login/register look */
.login-container { position: relative; z-index: 2; display:flex; justify-content:flex-end; align-items:center; min-height:100vh; padding:40px 6%; }
.login-book { width:100%; max-width:760px; background:white; border-radius:20px; padding:36px 40px; box-shadow:0 12px 36px rgba(10,20,40,0.10); }
.left-col { width:100%; max-width:540px; margin:0 auto; display:flex; flex-direction:column; padding:6px 8px; }
.form-header { display:flex; align-items:center; gap:16px; margin-bottom:12px; justify-content:center; }
.form-header img { max-height:64px; }
.brand-name { font-weight:900; font-size:23px; color:#111827; }
h1 { color:#1a202c; font-size:26px; margin-bottom:8px; text-align:center; font-weight:600; }
.subtitle { color:#718096; text-align:center; margin-bottom:18px; font-size:14px; }
.alert { padding:12px 16px; border-radius:6px; margin-bottom:16px; font-size:14px; display:block; }
.login-form { margin-bottom:18px; }
.form-group { margin-bottom:16px; }
label { display:block; margin-bottom:8px; color:#2d3748; font-weight:500; font-size:14px; }
input[type="email"] { width:100%; padding:12px 16px; border:1px solid #e2e8f0; border-radius:6px; font-size:14px; background:#fff; }
input[type="email"]:focus { outline:none; border-color:#4299e1; box-shadow:0 0 0 3px rgba(66,153,225,0.06); }
.btn-submit { width:100%; padding:12px; background:#4299e1; color:white; border:none; border-radius:6px; font-size:16px; font-weight:600; cursor:pointer; }
.btn-submit:hover { background:#3182ce; }
.register-link { text-align:center; font-size:14px; color:#718096; }
.register-link a { color:#4299e1; text-decoration:none; font-weight:600; }
.register-link a:hover { text-decoration:underline; }
@media (max-width:480px){ .login-book{ padding:28px 20px; border-radius:0; } }
</style>

<script>
document.getElementById('forgotForm').addEventListener('submit', function(e){
    e.preventDefault();
    const email = document.getElementById('fp-email').value.trim();
    const alertEl = document.getElementById('fp-alert');
    const btn = document.getElementById('fp-submit');
    if (!email) {
        alertEl.style.display = 'block';
        alertEl.className = 'alert error';
        alertEl.textContent = 'Please enter your email';
        return;
    }
    btn.disabled = true;
    btn.textContent = 'Sending...';
    alertEl.style.display = 'none';

    // Simulate async send then redirect to verify page
    setTimeout(function(){
        window.location.href = '/forgot-password/verify';
    }, 900);
});
</script>
<div class="login-content">
    <div class="form-header">
        <picture>
            <source srcset="/assets/logo.avif" type="image/avif">
            <img src="/assets/logo.png" alt="eLinked logo" />
        </picture>
        <div class="brand-name header-name">eLinked</div>
    </div>

    <h1 style="text-align:center;margin-bottom:12px;">Forgot Password</h1>
    <p class="subtitle">Enter your email and we'll send a verification code</p>

    <form id="forgotForm" class="login-form" onsubmit="return false;">
        <div class="form-group">
            <label for="fp-email">Email</label>
            <input id="fp-email" type="email" placeholder="you@example.com" required />
        </div>

        <button id="fp-send" class="btn-submit">Send verification code</button>
    </form>

    <p style="text-align:center;margin-top:16px;">Back to <a href="/login">Sign in</a></p>
</div>

<script>
document.getElementById('fp-send').addEventListener('click', function () {
    // simulate sending and navigate to verify
    this.textContent = 'Sending...';
    setTimeout(() => {
        window.location.href = '/forgot-password/verify';
    }, 700);
});
</script>
