<!-- Enhanced Soft animated SVG background with macOS-like movements -->
<svg class="bg-svg" viewBox="0 0 1440 900" preserveAspectRatio="none" aria-hidden="true">
    <defs>
        <filter id="softBlur">
            <feGaussianBlur stdDeviation="80" result="blur" />
        </filter>

        <radialGradient id="reg-g1" cx="30%" cy="20%" r="60%">
            <stop offset="0%" stop-color="#bfe9ff" stop-opacity="1" />
            <stop offset="60%" stop-color="#7fc2ff" stop-opacity="0.95" />
            <stop offset="100%" stop-color="#4da9ffa3" stop-opacity="0.9" />
        </radialGradient>

        <radialGradient id="reg-g2" cx="20%" cy="30%" r="60%">
            <stop offset="0%" stop-color="#ffe8ff" stop-opacity="1" />
            <stop offset="60%" stop-color="#d7a8ff" stop-opacity="0.95" />
            <stop offset="100%" stop-color="#b570ffc0" stop-opacity="0.9" />
        </radialGradient>

        <radialGradient id="reg-g3" cx="60%" cy="70%" r="70%">
            <stop offset="0%" stop-color="#fff2ff2e" stop-opacity="1" />
            <stop offset="60%" stop-color="#efd1ff8a" stop-opacity="0.98" />
            <stop offset="100%" stop-color="#d9c3ff7e" stop-opacity="0.92" />
        </radialGradient>
    </defs>

    <g filter="url(#softBlur)" style="mix-blend-mode:screen;">
        <circle class="reg-blob reg-blob-1" cx="1100" cy="180" r="380" fill="url(#reg-g1)" />
        <circle class="reg-blob reg-blob-2" cx="300" cy="260" r="360" fill="url(#reg-g2)" />
        <circle class="reg-blob reg-blob-3" cx="820" cy="620" r="420" fill="url(#reg-g3)" />
    </g>
</svg>

