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
                        <form @submit.prevent="handleRegister" class="login-form">
                            <div class="form-group">
                                <label for="reg-username">Username</label>
                                <input
                                    type="text"
                                    id="reg-username"
                                    v-model="registerData.username"
                                    placeholder="Choose a username"
                                    required
                                    :disabled="loading"
                                />
                            </div>

                            <div class="form-group">
                                <label for="reg-fullname">Full name</label>
                                <input
                                    type="text"
                                    id="reg-fullname"
                                    v-model="registerData.full_name"
                                    placeholder="Your full name"
                                    required
                                    :disabled="loading"
                                />
                            </div>

                            <div class="form-group">
                                <label for="reg-email">Email</label>
                                <input
                                    type="email"
                                    id="reg-email"
                                    v-model="registerData.email"
                                    placeholder="Enter your email"
                                    required
                                    :disabled="loading"
                                />
                            </div>

                            <div class="form-group">
                                <label for="reg-password">Password</label>
                                <input 
                                    type="password"
                                    id="reg-password"
                                    v-model="registerData.password"
                                    placeholder="Choose a password (min 6 characters)"
                                    required
                                    :disabled="loading"
                                />
                            </div>

                            <div class="form-group">
                                <label for="reg-confirm">Confirm Password</label>
                                <input 
                                    type="password"
                                    id="reg-confirm"
                                    v-model="registerData.confirmPassword"
                                    placeholder="Confirm your password"
                                    required
                                    :disabled="loading"
                                />
                            </div>

                            <button 
                                type="submit" 
                                class="btn-submit"
                                :disabled="loading"
                            >
                                <span v-if="!loading">Sign Up</span>
                                <span v-else>Creating account...</span>
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

@keyframes floatA {
    0% { transform: translate3d(0,0,0); }
    50% { transform: translate3d(-18px,8px,0); }
    100% { transform: translate3d(0,0,0); }
}

@keyframes floatB {
    0% { transform: translate3d(0,0,0); }
    50% { transform: translate3d(20px,-12px,0); }
    100% { transform: translate3d(0,0,0); }
}

@keyframes floatC {
    0% { transform: translate3d(0,0,0); }
    50% { transform: translate3d(-10px,-6px,0); }
    100% { transform: translate3d(0,0,0); }
}

.blob-1 { animation: floatA 18s ease-in-out infinite; }
.blob-2 { animation: floatB 22s ease-in-out infinite; }
.blob-3 { animation: floatC 26s ease-in-out infinite; }

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
    transition: max-height 800ms cubic-bezier(.2,.9,.3,1), padding 600ms cubic-bezier(.2,.9,.3,1);
    will-change: max-height, padding, transform;
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
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.2s;
    background: #ffffff;
    color: #2d3748;
}

input[type="text"]:focus,
input[type="email"]:focus,
input[type="password"]:focus {
    outline: none;
    border-color: #4299e1;
    box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
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
                password: '',
                confirmPassword: ''
            },
            loading: false,
            alert: {
                show: false,
                message: '',
                type: 'error'
            }
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
            this.registerData = { full_name: '', username: '', password: '', confirmPassword: '' };
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
    }
}).mount('#loginApp');
</script>
