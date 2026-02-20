<!-- Soft animated SVG background (inspired by webroot/assets/mainbg.avif) -->
<svg class="bg-svg" viewBox="0 0 1440 900" preserveAspectRatio="none" aria-hidden="true">
    <defs>
        <filter id="softBlur">
            <feGaussianBlur stdDeviation="80" result="blur" />
        </filter>

        <radialGradient id="g1" cx="30%" cy="20%" r="60%">
            <stop offset="0%" stop-color="#bfe9ff" stop-opacity="1" />
            <stop offset="60%" stop-color="#7fc2ff" stop-opacity="0.95" />
            <stop offset="100%" stop-color="#4da9ffa3" stop-opacity="0.9" />
        </radialGradient>

        <radialGradient id="g2" cx="20%" cy="30%" r="60%">
            <stop offset="0%" stop-color="#ffe8ff" stop-opacity="1" />
            <stop offset="60%" stop-color="#d7a8ff" stop-opacity="0.95" />
            <stop offset="100%" stop-color="#b570ffc0" stop-opacity="0.9" />
        </radialGradient>

        <radialGradient id="g3" cx="60%" cy="70%" r="70%">
            <stop offset="0%" stop-color="#fff2ff2e" stop-opacity="1" />
            <stop offset="60%" stop-color="#efd1ff8a" stop-opacity="0.98" />
            <stop offset="100%" stop-color="#d9c3ff7e" stop-opacity="0.92" />
        </radialGradient>
    </defs>

    <g filter="url(#softBlur)" style="mix-blend-mode:screen;">
        <circle class="blob blob-1" cx="1100" cy="180" r="380" fill="url(#g1)" />
        <circle class="blob blob-2" cx="300" cy="260" r="360" fill="url(#g2)" />
        <circle class="blob blob-3" cx="820" cy="620" r="420" fill="url(#g3)" />
    </g>
</svg>