<div id="registerApp">
    <div class="login-container">
        <div class="login-book <?= isset($hideImage) && $hideImage ? 'mobile-view' : '' ?>">
            <!-- Left Column removed: only right-side form remains -->
            <div class="login-page">
                <div class="login-content">
                    <!-- Debug indicator -->
                    <div style="background: #10b981; color: white; padding: 8px; text-align: center; font-size: 12px; border-radius: 6px; margin-bottom: 16px;">
                        ✓ Enhanced Register Template Loaded (v2.0)
                    </div>
                    
                    <!-- Header -->
                    <div class="form-header">
                        <h1>Create Account</h1>
                        <p class="subtitle">Join WeLinked to connect with others</p>
                    </div>

                    <!-- Alert Message -->
                    <div v-if="alert.show" :class="['alert', alert.type]">
                        {{ alert.message }}
                    </div>

                    <form class="register-form" @submit.prevent="handleRegister">
                        <div class="form-group field-with-icon">
                            <label for="username">Username</label>
                            <div class="input-wrap">
                                <input
                                    type="text"
                                    id="username"
                                    v-model="credentials.username"
                                    placeholder="Choose a username"
                                    required
                                    :disabled="loading"
                                    :class="{ 'invalid': usernameStatus==='taken' || usernameStatus==='error', 'valid': usernameStatus==='available' }"
                                />
                                <span class="status-icon" v-if="usernameStatus==='checking'">●</span>
                                <span class="status-icon success" v-if="usernameStatus==='available'">✓</span>
                                <span class="status-icon error" v-if="usernameStatus==='taken'">✕</span>
                                <span class="status-icon error" v-if="usernameStatus==='error'">!</span>
                            </div>
                            <div class="short-msg" v-if="usernameStatus==='checking'">Checking...</div>
                            <div class="short-msg success" v-if="usernameStatus==='available'">Available</div>
                            <div class="short-msg error" v-if="usernameStatus==='taken'">Username already taken</div>
                            <div class="short-msg error" v-if="usernameStatus==='error'">Can't check username right now</div>
                        </div>

                        <div class="form-group">
                            <label for="full_name">Full name</label>
                            <input
                                type="text"
                                id="full_name"
                                v-model="credentials.full_name"
                                placeholder="Your full name"
                                required
                                :disabled="loading"
                            />
                        </div>

                        <div class="form-group field-with-icon">
                            <label for="email">Email</label>
                            <div class="input-wrap">
                                <input
                                    type="email"
                                    id="email"
                                    v-model="credentials.email"
                                    placeholder="Enter your email"
                                    required
                                    :disabled="loading"
                                    :class="{ 'invalid': emailStatus==='invalid' || emailStatus==='taken' || emailStatus==='error', 'valid': emailStatus==='available' }"
                                />
                                <span class="status-icon" v-if="emailStatus==='checking'">●</span>
                                <span class="status-icon success" v-if="emailStatus==='available'">✓</span>
                                <span class="status-icon error" v-if="emailStatus==='taken' || emailStatus==='invalid'">✕</span>
                                <span class="status-icon error" v-if="emailStatus==='error'">!</span>
                            </div>
                            <div class="short-msg" v-if="emailStatus==='checking'">Checking...</div>
                            <div class="short-msg success" v-if="emailStatus==='available'">Valid Email</div>
                            <div class="short-msg error" v-if="emailStatus==='invalid'">Invalid email</div>
                            <div class="short-msg error" v-if="emailStatus==='taken'">Email already in use</div>
                            <div class="short-msg error" v-if="emailStatus==='error'">Can't check email right now</div>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input
                                type="password"
                                id="password"
                                v-model="credentials.password"
                                placeholder="Choose a password (min 6 characters)"
                                required
                                :disabled="loading"
                                @input="updatePasswordStrength"
                            />
                            <div class="pw-strength">
                                <div class="bar" :class="{'s0': passwordScore===0, 's1': passwordScore===1, 's2': passwordScore===2, 's3': passwordScore===3, 's4': passwordScore===4 }">
                                    <span class="seg" v-for="n in 4" :key="n"></span>
                                </div>
                                <div class="pw-label">{{ passwordLabel }}</div>
                            </div>
                        </div>

                        <div class="form-group field-with-icon">
                            <label for="confirmPassword">Confirm Password</label>
                            <div class="input-wrap">
                                <input
                                    type="password"
                                    id="confirmPassword"
                                    v-model="credentials.confirmPassword"
                                    placeholder="Confirm your password"
                                    required
                                    :disabled="loading"
                                    :class="{ 'invalid': confirmInvalid }
                                    "
                                />
                                <span class="status-icon error" v-if="confirmInvalid">✕</span>
                                <span class="status-icon success" v-else-if="credentials.confirmPassword && !confirmInvalid">✓</span>
                            </div>
                            <div class="short-msg error" v-if="confirmInvalid">Passwords do not match</div>
                        </div>

                        <button
                            type="submit"
                            class="btn-submit"
                            :disabled="loading"
                        >
                            <span v-if="!loading" class="btn-content">Sign Up</span>
                            <span v-else class="btn-content">
                                <span class="spinner"></span>
                                Creating account...
                            </span>
                        </button>
                    </form>

                    <p class="register-link">
                        Already have an account? <a href="/login">Sign in</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes flipIn {
    from {
        transform: rotateY(90deg);
        opacity: 0;
    }
    to {
        transform: rotateY(0deg);
        opacity: 1;
    }
}

.form-flipper {
    perspective: 1000px;
    width: 100%;
}

.flip-in {
    animation: flipIn 0.6s ease-in-out;
    transform-style: preserve-3d;
}

/* Page background setup */
html, body {
    height: 100%;
    background-color: #dfe8ff; /* soft fallback */
    background-image: none; /* replaced by SVG background */
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
}

/* SVG background styling */
.bg-svg {
    position: fixed;
    inset: 0;
    width: 100vw;
    height: 100vh;
    z-index: 0;
    pointer-events: none;
    opacity: 1;
}

.bg-svg .reg-blob {
    transform-box: fill-box;
    transform-origin: center;
}

/* Enhanced macOS-like smooth animations */
@keyframes regMacosFloat1 {
    0% { 
        transform: translate3d(0, 0, 0) scale(1) rotate(0deg);
        opacity: 0.9;
    }
    25% { 
        transform: translate3d(-25px, 15px, 0) scale(1.08) rotate(2deg);
        opacity: 0.95;
    }
    50% { 
        transform: translate3d(-12px, -20px, 0) scale(0.95) rotate(-1deg);
        opacity: 0.85;
    }
    75% { 
        transform: translate3d(28px, 8px, 0) scale(1.05) rotate(1.5deg);
        opacity: 0.92;
    }
    100% { 
        transform: translate3d(0, 0, 0) scale(1) rotate(0deg);
        opacity: 0.9;
    }
}

