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

    <form id="resetForm" class="login-form" method="post" action="<?= $this->Url->build(['controller' => 'Passwords', 'action' => 'reset']) ?>">
        <div class="form-group">
            <label for="new-pass">New password</label>
            <input id="new-pass" name="password" type="password" placeholder="New password" required />
        </div>
        <div class="form-group">
            <label for="confirm-pass">Confirm password</label>
            <input id="confirm-pass" name="confirm_password" type="password" placeholder="Confirm password" required />
        </div>

        <button type="submit" id="reset-submit" class="btn-submit">Set password</button>
    </form>

    <p style="text-align:center;margin-top:12px;">Remembered? <a href="<?= $this->Url->build(['controller' => 'Login', 'action' => 'index']) ?>">Sign in</a></p>
</div>

<script>
document.getElementById('resetForm').addEventListener('submit', function (e) {
    const p1 = document.getElementById('new-pass').value;
    const p2 = document.getElementById('confirm-pass').value;
    if (!p1 || !p2) {
        e.preventDefault();
        alert('Please fill both fields');
        return;
    }
    if (p1 !== p2) {
        e.preventDefault();
        alert('Passwords do not match');
        return;
    }
    // show visual feedback; allow normal form submit to server
    document.getElementById('reset-submit').textContent = 'Setting...';
});
</script>