<div id="loginApp">
    <div class="login-container">
        <div class="login-book <?= isset($hideImage) && $hideImage ? 'mobile-view' : '' ?>" :class="{ 'is-register': isRegister }">
            <div class="login-inner">
                <div class="left-col">

                    <!-- Form Flipper (Login / Register) -->
                    <div class="login-page">
                        <div class="form-flipper">
                    <!-- Login Form -->
                    <div v-if="!isRegister && !isForgot" class="login-content flip-in" :key="'login'">
                        <div class="form-header" :style="{ '--brand-gap': brandGap }">
                            <picture>
                                <source srcset="/assets/logo.avif" type="image/avif">
                                <img src="/assets/logo.png" alt="eLinked logo" />
                            </picture>
                            <div class="brand-name header-name" style="margin-left: -4px;">eLinked</div>
                        </div>
                        <p class="subtitle">Sign in to your account</p>

                        <!-- Alert Messages -->
                        <div v-if="alert.show" :class="['alert', alert.type]">
                            {{ alert.message }}
                        </div>

                        <!-- Login Form -->
                        <form @submit.prevent="handleLogin" class="login-form">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input 
                                    type="text"
                                    id="username"
                                    v-model="credentials.username"
                                    placeholder="Enter your username"
                                    required
                                    :disabled="loading"
                                />
                            </div>

                            <div class="form-group">
                                <label for="password">Password</label>
                                <input 
                                    type="password"
                                    id="password"
                                    v-model="credentials.password"
                                    placeholder="Enter your password"
                                    required
                                    :disabled="loading"
                                />
                            </div>

                            <button 
                                type="submit" 
                                class="btn-submit"
                                :disabled="loading"
                            >
                                <span v-if="!loading">Login</span>
                                <span v-else>Logging in...</span>
                            </button>
                        </form>

                        <p class="register-link">
                            Don't have an account? <a href="#" @click.prevent="toggleForm">Sign up</a>
                        </p>
                        <p class="forgot-link">
                        </p>
                    </div>

                    <!-- Forgot Password Form -->
                    <div v-else-if="isForgot" class="login-content flip-in" :key="'forgot'">
                        <div class="form-header" :style="{ '--brand-gap': brandGap }">
                            <picture>
                                <source srcset="/assets/logo.avif" type="image/avif">
                                <img src="/assets/logo.png" alt="eLinked logo" />
                            </picture>
                            <div class="brand-name header-name" style="margin-left: -4px;">eLinked</div>
                        </div>
                        <h1>Forgot Password</h1>
                        <p class="subtitle">Don’t worry, happens to all of us. Enter your email below to recover your password</p>

                        <div v-if="alert.show" :class="['alert', alert.type]">
                            {{ alert.message }}
                        </div>

                        <form @submit.prevent="handleForgot" class="login-form">
                            <div class="form-group">
                                <label for="fp-email">Email</label>
                                <input
                                    type="email"
                                    id="fp-email"
                                    v-model="forgotEmail"
                                    placeholder="you@example.com"
                                    required
                                    :disabled="loading"
                                />
                            </div>

                            <button type="submit" class="btn-submit" :disabled="loading">
                                <span v-if="!loading">Send verification code</span>
                                <span v-else>Sending...</span>
                            </button>
                            </form>

                            <p class="register-link">
                                Remembered your password? <a href="/login">Sign in</a>
                            </p>
                            <p class="register-link">
                                Don't have an account? <a href="/register">Sign up</a>
                            </p>
                    </div>

                    <!-- Register Form -->
                    <div v-else-if="isRegister" class="login-content flip-in" :key="'register'">
                        <div class="form-header" :style="{ '--brand-gap': brandGap }">
                            <picture>
                                <source srcset="/assets/logo.avif" type="image/avif">
                                <img src="/assets/logo.png" alt="eLinked logo" />
                            </picture>
                            <div class="brand-name header-name" style="margin-left: -4px;">eLinked</div>
                        </div>
                        <p class="subtitle">Create your account</p>

                        <!-- Alert Messages -->
                        <div v-if="alert.show" :class="['alert', alert.type]">
                            {{ alert.message }}
                        </div>

                        <!-- Register Form -->
                        <form @submit.prevent="handleRegister" class="login-form register-form-grid">
                            <!-- Row 1: Username | Full Name -->
                            <div class="form-group field-with-icon">
                                <label for="reg-username">Username</label>
                                <div class="input-wrap">
                                    <input
                                        type="text"
                                        id="reg-username"
                                        v-model="registerData.username"
                                        placeholder="Choose a username"
                                        required
                                        :disabled="loading"
                                        :class="{ 'invalid': usernameStatus==='taken' || usernameStatus==='error', 'valid': usernameStatus==='available' }"
                                        @input="sanitizeUsername"
                                        pattern="[a-zA-Z0-9_]+"
                                        title="Username can only contain letters, numbers, and underscores"
                                        maxlength="21"
                                    />
                                    <span class="status-icon checking" v-if="usernameStatus==='checking'"></span>
                                    <span class="status-icon success" v-if="usernameStatus==='available'">✓</span>
                                    <span class="status-icon error" v-if="usernameStatus==='taken'">✕</span>
                                    <span class="status-icon error" v-if="usernameStatus==='error'">!</span>
                                </div>
                                <div class="short-msg" v-if="usernameStatus==='checking'">Checking...</div>
                                <div class="short-msg success" v-if="usernameStatus==='available'">Available</div>
                                <div class="short-msg error" v-if="usernameStatus==='taken'">Username already taken</div>
                                <div class="short-msg error" v-if="usernameStatus==='error'">Can't check username right now</div>
                                <div class="short-msg" v-if="usernameStatus==='idle' && !registerData.username" style="color: #64748b;">Only letters, numbers, and underscores</div>
                            </div>

                            <div class="form-group">
                                <label for="reg-fullname">Full name</label>
                                <div class="input-wrap">
                                    <input
                                        type="text"
                                        id="reg-fullname"
                                        v-model="registerData.full_name"
                                        placeholder="Your full name"
                                        required
                                        :disabled="loading"
                                        maxlength="21"
                                    />
                                </div>
                            </div>

                            <!-- Row 2: Email | Gender -->
                            <div class="form-group field-with-icon">
                                <label for="reg-email">Email</label>
                                <div class="input-wrap">
                                    <input
                                        type="email"
                                        id="reg-email"
                                        v-model="registerData.email"
                                        placeholder="Enter your email"
                                        required
                                        :disabled="loading"
                                        :class="{ 'invalid': emailStatus==='invalid' || emailStatus==='taken' || emailStatus==='error', 'valid': emailStatus==='available' }"
                                        maxlength="32"
                                    />
                                    <span class="status-icon checking" v-if="emailStatus==='checking'"></span>
                                    <span class="status-icon success" v-if="emailStatus==='available'">✓</span>
                                    <span class="status-icon error" v-if="emailStatus==='taken' || emailStatus==='invalid'">✕</span>
                                    <span class="status-icon error" v-if="emailStatus==='error'">!</span>
                                </div>
                                <div class="short-msg" v-if="emailStatus==='checking'">Checking...</div>
                                <div class="short-msg success" v-if="emailStatus==='available'">Looks good</div>
                                <div class="short-msg error" v-if="emailStatus==='invalid'">Invalid email</div>
                                <div class="short-msg error" v-if="emailStatus==='taken'">Email already in use</div>
                                <div class="short-msg error" v-if="emailStatus==='error'">Can't check email right now</div>
                            </div>

                            <div class="form-group">
                                <label for="reg-gender">Gender</label>
                                <div class="input-wrap">
                                    <select
                                        id="reg-gender"
                                        v-model="registerData.gender"
                                        required
                                        :disabled="loading"
                                    >
                                        <option value="" disabled selected>Select gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Prefer not to say">Prefer not to say</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Row 3: Password | Confirm Password -->
                            <div class="form-group">
                                <label for="reg-password">Password</label>
                                <div class="input-wrap">
                                    <input 
                                        type="password"
                                        id="reg-password"
                                        v-model="registerData.password"
                                        placeholder="Choose a password (min 6 characters)"
                                        required
                                        :disabled="loading"
                                        @input="updatePasswordStrength"
                                    />
                                </div>
                                <div class="pw-strength" v-if="registerData.password">
                                    <div class="bar" :class="{'s0': passwordScore===0, 's1': passwordScore===1, 's2': passwordScore===2, 's3': passwordScore===3, 's4': passwordScore===4 }">
                                        <span class="seg" v-for="n in 4" :key="n"></span>
                                    </div>
                                    <div class="pw-label">{{ passwordLabel }}</div>
                                </div>
                                <div class="helper-text" v-if="registerData.password && passwordScore < 3">
                                 Use uppercase, lowercase, numbers & symbols
                                </div>
                            </div>

                            <div class="form-group field-with-icon">
                                <label for="reg-confirm">Confirm Password</label>
                                <div class="input-wrap">
                                    <input 
                                        type="password"
                                        id="reg-confirm"
                                        v-model="registerData.confirmPassword"
                                        placeholder="Confirm your password"
                                        required
                                        :disabled="loading"
                                        :class="{ 'invalid': confirmInvalid }"
                                    />
                                    <span class="status-icon error" v-if="confirmInvalid">✕</span>
                                    <span class="status-icon success" v-else-if="registerData.confirmPassword && !confirmInvalid">✓</span>
                                </div>
                                <div class="short-msg error" v-if="confirmInvalid">Passwords do not match</div>
                            </div>

                            <!-- Row 4: Terms Agreement Checkbox (Full Width) -->
                            <div class="form-group terms-checkbox">
                                <label class="checkbox-label">
                                    <input
                                        type="checkbox"
                                        v-model="registerData.agreeToTerms"
                                        required
                                        :disabled="loading"
                                    />
                                    <span class="checkbox-text">I agree to the <a href="#" @click.prevent>Terms of Service</a> and <a href="#" @click.prevent>Privacy Policy</a></span>
                                </label>
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
                            Already have an account? <a href="#" @click.prevent="toggleForm">Sign in</a>
                        </p>
                    </div>
                    
                    <!-- Verify Code (inline) -->
                    <div v-else-if="isVerify" class="login-content flip-in" :key="'verify'">
                        <div class="form-header" :style="{ '--brand-gap': brandGap }">
                            <picture>
                                <source srcset="/assets/logo.avif" type="image/avif">
                                <img src="/assets/logo.png" alt="eLinked logo" />
                            </picture>
                            <div class="brand-name header-name" style="margin-left: -4px;">eLinked</div>
                        </div>

                        <h1 style="text-align:center;margin-bottom:8px;">Verify Code</h1>
                        <p class="subtitle">Enter the 6-digit code sent to your email</p>

                        <div id="otp-inline" style="display:flex;gap:10px;justify-content:center;margin:18px 0 8px 0;">
                            <input class="otp-box-inline" maxlength="1" inputmode="numeric" pattern="[0-9]*" />
                            <input class="otp-box-inline" maxlength="1" inputmode="numeric" pattern="[0-9]*" />
                            <input class="otp-box-inline" maxlength="1" inputmode="numeric" pattern="[0-9]*" />
                            <input class="otp-box-inline" maxlength="1" inputmode="numeric" pattern="[0-9]*" />
                            <input class="otp-box-inline" maxlength="1" inputmode="numeric" pattern="[0-9]*" />
                            <input class="otp-box-inline" maxlength="1" inputmode="numeric" pattern="[0-9]*" />
                        </div>

                        <div style="text-align:center;margin-top:8px;">
                            <button class="btn-submit" @click.prevent="handleVerify">Proceed</button>
                        </div>

                        <p class="register-link" style="text-align:center;margin-top:12px;">Didn't receive a code? <a href="#" @click.prevent="toggleForgot">Resend</a></p>
                    </div>

                    <!-- Reset password (inline) -->
                    <div v-else-if="isReset" class="login-content flip-in" :key="'reset'">
                        <div class="form-header" :style="{ '--brand-gap': brandGap }">
                            <picture>
                                <source srcset="/assets/logo.avif" type="image/avif">
                                <img src="/assets/logo.png" alt="eLinked logo" />
                            </picture>
                            <div class="brand-name header-name" style="margin-left: -4px;">eLinked</div>
                        </div>

                        <h1 style="text-align:center;margin-bottom:8px;">Create New Password</h1>
                        <p class="subtitle">Choose a secure password for your account</p>

                        <form @submit.prevent="handleResetInline" class="login-form">
                            <div class="form-group">
                                <label for="new-pass-inline">New password</label>
                                <input id="new-pass-inline" type="password" v-model="resetPass" placeholder="New password" required />
                            </div>
                            <div class="form-group">
                                <label for="confirm-pass-inline">Confirm password</label>
                                <input id="confirm-pass-inline" type="password" v-model="resetConfirm" placeholder="Confirm password" required />
                            </div>

                            <button type="submit" class="btn-submit">Set password</button>
                        </form>

                        <p style="text-align:center;margin-top:12px;">Remembered? <a href="#" @click.prevent="toggleForgot">Sign in</a></p>
                    </div>
                        </div>
                    </div>
                </div>

                <!-- right column removed: single-column centered form -->
            </div>
        </div>
    </div>