@keyframes regMacosFloat2 {
    0% { 
        transform: translate3d(0, 0, 0) scale(1) rotate(0deg);
        opacity: 0.88;
    }
    20% { 
        transform: translate3d(22px, -18px, 0) scale(0.92) rotate(-2deg);
        opacity: 0.93;
    }
    45% { 
        transform: translate3d(-8px, 25px, 0) scale(1.1) rotate(2.5deg);
        opacity: 0.85;
    }
    70% { 
        transform: translate3d(-30px, -10px, 0) scale(0.98) rotate(-1deg);
        opacity: 0.9;
    }
    100% { 
        transform: translate3d(0, 0, 0) scale(1) rotate(0deg);
        opacity: 0.88;
    }
}

@keyframes regMacosFloat3 {
    0% { 
        transform: translate3d(0, 0, 0) scale(1) rotate(0deg);
        opacity: 0.87;
    }
    30% { 
        transform: translate3d(-15px, -22px, 0) scale(1.06) rotate(1.8deg);
        opacity: 0.91;
    }
    60% { 
        transform: translate3d(35px, 12px, 0) scale(0.93) rotate(-2.2deg);
        opacity: 0.84;
    }
    85% { 
        transform: translate3d(8px, -18px, 0) scale(1.03) rotate(0.8deg);
        opacity: 0.89;
    }
    100% { 
        transform: translate3d(0, 0, 0) scale(1) rotate(0deg);
        opacity: 0.87;
    }
}

/* Smooth morphing effect for gradients */
@keyframes regGradientShift1 {
    0%, 100% { 
        cx: 30%; cy: 20%; r: 60%;
    }
    50% { 
        cx: 35%; cy: 25%; r: 65%;
    }
}

@keyframes regGradientShift2 {
    0%, 100% { 
        cx: 20%; cy: 30%; r: 60%;
    }
    50% { 
        cx: 18%; cy: 35%; r: 58%;
    }
}

@keyframes regGradientShift3 {
    0%, 100% { 
        cx: 60%; cy: 70%; r: 70%;
    }
    50% { 
        cx: 65%; cy: 68%; r: 72%;
    }
}

.reg-blob-1 { 
    animation: regMacosFloat1 16s cubic-bezier(0.25, 0.46, 0.45, 0.94) infinite;
    will-change: transform, opacity;
}
.reg-blob-2 { 
    animation: regMacosFloat2 18s cubic-bezier(0.25, 0.46, 0.45, 0.94) infinite 2s;
    will-change: transform, opacity;
}
.reg-blob-3 { 
    animation: regMacosFloat3 20s cubic-bezier(0.25, 0.46, 0.45, 0.94) infinite 4s;
    will-change: transform, opacity;
}

/* Apply gradient morphing to radial gradients */
#reg-g1 { animation: regGradientShift1 12s ease-in-out infinite; }
#reg-g2 { animation: regGradientShift2 15s ease-in-out infinite 1.5s; }
#reg-g3 { animation: regGradientShift3 18s ease-in-out infinite 3s; }

.login-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 20px;
    background: transparent;
    position: relative;
    z-index: 2;
}

.login-book {
    position: relative;
    z-index: 3;
}

.login-book {
    display: flex;
    justify-content: flex-end;
    width: 100%;
    max-width: 900px;
    min-height: 420px;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
}

.login-book.mobile-view {
    max-width: 500px;
    min-height: auto;
}

.login-book.mobile-view .login-page {
    flex: none;
    width: 100%;
}

.login-page {
    flex: none;
    width: 380px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 32px;
    background: #ffffff;
}

.login-content {
    width: 100%;
    max-width: 360px;
    padding: 8px 0;
}

