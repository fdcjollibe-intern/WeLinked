<div class="login-content">
    <div class="form-header">
        <picture>
            <source srcset="/assets/logo.avif" type="image/avif">
            <img src="/assets/logo.png" alt="eLinked logo" />
        </picture>
        <div class="brand-name header-name">eLinked</div>
    </div>

    <h1 style="text-align:center;margin-bottom:8px;">Create New Password</h1>
    <p class="subtitle">Choose a secure password for your account</p>

    <form id="resetForm" class="login-form" onsubmit="return false;">
        <div class="form-group">
            <label for="new-pass">New password</label>
            <input id="new-pass" type="password" placeholder="New password" required />
        </div>
        <div class="form-group">
            <label for="confirm-pass">Confirm password</label>
            <input id="confirm-pass" type="password" placeholder="Confirm password" required />
        </div>

        <button id="reset-submit" class="btn-submit">Set password</button>
    </form>

    <p style="text-align:center;margin-top:12px;">Remembered? <a href="/login">Sign in</a></p>
</div>

<script>
document.getElementById('reset-submit').addEventListener('click', function () {
    const p1 = document.getElementById('new-pass').value;
    const p2 = document.getElementById('confirm-pass').value;
    if (!p1 || !p2) {
        alert('Please fill both fields');
        return;
    }
    if (p1 !== p2) {
        alert('Passwords do not match');
        return;
    }
    this.textContent = 'Setting...';
    setTimeout(() => {
        // simulate completion and redirect to login
        window.location.href = '/login';
    }, 800);
});
</script>