</div>

<style>
                
/* Page background (covers whole viewport) */
html, body {
    height: 100%;
    background-color: #dfe8ff; /* stronger soft fallback */
    background-image: none; /* replaced by SVG background for consistency */
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
}

/* SVG background styling */
.bg-svg {
    position: fixed;
    inset: 0; /* top:0; right:0; bottom:0; left:0; */
    width: 100vw;
    height: 100vh;
    z-index: 0;
    pointer-events: none;
    opacity: 1;
}

.bg-svg .blob {
    transform-box: fill-box;
    transform-origin: center;
}

/* Enhanced macOS-like smooth animations */
@keyframes macosFloat1 {
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

@keyframes macosFloat2 {
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

@keyframes macosFloat3 {
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
@keyframes gradientShift1 {
    0%, 100% { 
        cx: 30%; cy: 20%; r: 60%;
    }
    50% { 
        cx: 35%; cy: 25%; r: 65%;
    }
}

@keyframes gradientShift2 {
    0%, 100% { 
        cx: 20%; cy: 30%; r: 60%;
    }
    50% { 
        cx: 18%; cy: 35%; r: 58%;
    }
}

@keyframes gradientShift3 {
    0%, 100% { 
        cx: 60%; cy: 70%; r: 70%;
    }
    50% { 
        cx: 65%; cy: 68%; r: 72%;
    }
}

.blob-1 { 
    animation: macosFloat1 16s cubic-bezier(0.25, 0.46, 0.45, 0.94) infinite;
    will-change: transform, opacity;
}
.blob-2 { 
    animation: macosFloat2 18s cubic-bezier(0.25, 0.46, 0.45, 0.94) infinite 2s;
    will-change: transform, opacity;
}
.blob-3 { 
    animation: macosFloat3 20s cubic-bezier(0.25, 0.46, 0.45, 0.94) infinite 4s;
    will-change: transform, opacity;
}

/* Apply gradient morphing to radial gradients */
#g1 { animation: gradientShift1 12s ease-in-out infinite; }
#g2 { animation: gradientShift2 15s ease-in-out infinite 1.5s; }
#g3 { animation: gradientShift3 18s ease-in-out infinite 3s; }

/* Ensure content sits above SVG */
.login-container { position: relative; z-index: 2; }
.login-book { z-index: 3; }

@keyframes flipIn {
    0% {
        transform: rotateY(90deg) translateY(18px) scale(0.97);
        opacity: 0;
    }
    60% {
        transform: rotateY(-10deg) translateY(6px) scale(1.01);
        opacity: 1;
    }
    100% {
        transform: rotateY(0deg) translateY(0) scale(1);
        opacity: 1;
    }
}

.form-flipper {
    perspective: 1000px;
    width: 100%;
    max-width: 420px;
    margin: 0 auto;
    transition: max-width 800ms cubic-bezier(.2,.9,.3,1);
}

.login-book.is-register .form-flipper {
    max-width: 720px;
}

.flip-in {
    animation: flipIn 0.8s cubic-bezier(.2,.9,.3,1) both;
    transform-style: preserve-3d;
    will-change: transform, opacity;
}

.login-container {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    min-height: 100vh;
    padding: 40px 6%;
    background: transparent;
}

.login-book {
    display: block;
    width: 100%;
    max-width: 760px;
    /* animate vertical growth between login and register */
    max-height: 560px;
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 12px 36px rgba(10, 20, 40, 0.10);
    margin-left: auto;
    margin-right: auto;
    padding: 36px 40px;
    transition: max-height 800ms cubic-bezier(.2,.9,.3,1), max-width 800ms cubic-bezier(.2,.9,.3,1), padding 600ms cubic-bezier(.2,.9,.3,1);
    will-change: max-height, max-width, padding, transform;
}

.login-book.mobile-view {
    max-width: 500px;
    max-height: 9999px;
}

.login-book.mobile-view .login-page {
    flex: none;
    width: 100%;
}

.login-inner {
    display: block;
    width: 100%;
}

.left-col {
    width: 100%;
    max-width: 540px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    padding: 6px 8px;
    transition: max-width 800ms cubic-bezier(.2,.9,.3,1);
}

.login-book.is-register .left-col {
    max-width: 720px;
}

/* top logo removed; form header contains logo and brand */

.brand-name {
    font-weight: 900;
    font-size: 23px;
    color: #111827;
    letter-spacing: -0.3px;
}

.form-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--brand-gap, 16px);
    margin-bottom: 12px;
}

.form-header img {
    max-height: 64px;
}

.header-name {
    font-size: 21px;
    line-height: 1;
}



.login-page {
    display: block;
    padding: 6px 0 0 0;
}

/* When register form is active, allow the white card to expand */
.login-book.is-register {
    max-width: 860px;
    max-height: 880px;
    padding: 36px;
}

.login-content {
    width: 100%;
    max-width: 420px;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    padding: 4px 0;
    transition: max-width 800ms cubic-bezier(.2,.9,.3,1);
}

.login-book.is-register .login-content {
    max-width: 720px;
}

.login-image { display: none; }

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
    color: #1a202c;
    font-size: 26px;
    margin-bottom: 8px;
    text-align: center;
    font-weight: 600;
}