.login-image {
    flex: 1;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    background-image: 
        linear-gradient(135deg, rgba(102, 126, 234, 0.9) 0%, rgba(118, 75, 162, 0.9) 100%),
        url('https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=800&q=80');
    background-size: cover;
    background-position: center;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.image-overlay {
    text-align: center;
    color: white;
    padding: 40px;
    z-index: 1;
}

.image-overlay h2 {
    font-size: 36px;
    font-weight: 700;
    margin-bottom: 16px;
    color: white;
}

.image-overlay p {
    font-size: 18px;
    opacity: 0.95;
    line-height: 1.6;
    color: white;
}

h1 {
    color: #0f172a;
    font-size: 32px;
    margin-bottom: 8px;
    text-align: center;
    font-weight: 700;
    letter-spacing: -0.02em;
}

.form-header {
    margin-bottom: 32px;
    text-align: center;
}

.subtitle {
    color: #64748b;
    text-align: center;
    margin-bottom: 0;
    font-size: 15px;
    line-height: 1.5;
}

.alert {
    padding: 14px 18px;
    border-radius: 8px;
    margin-bottom: 24px;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 10px;
    animation: slideDown 0.4s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-16px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.alert.success {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    color: #166534;
    border: 2px solid #86efac;
}

.alert.success::before {
    content: '✓';
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    background: #22c55e;
    color: white;
    border-radius: 50%;
    font-weight: bold;
    flex-shrink: 0;
}

.alert.error {
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    color: #991b1b;
    border: 2px solid #fca5a5;
}

.alert.error::before {
    content: '!';
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    background: #ef4444;
    color: white;
    border-radius: 50%;
    font-weight: bold;
    flex-shrink: 0;
}

.register-form {
    margin-bottom: 24px;
}

.form-group {
    margin-bottom: 24px;
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

label {
    display: block;
    margin-bottom: 8px;
    color: #1e293b;
    font-weight: 600;
    font-size: 14px;
    letter-spacing: 0.01em;
}

input[type="text"],
input[type="email"],
input[type="password"] {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: #ffffff;
    color: #2d3748;
}

.field-with-icon .input-wrap {
    position: relative;
    padding-right: 44px;
}

.status-icon {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 9999px;
    font-size: 13px;
    font-weight: 600;
    background: transparent;
    color: #9ca3af;
    transition: all 0.3s ease;
}

.status-icon.success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
    animation: successPop 0.3s ease;
}

.status-icon.error {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
    animation: errorShake 0.4s ease;
}

.status-icon.checking {
    color: #9ca3af;
}

.status-icon.checking::after {
    content: '';
    width: 14px;
    height: 14px;
    border: 2.5px solid #3b82f6;
    border-top-color: transparent;
    border-radius: 50%;
    display: inline-block;
    animation: spin 0.8s linear infinite;
}

@keyframes successPop {
    0% { transform: translateY(-50%) scale(0.5); opacity: 0; }
    50% { transform: translateY(-50%) scale(1.1); }
    100% { transform: translateY(-50%) scale(1); opacity: 1; }
}

@keyframes errorShake {
    0%, 100% { transform: translateY(-50%) translateX(0); }
    25% { transform: translateY(-50%) translateX(-4px); }
    75% { transform: translateY(-50%) translateX(4px); }
}

.short-msg {
    font-size: 13px;
    margin-top: 8px;
    color: #6b7280;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s ease;
}

.short-msg.success {
    color: #059669;
}

.short-msg.success::before {
    content: '✓';
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 16px;
    height: 16px;
    background: #d1fae5;
    border-radius: 50%;
    font-size: 11px;
    font-weight: bold;
}

.short-msg.error {
    color: #dc2626;
}

.short-msg.error::before {
    content: '!';
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 16px;
    height: 16px;
    background: #fee2e2;
    border-radius: 50%;
    font-size: 11px;
    font-weight: bold;
}

.input-wrap input.invalid {
    border-color: #ef4444;
    border-width: 2px;
    background: #fef2f2;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    animation: errorShakeInput 0.4s ease;
}

.input-wrap input.valid {
    border-color: #10b981;
    border-width: 2px;
    background: #f0fdf4;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

@keyframes errorShakeInput {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-8px); }
    75% { transform: translateX(8px); }
}

.pw-strength {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 10px;
    padding: 10px 14px;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
}

.pw-strength .bar {
    display: flex;
    gap: 6px;
    flex: 1;
}

.pw-strength .seg {
    flex: 1;
    height: 8px;
    background: #e2e8f0;
    border-radius: 4px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.05);
}

.bar.s0 .seg {
    background: #e2e8f0;
}

.bar.s1 .seg:nth-child(-n+1) {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    box-shadow: 0 2px 6px rgba(239, 68, 68, 0.3);
}

.bar.s2 .seg:nth-child(-n+2) {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    box-shadow: 0 2px 6px rgba(245, 158, 11, 0.3);
}

.bar.s3 .seg:nth-child(-n+3) {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    box-shadow: 0 2px 6px rgba(59, 130, 246, 0.3);
}

.bar.s4 .seg {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3);
}

.pw-label {
    font-size: 13px;
    font-weight: 600;
    min-width: 80px;
    text-align: right;
    transition: color 0.3s ease;
}

