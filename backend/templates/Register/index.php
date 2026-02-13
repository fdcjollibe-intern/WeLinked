<div id="registerApp">
    <div class="login-container">
        <div class="login-book <?= isset($hideImage) && $hideImage ? 'mobile-view' : '' ?>">
            <!-- Left Column removed: only right-side form remains -->
            <div class="login-page">
                <div class="login-content">
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
                                    :class="{ 'invalid': usernameStatus==='taken', 'valid': usernameStatus==='available' }"
                                />
                                <span class="status-icon" v-if="usernameStatus==='checking'">●</span>
                                <span class="status-icon success" v-if="usernameStatus==='available'">✓</span>
                                <span class="status-icon error" v-if="usernameStatus==='taken'">✕</span>
                            </div>
                            <div class="short-msg" v-if="usernameStatus==='checking'">Checking...</div>
                            <div class="short-msg success" v-if="usernameStatus==='available'">Available</div>
                            <div class="short-msg error" v-if="usernameStatus==='taken'">Username already taken</div>
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
                                    :class="{ 'invalid': emailStatus==='invalid' || emailStatus==='taken', 'valid': emailStatus==='available' }"
                                />
                                <span class="status-icon" v-if="emailStatus==='checking'">●</span>
                                <span class="status-icon success" v-if="emailStatus==='available'">✓</span>
                                <span class="status-icon error" v-if="emailStatus==='taken' || emailStatus==='invalid'">✕</span>
                            </div>
                            <div class="short-msg" v-if="emailStatus==='checking'">Checking...</div>
                            <div class="short-msg success" v-if="emailStatus==='available'">Looks good</div>
                            <div class="short-msg error" v-if="emailStatus==='invalid'">Invalid email</div>
                            <div class="short-msg error" v-if="emailStatus==='taken'">Email already in use</div>
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
                            <span v-if="!loading">Sign Up</span>
                            <span v-else>Creating account...</span>
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

.login-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 20px;
    background: #f7fafc;
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
    color: #1a202c;
    font-size: 28px;
    margin-bottom: 8px;
    text-align: center;
    font-weight: 600;
}

.subtitle {
    color: #718096;
    text-align: center;
    margin-bottom: 32px;
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

.register-form {
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

.field-with-icon .input-wrap{position:relative;padding-right:44px}
.status-icon{position:absolute;right:8px;top:50%;transform:translateY(-50%);display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:9999px;font-size:12px;background:transparent;color:#9ca3af}
.status-icon.success{background:rgba(22,163,74,0.12);color:#16a34a}
.status-icon.error{background:rgba(220,38,38,0.08);color:#dc2626}
.status-icon.checking{color:#9ca3af}
.status-icon.checking::after{content:'';width:12px;height:12px;border:2px solid #9ca3af;border-top-color:transparent;border-radius:50%;display:inline-block;animation:spin 0.9s linear infinite}
.short-msg{font-size:12px;margin-top:6px;color:#6b7280}
.short-msg.success{color:#16a34a}
.short-msg.error{color:#dc2626}
.input-wrap input.invalid{border-color:#dc2626;box-shadow:0 0 0 3px rgba(220,38,38,0.06)}
.input-wrap input.valid{border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,0.06)}

.pw-strength{display:flex;align-items:center;gap:12px;margin-top:8px}
.pw-strength .bar{display:flex;gap:6px}
.pw-strength .seg{width:36px;height:6px;background:#eef2f7;border-radius:3px;transition:background 220ms ease}
.bar.s0 .seg{background:#eef2f7}
.bar.s1 .seg:nth-child(-n+1){background:#fb923c}
.bar.s2 .seg:nth-child(-n+2){background:#fb923c}
.bar.s3 .seg:nth-child(-n+3){background:#f59e0b}
.bar.s4 .seg{background:#16a34a}
.pw-label{font-size:12px;color:#6b7280}

@keyframes spin{from{transform:rotate(0)}to{transform:rotate(360deg)}}

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
            passwordScore: 0
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
                        'X-CSRF-Token': '<?= $this->request->getAttribute('csrfToken') ?>'
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
        // compute simple password strength
        updatePasswordStrength() {
            const pw = this.credentials.password || '';
            let score = 0;
            if (pw.length >= 6) score++;
            if (pw.length >= 8) score++;
            if (/[0-9]/.test(pw)) score++;
            if (/[^A-Za-z0-9]/.test(pw)) score++;
            this.passwordScore = Math.min(score, 4);
        },
        get passwordLabel() {
            switch (this.passwordScore) {
                case 0: return 'Very weak';
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
            if (!v || v.length < 3) { this.usernameStatus = 'idle'; return; }
            this.usernameStatus = 'checking';
            try {
                const res = await fetch('/register/check-username', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ username: v })
                });
                if (!res.ok) { this.usernameStatus = 'error'; return; }
                const json = await res.json();
                this.usernameStatus = json && json.available ? 'available' : 'taken';
            } catch (e) {
                this.usernameStatus = 'error';
            }
        },
        async validateEmail(value) {
            const v = (value || '').trim();
            const simpleEmail = /^\S+@\S+\.\S+$/;
            if (!v) { this.emailStatus = 'idle'; return; }
            if (!simpleEmail.test(v)) { this.emailStatus = 'invalid'; return; }
            this.emailStatus = 'checking';
            try {
                const res = await fetch('/register/check-email', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ email: v })
                });
                if (!res.ok) { this.emailStatus = 'error'; return; }
                const json = await res.json();
                this.emailStatus = json && json.available ? 'available' : 'taken';
            } catch (e) {
                this.emailStatus = 'error';
            }
        }
    },
    mounted() {
        console.log('Register app mounted!');
            this.$watch(
                () => this.credentials.username,
                (nv) => {
                    if (this.userDebounce) clearTimeout(this.userDebounce);
                    this.userDebounce = setTimeout(() => this.validateUsername(nv), 600);
                }
            );

            this.$watch(
                () => this.credentials.email,
                (nv) => {
                    if (this.emailDebounce) clearTimeout(this.emailDebounce);
                    this.emailDebounce = setTimeout(() => this.validateEmail(nv), 600);
                }
            );
    }
}).mount('#registerApp');
</script>