.subtitle {
    color: #718096;
    text-align: center;
    margin-bottom: 18px;
    font-size: 14px;
}

.alert {
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-size: 14px;
}

.alert.success {
    background: #f0fdf4;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.alert.error {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.login-form {
    margin-bottom: 24px;
}

.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    margin-bottom: 8px;
    color: #2d3748;
    font-weight: 500;
    font-size: 14px;
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
    box-sizing: border-box;
}

input[type="text"]:focus,
input[type="email"]:focus,
input[type="password"]:focus {
    outline: none;
    border-color: #3b82f6;
    border-width: 2px;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    background: #ffffff;
}

input:disabled {
    background: #f7fafc;
    cursor: not-allowed;
    color: #a0aec0;
}

.btn-submit {
    width: 100%;
    padding: 12px;
    background: #4299e1;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-submit:hover:not(:disabled) {
    background: #3182ce;
    box-shadow: 0 2px 8px rgba(66, 153, 225, 0.3);
}

.btn-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.register-link {
    text-align: center;
    font-size: 14px;
    color: #718096;
}
.register-link a {
    color: #4299e1;
    text-decoration: none;
    font-weight: 600;
}

.register-link a:hover {
    color: #3182ce;
    text-decoration: underline;
}

/* Style forgot password to match register link */
.forgot-link {
    text-align: center;
    font-size: 14px;
    color: #718096;
    margin-top: 8px;
}