.bar.s0 ~ .pw-label { color: #9ca3af; }
.bar.s1 ~ .pw-label { color: #dc2626; }
.bar.s2 ~ .pw-label { color: #d97706; }
.bar.s3 ~ .pw-label { color: #2563eb; }
.bar.s4 ~ .pw-label { color: #059669; }

.helper-text {
    font-size: 12px;
    color: #64748b;
    margin-top: 8px;
    padding: 8px 12px;
    background: #f1f5f9;
    border-radius: 6px;
    border-left: 3px solid #3b82f6;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

input[type="text"]:focus,
input[type="email"]:focus,
input[type="password"]:focus {
    outline: none;
    border-color: #3b82f6;
    border-width: 2px;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    background: #ffffff;
    transform: translateY(-1px);
}

input:disabled {
    background: #f7fafc;
    cursor: not-allowed;
    color: #a0aec0;
}

.btn-submit {
    width: 100%;
    padding: 14px 20px;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    position: relative;
    overflow: hidden;
}

.btn-submit::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn-submit:hover:not(:disabled) {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
    transform: translateY(-2px);
}

.btn-submit:hover:not(:disabled)::before {
    left: 100%;
}

.btn-submit:active:not(:disabled) {
    transform: translateY(0);
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}

.btn-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
}

.btn-content {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.spinner {
    width: 16px;
    height: 16px;
    border: 2.5px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.login-link {
    text-align: center;
    font-size: 14px;
    color: #718096;
}

.login-link a {
    color: #4299e1;
    text-decoration: none;
    font-weight: 600;
}

.register-link a:hover {
    color: #3182ce;
    text-decoration: underline;
}

/* Responsive Design */
@media (max-width: 968px) {
    .login-book {
        flex-direction: column;
    }

    .login-image {
        min-height: 300px;
    }

    .login-page {
        padding: 40px 30px;
    }

    .image-overlay h2 {
        font-size: 28px;
    }

    .image-overlay p {
        font-size: 16px;
    }
}

@media (max-width: 480px) {
    .login-container {
        padding: 0;
    }

    .login-book {
        border-radius: 0;
        min-height: 100vh;
    }

    .login-page {
        padding: 30px 20px;
    }

    .image-overlay {
        padding: 30px 20px;
    }
}
</style>

<script>
console.log('=== REGISTER SCRIPT LOADED ===');
console.log('Vue available?', typeof Vue !== 'undefined');

const { createApp } = Vue;

createApp({
    data() {
        return {
            appTitle: 'WeLinked',
            credentials: {
                full_name: '',
                username: '',
                email: '',
                password: '',
                confirmPassword: ''
            },
            loading: false,
            alert: {
                show: false,
                message: '',
                type: 'error'
            },
            usernameStatus: 'idle', // idle | checking | available | taken
            emailStatus: 'idle', // idle | checking | available | taken | invalid
            pwDebounce: null,
            userDebounce: null,
            emailDebounce: null,
            passwordScore: 0,
            csrfToken: '<?= $this->request->getAttribute('csrfToken') ?>'
        }
    },
    methods: {
        async handleRegister() {
            this.loading = true;
            this.clearAlert();

            // Client-side validation
            if (this.credentials.password !== this.credentials.confirmPassword) {
                this.showAlert('Passwords do not match', 'error');
                this.loading = false;
                return;
            }

            if (this.credentials.password.length < 6) {
                this.showAlert('Password must be at least 6 characters', 'error');
                this.loading = false;
                return;
            }

            if (this.usernameStatus === 'taken') {
                this.showAlert('Username is already taken', 'error');
                this.loading = false;
                return;
            }

            if (this.emailStatus === 'invalid' || this.emailStatus === 'taken') {
                this.showAlert('Please provide a valid, unused email', 'error');
                this.loading = false;
                return;
            }

            try {
                const response = await fetch('/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': this.csrfToken
                    },
                    body: JSON.stringify({
                        full_name: this.credentials.full_name,
                        username: this.credentials.username,
                        email: this.credentials.email,
                        password: this.credentials.password,
                        confirmPassword: this.credentials.confirmPassword
                    })
                });

                const contentType = response.headers.get('content-type') || '';
                let data = null;
                if (contentType.toLowerCase().includes('application/json')) {
                    data = await response.json();
                } else {
                    const text = await response.text();
                    try { data = JSON.parse(text); } catch (e) { data = { success: false, message: text }; }
                }

                if (data.success) {
                    this.showAlert('Registration successful! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = '/dashboard';
                    }, 1000);
                } else {
                    this.showAlert(data.message || 'Registration failed', 'error');
                }
            } catch (error) {
                this.showAlert('An error occurred. Please try again.', 'error');
                console.error('Registration error:', error);
            } finally {
                this.loading = false;
            }
        },
        showAlert(message, type) {
            this.alert = {
                show: true,
                message: message,
                type: type
            };
        },
        clearAlert() {
            this.alert.show = false;
        }
        ,
        // Compute password strength with better scoring
        updatePasswordStrength() {
            const pw = this.credentials.password || '';
            let score = 0;
            
            // Length scoring
            if (pw.length >= 6) score++;
            if (pw.length >= 10) score++;
            
            // Character variety
            if (/[a-z]/.test(pw) && /[A-Z]/.test(pw)) score++; // Mixed case
            if (/[0-9]/.test(pw)) score++; // Has numbers
            if (/[^A-Za-z0-9]/.test(pw)) score++; // Has special chars
            
            // Bonus for combination
            if (pw.length >= 12 && score >= 3) score++;
            
            this.passwordScore = Math.min(score, 4);
        },
        get passwordLabel() {
            switch (this.passwordScore) {
                case 0: return 'Too weak';
                case 1: return 'Weak';
                case 2: return 'Fair';
                case 3: return 'Good';
                case 4: return 'Strong';
                default: return '';
            }
        },
        get confirmInvalid() {
            return this.credentials.confirmPassword && this.credentials.password !== this.credentials.confirmPassword;
        },
        async validateUsername(value) {
            const v = (value || '').trim();
            console.log('validateUsername called with:', v);
            if (!v || v.length < 3) { 
                this.usernameStatus = 'idle';
                console.log('Username too short or empty, status: idle');
                return;
            }
            this.usernameStatus = 'checking';
            console.log('Username status: checking');
            try {
                const res = await fetch('/register/check-username', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': this.csrfToken
                    },
                    body: JSON.stringify({ username: v })
                });
                console.log('Username check response status:', res.status);
                if (!res.ok) { 
                    this.usernameStatus = 'error';
                    console.log('Username check failed, status: error');
                    return;
                }
                const json = await res.json();
                console.log('Username check result:', json);
                this.usernameStatus = json && json.available ? 'available' : 'taken';
                console.log('Username final status:', this.usernameStatus);
            } catch (e) {
                console.error('Username validation error:', e);
                this.usernameStatus = 'error';
            }
        },
        async validateEmail(value) {
            const v = (value || '').trim();
            const simpleEmail = /^\S+@\S+\.\S+$/;
            console.log('validateEmail called with:', v);
            if (!v) { 
                this.emailStatus = 'idle';
                console.log('Email empty, status: idle');
                return;
            }
            if (!simpleEmail.test(v)) { 
                this.emailStatus = 'invalid';
                console.log('Email invalid format, status: invalid');
                return;
            }
            this.emailStatus = 'checking';
            console.log('Email status: checking');
            try {
                const res = await fetch('/register/check-email', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': this.csrfToken
                    },
                    body: JSON.stringify({ email: v })
                });
                console.log('Email check response status:', res.status);
                if (!res.ok) { 
                    this.emailStatus = 'error';
                    console.log('Email check failed, status: error');
                    return;
                }
                const json = await res.json();
                console.log('Email check result:', json);
                this.emailStatus = json && json.available ? 'available' : 'taken';
                console.log('Email final status:', this.emailStatus);
            } catch (e) {
                console.error('Email validation error:', e);
                this.emailStatus = 'error';
            }
        }
    },
    mounted() {
        console.log('=== REGISTER APP MOUNTED SUCCESSFULLY ===');
        console.log('Vue version:', Vue.version);
        console.log('CSRF Token:', this.csrfToken);
        console.log('Username status:', this.usernameStatus);
        console.log('Email status:', this.emailStatus);
        
        this.$watch(
            () => this.credentials.username,
            (nv) => {
                console.log('Username changed:', nv);
                if (this.userDebounce) clearTimeout(this.userDebounce);
                this.userDebounce = setTimeout(() => this.validateUsername(nv), 600);
            }
        );

        this.$watch(
            () => this.credentials.email,
            (nv) => {
                console.log('Email changed:', nv);
                if (this.emailDebounce) clearTimeout(this.emailDebounce);
                this.emailDebounce = setTimeout(() => this.validateEmail(nv), 600);
            }
        );
    }
}).mount('#registerApp');
</script>
