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

    <div id="otp" style="display:flex;gap:10px;justify-content:center;margin:18px 0 8px 0;">
        <input class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" />
        <input class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" />
        <input class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" />
        <input class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" />
        <input class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" />
        <input class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" />
    </div>

    <div style="text-align:center;margin-top:8px;">
        <button id="otp-verify" class="btn-submit">Proceed</button>
    </div>

    <p style="text-align:center;margin-top:12px;">Didn't receive a code? <a href="/forgot-password">Resend</a></p>
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
// simple OTP UX: focus next
const boxes = document.querySelectorAll('.otp-box');
boxes.forEach((b, idx) => {
    b.addEventListener('input', (e) => {
        const v = e.target.value;
        if (v.length >= 1 && idx < boxes.length - 1) boxes[idx + 1].focus();
    });
    b.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && !e.target.value && idx > 0) {
            boxes[idx - 1].focus();
        }
    });
});

document.getElementById('otp-verify').addEventListener('click', () => {
    // In a real app we'd verify; here just proceed to reset
    document.getElementById('otp-verify').textContent = 'Verifying...';
    setTimeout(() => window.location.href = '/forgot-password/reset', 600);
});
</script>