.forgot-link a {
    color: #4299e1;
    text-decoration: none;
    font-weight: 600;
}

.forgot-link a:hover {
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
        padding: 28px 22px;
        justify-content: center;
        width: 100%;
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

/* Validation UI Styles */
.input-wrap {
    position: relative;
    width: 100%;
    box-sizing: border-box;
}

.input-wrap input {
    width: 100%;
    box-sizing: border-box;
}

.field-with-icon .input-wrap input {
    padding-right: 48px; /* Add padding to input itself for icon space */
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

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
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
    border-color: #ef4444 !important;
    border-width: 2px;
    background: #fef2f2;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    animation: errorShakeInput 0.4s ease;
}

.input-wrap input.valid {
    border-color: #10b981 !important;
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

/* 2-Column Register Form Grid */
.register-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0 24px;
}

.register-form-grid .form-group {
    margin-bottom: 18px;
}

.register-form-grid .terms-checkbox {
    grid-column: 1 / -1;
    margin-top: 8px;
    margin-bottom: 16px;
}

.register-form-grid .btn-submit {
    grid-column: 1 / -1;
}

.checkbox-label {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    cursor: pointer;
    font-size: 13px;
    color: #4b5563;
    line-height: 1.5;
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    min-width: 18px;
    margin-top: 2px;
    cursor: pointer;
    accent-color: #4299e1;
}

.checkbox-text {
    flex: 1;
}

.checkbox-text a {
    color: #4299e1;
    text-decoration: none;
    font-weight: 600;
}

.checkbox-text a:hover {
    color: #3182ce;
    text-decoration: underline;
}

/* Select dropdown styling */
select {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: #ffffff;
    color: #2d3748;
    box-sizing: border-box;
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 8L2 4h8z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    padding-right: 36px;
}

select:focus {
    outline: none;
    border-color: #3b82f6;
    border-width: 2px;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    background-color: #ffffff;
}

select:disabled {
    background: #f7fafc;
    cursor: not-allowed;
    color: #a0aec0;
}

select option {
    padding: 8px;
}

/* Responsive: Single column on mobile */
@media (max-width: 640px) {
    .register-form-grid {
        grid-template-columns: 1fr;
        gap: 0;
    }
    
    .register-form-grid .terms-checkbox {
        grid-column: 1;
    }
    
    .register-form-grid .btn-submit {
        grid-column: 1;
    }
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
</style>

<script>
const { createApp } = Vue;

createApp({
    data() {
        return {
            appTitle: 'WeLinked',
            isRegister: false,
            isForgot: false,
            brandGap: '0px',
            forgotEmail: '',
            csrfToken: '<?= $this->request->getAttribute('csrfToken') ?>',
            credentials: {
                username: '',
                password: ''
            },
            registerData: {
                full_name: '',
                username: '',
                email: '',
                gender: '',
                password: '',
                confirmPassword: '',
                agreeToTerms: false
            },
            loading: false,
            alert: {
                show: false,
                message: '',
                type: 'error'
            },
            // Validation states
            usernameStatus: 'idle', // idle | checking | available | taken | error
            emailStatus: 'idle', // idle | checking | available | taken | invalid | error
            passwordScore: 0,
            userDebounce: null,
            emailDebounce: null
        }
    },
    methods: {
        toggleForgot() {
            this.isForgot = !this.isForgot;
            if (this.isForgot) {
                this.isRegister = false;
            }
            this.clearAlert();
            // reset form data
            this.forgotEmail = '';
        },
        async handleForgot() {
            if (!this.forgotEmail || !this.forgotEmail.includes('@')) {
                this.showAlert('Please enter a valid email', 'error');
                return;
            }

            this.loading = true;
            this.clearAlert();
            try {
                // Simulate async send
                await new Promise(res => setTimeout(res, 800));
                this.showAlert('Verification code sent. Redirecting...', 'success');
                setTimeout(() => {
                    window.location.href = "<?= $this->Url->build(['controller' => 'Passwords', 'action' => 'verify']) ?>";
                }, 700);
            } catch (e) {
                this.showAlert('Unable to send code. Try again.', 'error');
            } finally {
                this.loading = false;
            }
        },
            toggleForm() {
            this.isRegister = !this.isRegister;
            this.clearAlert();
            // Clear form data when switching
            this.credentials = { username: '', password: '' };
            this.registerData = { full_name: '', username: '', email: '', gender: '', password: '', confirmPassword: '', agreeToTerms: false };
            // Reset validation states
            this.usernameStatus = 'idle';
            this.emailStatus = 'idle';
            this.passwordScore = 0;
            // ensure other states off
            this.isForgot = false;
        },
        async handleLogin() {
            this.loading = true;
            this.clearAlert();

            console.log('=== LOGIN ATTEMPT START ===');
            console.log('Username:', this.credentials.username);
            console.log('Password length:', this.credentials.password.length);
            console.log('CSRF Token:', this.csrfToken);

            try {
                const requestBody = {
                    username: this.credentials.username,
                    password: this.credentials.password
                };
                
                console.log('Request body:', requestBody);
                
                const response = await fetch('/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': this.csrfToken
                    },
                    body: JSON.stringify(requestBody)
                });

                console.log('Response status:', response.status);
                console.log('Response headers:', Object.fromEntries(response.headers.entries()));

                const contentType = response.headers.get('content-type') || '';
                let data = null;
                let fallbackText = '';

                if (contentType.includes('application/json')) {
                    try {
                        data = await response.json();
                        console.log('Response data:', data);
                    } catch (parseError) {
                        console.warn('Failed to parse JSON response:', parseError);
                    }
                } else {
                    fallbackText = await response.text();
                    console.warn('Response is not JSON. Raw payload:', fallbackText);
                }

                if (!response.ok) {
                    const message = data && data.message ? data.message : 'Invalid username or password';
                    console.error('✗ Login failed:', message);
                    if (data && data.debug) {
                        console.error('Debug info:', data.debug);
                    }
                    this.showAlert(message, 'error');
                    console.log('=== LOGIN ATTEMPT END ===');
                    return;
                }

                if (data && data.success) {
                    console.log('✓ Login successful!');
                    this.showAlert('Login successful! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = '/dashboard';
                    }, 1000);
                    console.log('=== LOGIN ATTEMPT END ===');
                    return;
                }

                if (!data && fallbackText !== '') {
                    console.warn('No JSON body, assuming success because HTTP status was OK.');
                    this.showAlert('Login successful! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = '/dashboard';
                    }, 1000);
                    console.log('=== LOGIN ATTEMPT END ===');
                    return;
                }

                const message = data && data.message ? data.message : 'Invalid credentials';
                console.error('✗ Login failed:', message);
                if (data && data.debug) {
                    console.error('Debug info:', data.debug);
                }
                this.showAlert(message, 'error');
                
                console.log('=== LOGIN ATTEMPT END ===');
            } catch (error) {
                console.error('✗✗✗ Login error:', error);
                this.showAlert('An error occurred. Please try again.', 'error');
                console.error('Full error:', error);
            } finally {
                this.loading = false;
            }
        },
        async handleRegister() {
            this.loading = true;
            this.clearAlert();

            // Client-side validation
            if (this.registerData.password !== this.registerData.confirmPassword) {
                this.showAlert('Passwords do not match', 'error');
                this.loading = false;
                return;
            }

            if (this.registerData.password.length < 6) {
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
                        full_name: this.registerData.full_name,
                        username: this.registerData.username,
                        email: this.registerData.email,
                        gender: this.registerData.gender,
                        password: this.registerData.password,
                        confirmPassword: this.registerData.confirmPassword
                    })
                });

                let data = null;
                const contentType = response.headers.get('content-type') || '';
                if (response.ok) {
                    if (contentType.toLowerCase().includes('application/json')) {
                        data = await response.json();
                    } else {
                        const text = await response.text();
                        try {
                            data = JSON.parse(text);
                        } catch (e) {
                            data = { success: true, message: text };
                        }
                    }
                } else {
                    if (contentType.toLowerCase().includes('application/json')) {
                        data = await response.json();
                    } else {
                        const text = await response.text();
                        data = { success: false, message: text };
                    }
                }

                if (data && data.success) {
                    this.showAlert('Registration successful! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = '/dashboard';
                    }, 1000);
                } else {
                    this.showAlert((data && data.message) || 'Registration failed', 'error');
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
        },
        // Password strength validation
        updatePasswordStrength() {
            const pw = this.registerData.password || '';
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
        // Sanitize username to only allow letters, numbers, and underscores
        sanitizeUsername(event) {
            const sanitized = event.target.value.replace(/[^a-zA-Z0-9_]/g, '');
            if (sanitized !== event.target.value) {
                this.registerData.username = sanitized;
            }
        },
        // Username availability check
        async validateUsername(value) {
            const v = (value || '').trim();
            if (!v || v.length < 3) {
                this.usernameStatus = 'idle';
                return;
            }
            this.usernameStatus = 'checking';
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
                if (!res.ok) {
                    this.usernameStatus = 'error';
                    return;
                }
                const json = await res.json();
                this.usernameStatus = json && json.available ? 'available' : 'taken';
            } catch (e) {
                console.error('Username validation error:', e);
                this.usernameStatus = 'error';
            }
        },
        // Email availability check
        async validateEmail(value) {
            const v = (value || '').trim();
            const simpleEmail = /^\S+@\S+\.\S+$/;
            if (!v) {
                this.emailStatus = 'idle';
                return;
            }
            if (!simpleEmail.test(v)) {
                this.emailStatus = 'invalid';
                return;
            }
            this.emailStatus = 'checking';
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
                if (!res.ok) {
                    this.emailStatus = 'error';
                    return;
                }
                const json = await res.json();
                this.emailStatus = json && json.available ? 'available' : 'taken';
            } catch (e) {
                console.error('Email validation error:', e);
                this.emailStatus = 'error';
            }
        }
    },
    computed: {
        passwordLabel() {
            if (!this.registerData.password) return '';
            switch (this.passwordScore) {
                case 0: return '';
                case 1: return 'Weak';
                case 2: return 'Fair';
                case 3: return 'Good';
                case 4: return 'Strong';
                default: return '';
            }
        },
        confirmInvalid() {
            return this.registerData.confirmPassword && 
                   this.registerData.password !== this.registerData.confirmPassword;
        }
    },
    mounted() {
        console.log('Login app mounted!');
        
        // Check URL to see if we should show register form
        if (window.location.pathname === '/register') {
            this.isRegister = true;
        }
        if (window.location.pathname === '/forgot-password') {
            // allow direct navigation to the /forgot-password route
            // which is handled by `PasswordsController::forgot()` and its template
            // we do not toggle inline UI here; let server-rendered route show the page
        }
        
        // Watch for changes in registerData.username
        this.$watch(
            () => this.registerData.username,
            (newVal) => {
                if (this.userDebounce) clearTimeout(this.userDebounce);
                this.userDebounce = setTimeout(() => this.validateUsername(newVal), 600);
            }
        );
        
        // Watch for changes in registerData.email
        this.$watch(
            () => this.registerData.email,
            (newVal) => {
                if (this.emailDebounce) clearTimeout(this.emailDebounce);
                this.emailDebounce = setTimeout(() => this.validateEmail(newVal), 600);
            }
        );
    }
}).mount('#loginApp');
</script>
