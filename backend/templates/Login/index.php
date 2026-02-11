<div id="loginApp">
    <div class="login-container">
        <div class="login-book <?= isset($hideImage) && $hideImage ? 'mobile-view' : '' ?>">
            <!-- Left Column - Image -->
            <?php if (!isset($hideImage) || !$hideImage): ?>
            <div class="login-image">
                <div class="image-overlay">
                    <h2>{{ isRegister ? 'Join WeLinked!' : 'Welcome Back!' }}</h2>
                    <p>{{ isRegister ? 'Create your account and start connecting with friends and the world around you.' : 'Connect with friends and the world around you on WeLinked.' }}</p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Right Column - Form Flipper -->
            <div class="login-page">
                <div class="form-flipper">
                    <!-- Login Form -->
                    <div v-if="!isRegister" class="login-content flip-in" :key="'login'">
                        <h1>{{ appTitle }}</h1>
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
                    </div>

                    <!-- Register Form -->
                    <div v-else class="login-content flip-in" :key="'register'">
                        <h1>{{ appTitle }}</h1>
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
    max-width: 420px;
    margin: 0 auto;
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
    width: 100%;
    max-width: 1100px;
    min-height: 600px;
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
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
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    background: #ffffff;
}

.login-content {
    width: 100%;
    max-width: 420px;
    min-height: 620px;
    display: flex;
    flex-direction: column;
    justify-content: center;
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
            isRegister: false,
            csrfToken: '<?= $this->request->getAttribute('csrfToken') ?>',
            credentials: {
                username: '',
                password: ''
            },
            registerData: {
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
        toggleForm() {
            this.isRegister = !this.isRegister;
            this.clearAlert();
            // Clear form data when switching
            this.credentials = { username: '', password: '' };
            this.registerData = { username: '', email: '', password: '', confirmPassword: '' };
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
                        window.location.href = '/users/dashboard';
                    }, 1000);
                    console.log('=== LOGIN ATTEMPT END ===');
                    return;
                }

                if (!data && fallbackText !== '') {
                    console.warn('No JSON body, assuming success because HTTP status was OK.');
                    this.showAlert('Login successful! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = '/users/dashboard';
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
                        window.location.href = '/users/dashboard';
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
    }
}).mount('#loginApp');
</script>
