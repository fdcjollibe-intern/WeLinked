<div class="login-content">
    <div class="form-header">
        <picture>
            <source srcset="/assets/logo.avif" type="image/avif">
            <img src="/assets/logo.png" alt="eLinked logo" />
        </picture>
        <div class="brand-name header-name">eLinked</div>
    </div>

    <h1 style="text-align:center;margin-bottom:8px;">Verify Code</h1>
    <p class="subtitle">Enter the 6-digit code sent to your email</p>

    <form method="post" action="<?= $this->Url->build(['controller' => 'Passwords', 'action' => 'verify']) ?>">
        <div id="otp" style="display:flex;gap:10px;justify-content:center;margin:18px 0 8px 0;">
            <input class="otp-box" name="d0" maxlength="1" inputmode="numeric" pattern="[0-9]*" />
            <input class="otp-box" name="d1" maxlength="1" inputmode="numeric" pattern="[0-9]*" />
            <input class="otp-box" name="d2" maxlength="1" inputmode="numeric" pattern="[0-9]*" />
            <input class="otp-box" name="d3" maxlength="1" inputmode="numeric" pattern="[0-9]*" />
            <input class="otp-box" name="d4" maxlength="1" inputmode="numeric" pattern="[0-9]*" />
            <input class="otp-box" name="d5" maxlength="1" inputmode="numeric" pattern="[0-9]*" />
        </div>

        <div style="text-align:center;margin-top:8px;">
            <button type="submit" class="btn-submit">Proceed</button>
        </div>

        <p style="text-align:center;margin-top:12px;">Didn't receive a code? <a href="<?= $this->Url->build(['controller' => 'Passwords', 'action' => 'forgot']) ?>">Resend</a></p>
    </form>
</div>

<style>
.otp-box {
    width: 44px;
    height: 52px;
    text-align: center;
    font-size: 20px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}
</style>

<script>
// simple OTP UX: focus next (non-blocking enhancement for usability)
document.querySelectorAll('.otp-box').forEach((b, idx, list) => {
    b.addEventListener('input', (e) => {
        if (e.target.value.length >= 1 && idx < list.length - 1) list[idx + 1].focus();
    });
    b.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !e.target.value && idx > 0) list[idx - 1].focus();
    });
});
</script>
</script>
