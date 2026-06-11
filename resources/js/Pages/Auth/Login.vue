<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

defineProps({
    canResetPassword: { type: Boolean, default: true },
    status: { type: String, default: null },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);
const emailFocused = ref(false);
const passFocused = ref(false);

function submit() {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
}

const emailValid = computed(() =>
    form.email.length > 0 && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)
);
const passStrength = computed(() => {
    const p = form.password;
    if (!p) return 0;
    let s = 0;
    if (p.length >= 8) s++;
    if (/[A-Z]/.test(p)) s++;
    if (/[0-9]/.test(p)) s++;
    if (/[^A-Za-z0-9]/.test(p)) s++;
    return s;
});
const strengthLabel = computed(() =>
    ['', 'Weak', 'Fair', 'Good', 'Strong'][passStrength.value]
);
const strengthColor = computed(() =>
    ['', '#ef4444', '#f59e0b', '#3b82f6', '#10b981'][passStrength.value]
);

const toggleDark = () => {
    const isDark = document.documentElement.classList.contains('dark')
    if (isDark) {
        document.documentElement.classList.remove('dark')
        localStorage.setItem('theme', 'light')
    } else {
        document.documentElement.classList.add('dark')
        localStorage.setItem('theme', 'dark')
    }
}

onMounted(() => {
    const theme = localStorage.getItem('theme')
    if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark')
    }
})
</script>

<template>
    <Head title="Sign In — ClinicOS" />

    <div class="auth-root">

        <!-- LEFT PANEL -->
        <aside class="left-panel">
            <div class="orb orb-1"></div>
            <div class="orb orb-2"></div>
            <div class="orb orb-3"></div>
            <div class="grid-overlay"></div>

            <div class="left-content">
                <div class="left-logo">
                    <div class="logo-mark">
                        <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="40" height="40" rx="12" fill="white" fill-opacity="0.15" />
                            <path d="M7 20h4l3-8 4 16 3-11 2 3h10" stroke="white" stroke-width="2.5"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="logo-text">
                        <span class="logo-name">ClinicOS</span>
                        <span class="logo-tag">Management System</span>
                    </div>
                </div>

                <div class="left-hero">
                    <h1 class="left-title">Healthcare,<br><em>Simplified.</em></h1>
                    <p class="left-sub">
                        One intelligent platform to manage patients, appointments, billing,
                        prescriptions and analytics — so you can focus on what matters most:
                        your patients.
                    </p>
                </div>

                <div class="feature-pills">
                    <div class="pill">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        Patient Records
                    </div>
                    <div class="pill">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        Smart Scheduling
                    </div>
                    <div class="pill">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        Billing & Insurance
                    </div>
                    <div class="pill">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        Analytics
                    </div>
                    <div class="pill">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        Lab Reports
                    </div>
                    <div class="pill">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                        Prescriptions
                    </div>
                </div>

                <div class="trust-badge">
                    <div class="trust-avatars">
                        <span class="avatar av1">DR</span>
                        <span class="avatar av2">NR</span>
                        <span class="avatar av3">KP</span>
                        <span class="avatar av4">+</span>
                    </div>
                    <div class="trust-text">
                        <strong>120+ clinics</strong> trust ClinicOS
                        <span>4.9 ★ average rating</span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- RIGHT PANEL -->
        <main class="right-panel">
            <div class="top-bar">
                <button class="dark-toggle" @click="toggleDark" type="button"
                    aria-label="Toggle dark mode">
                    <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="5" />
                        <line x1="12" y1="1" x2="12" y2="3" />
                        <line x1="12" y1="21" x2="12" y2="23" />
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" />
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78" />
                        <line x1="1" y1="12" x2="3" y2="12" />
                        <line x1="21" y1="12" x2="23" y2="12" />
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36" />
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22" />
                    </svg>
                    <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                    </svg>
                </button>
                <span class="need-help">Need help? <a href="mailto:support@clinicos.com">Contact Support</a></span>
            </div>

            <div class="form-card">
                <div class="form-header">
                    <div class="form-logo">
                        <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="40" height="40" rx="11" fill="#0d9488" />
                            <path d="M7 20h4l3-8 4 16 3-11 2 3h10" stroke="white" stroke-width="2.5"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <h2 class="form-title">Welcome back</h2>
                    <p class="form-subtitle">Sign in to your ClinicOS account</p>
                </div>

                <div v-if="status" class="alert-success">
                    <svg viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="form-body" novalidate>

                    <!-- Email -->
                    <div class="field-group"
                        :class="{ focused: emailFocused, filled: form.email, valid: emailValid, error: form.errors.email }">
                        <label for="email" class="field-label">Email Address <span class="required-star">*</span></label>
                        <div class="field-wrap">
                            <span class="field-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <input id="email" v-model="form.email" type="email" autocomplete="username"
                                placeholder="doctor@yourclinic.com" class="field-input" @focus="emailFocused = true"
                                @blur="emailFocused = false" required />
                            <span v-if="emailValid" class="field-check">
                                <svg viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </span>
                        </div>
                        <p v-if="form.errors.email" class="field-error">
                            <svg viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div class="field-group"
                        :class="{ focused: passFocused, filled: form.password, error: form.errors.password }">
                        <div class="field-label-row">
                            <label for="password" class="field-label">Password <span class="required-star">*</span></label>
                            <Link v-if="canResetPassword" :href="route('password.request')" class="forgot-link">
                                Forgot password?
                            </Link>
                        </div>
                        <div class="field-wrap">
                            <span class="field-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                    <path stroke-linecap="round" d="M7 11V7a5 5 0 0110 0v4" />
                                </svg>
                            </span>
                            <input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'"
                                autocomplete="current-password" placeholder="Enter your password" class="field-input"
                                @focus="passFocused = true" @blur="passFocused = false" required />
                            <button type="button" class="pass-toggle" @click="showPassword = !showPassword"
                                :aria-label="showPassword ? 'Hide password' : 'Show password'" tabindex="0">
                                <svg v-if="!showPassword" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>

                        <div v-if="form.password.length > 0" class="strength-wrap">
                            <div class="strength-bars">
                                <span class="sbar" :class="{ active: passStrength >= 1 }"
                                    :style="passStrength >= 1 ? `background:${strengthColor}` : ''"></span>
                                <span class="sbar" :class="{ active: passStrength >= 2 }"
                                    :style="passStrength >= 2 ? `background:${strengthColor}` : ''"></span>
                                <span class="sbar" :class="{ active: passStrength >= 3 }"
                                    :style="passStrength >= 3 ? `background:${strengthColor}` : ''"></span>
                                <span class="sbar" :class="{ active: passStrength >= 4 }"
                                    :style="passStrength >= 4 ? `background:${strengthColor}` : ''"></span>
                            </div>
                            <span class="strength-label" :style="`color:${strengthColor}`">{{ strengthLabel }}</span>
                        </div>

                        <p v-if="form.errors.password" class="field-error">
                            <svg viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Remember me -->
                    <div class="remember-row">
                        <label class="checkbox-label">
                            <span class="checkbox-wrap">
                                <input type="checkbox" v-model="form.remember" class="checkbox-input" />
                                <span class="checkbox-custom">
                                    <svg viewBox="0 0 12 10" fill="none">
                                        <polyline points="1,5 4.5,8.5 11,1" stroke="white" stroke-width="1.8"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </span>
                            <span class="checkbox-text">Keep me signed in</span>
                        </label>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="submit-btn" :class="{ loading: form.processing }"
                        :disabled="form.processing">
                        <span v-if="!form.processing" class="btn-inner">
                            Sign In to ClinicOS
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                        <span v-else class="btn-inner">
                            <span class="spinner"></span>
                            Authenticating…
                        </span>
                    </button>

                    <div class="divider"><span>New to ClinicOS?</span></div>

                    <div class="text-center">
                        <Link :href="route('register')"
                            class="text-sm font-medium text-teal-600 dark:text-teal-400 hover:underline">
                            Create an account
                        </Link>
                    </div>

                </form>

                <div class="form-footer">
                    <p class="footer-copy">
                        © {{ new Date().getFullYear() }} ClinicOS ·
                        <a href="#">Privacy Policy</a> ·
                        <a href="#">Terms of Use</a>
                    </p>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,600&family=DM+Serif+Display:ital@0;1&display=swap');

:root {
    --teal: #0d9488;
    --teal-dark: #0f766e;
    --teal-light: #f0fdfa;
    --teal-ring: rgba(13, 148, 136, 0.25);
    --bg: #f0f4f8;
    --surface: #ffffff;
    --surface2: #f8fafc;
    --border: #e2e8f0;
    --border-focus: #0d9488;
    --text: #0f172a;
    --text2: #475569;
    --text3: #94a3b8;
    --error: #ef4444;
    --success: #10b981;
    --left-from: #0a4f4a;
    --left-to: #0d3b37;
    --radius-card: 24px;
    --radius-field: 12px;
    --shadow-card: 0 20px 60px rgba(0,0,0,0.10), 0 4px 16px rgba(0,0,0,0.06);
    --font-body: 'DM Sans', sans-serif;
    --font-display: 'DM Serif Display', serif;
}

.dark-mode {
    --bg: #0b0f1a;
    --surface: #141c2b;
    --surface2: #1a2439;
    --border: #1e2d45;
    --text: #f1f5f9;
    --text2: #94a3b8;
    --text3: #4a5568;
    --teal-light: rgba(13,148,136,0.12);
    --shadow-card: 0 20px 60px rgba(0,0,0,0.5), 0 4px 16px rgba(0,0,0,0.3);
}

* { box-sizing: border-box; margin: 0; padding: 0; }

.auth-root {
    display: flex;
    min-height: 100vh;
    background: var(--bg);
    font-family: var(--font-body);
    color: var(--text);
    transition: background 0.3s, color 0.3s;
}

.left-panel {
    position: relative;
    flex: 0 0 480px;
    background: linear-gradient(160deg, var(--left-from) 0%, var(--left-to) 100%);
    overflow: hidden;
    display: flex;
    align-items: stretch;
}

@media (max-width: 1024px) { .left-panel { display: none; } }

.orb { position: absolute; border-radius: 50%; filter: blur(60px); pointer-events: none; }
.orb-1 { width: 400px; height: 400px; top: -120px; right: -100px; background: rgba(20,184,166,0.25); }
.orb-2 { width: 300px; height: 300px; bottom: 60px; left: -80px; background: rgba(6,182,212,0.15); }
.orb-3 { width: 200px; height: 200px; top: 50%; left: 50%; transform: translate(-50%,-50%); background: rgba(255,255,255,0.04); }

.grid-overlay {
    position: absolute; inset: 0;
    background-image:
        linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
    background-size: 40px 40px;
    pointer-events: none;
}

.left-content { position: relative; z-index: 2; display: flex; flex-direction: column; padding: 48px 48px 40px; gap: 40px; width: 100%; }
.left-logo { display: flex; align-items: center; gap: 12px; }
.logo-mark svg { width: 44px; height: 44px; }
.logo-text { display: flex; flex-direction: column; }
.logo-name { font-family: var(--font-body); font-size: 20px; font-weight: 800; color: #fff; letter-spacing: -0.5px; }
.logo-tag { font-size: 10px; color: rgba(255,255,255,0.5); text-transform: uppercase; letter-spacing: 2px; margin-top: -1px; }

.left-hero { flex: 1; display: flex; flex-direction: column; justify-content: center; }
.left-title { font-family: var(--font-display); font-size: clamp(42px,5vw,56px); line-height: 1.1; color: #fff; font-weight: 400; letter-spacing: -1px; }
.left-title em { font-style: italic; color: #5eead4; }
.left-sub { margin-top: 20px; font-size: 15px; color: rgba(255,255,255,0.6); line-height: 1.75; max-width: 360px; }

.feature-pills { display: flex; flex-wrap: wrap; gap: 8px; }
.pill { display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); border-radius: 100px; padding: 6px 14px; font-size: 12px; color: rgba(255,255,255,0.85); font-weight: 500; backdrop-filter: blur(8px); }
.pill svg { width: 12px; height: 12px; color: #5eead4; flex-shrink: 0; }

.trust-badge { display: flex; align-items: center; gap: 14px; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12); border-radius: 16px; padding: 14px 18px; backdrop-filter: blur(10px); }
.trust-avatars { display: flex; }
.avatar { width: 32px; height: 32px; border-radius: 50%; border: 2px solid rgba(255,255,255,0.3); display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 700; color: white; margin-left: -8px; }
.avatar:first-child { margin-left: 0; }
.av1 { background: #0891b2; }
.av2 { background: #7c3aed; }
.av3 { background: #dc2626; }
.av4 { background: rgba(255,255,255,0.2); font-size: 12px; }
.trust-text { font-size: 13px; color: rgba(255,255,255,0.75); line-height: 1.5; }
.trust-text strong { color: white; display: block; }
.trust-text span { font-size: 11px; }

.right-panel {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 24px 20px 32px;
    background: var(--bg);
    transition: background 0.3s;
    position: relative;
}

.top-bar { position: absolute; top: 20px; right: 24px; display: flex; align-items: center; gap: 16px; }

.dark-toggle {
    width: 40px; height: 40px; border-radius: 12px; background: var(--surface);
    border: 1px solid var(--border); display: flex; align-items: center; justify-content: center;
    cursor: pointer; color: var(--text2); transition: all 0.2s;
}
.dark-toggle:hover { background: var(--surface2); color: var(--teal); }
.dark-toggle svg { width: 18px; height: 18px; }
.icon-sun { display: none; }
.icon-moon { display: block; }
.dark-mode .icon-sun { display: block; }
.dark-mode .icon-moon { display: none; }

.need-help { font-size: 13px; color: var(--text3); }
.need-help a { color: var(--teal); text-decoration: none; font-weight: 500; }

.form-card {
    width: 100%;
    max-width: 460px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-card);
    box-shadow: var(--shadow-card);
    overflow: hidden;
    transition: background 0.3s, border-color 0.3s, box-shadow 0.3s;
    animation: cardIn 0.5s cubic-bezier(0.22,1,0.36,1) both;
}

@keyframes cardIn { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }

.form-header { padding: 36px 40px 28px; border-bottom: 1px solid var(--border); text-align: center; background: var(--surface2); }
.form-logo { margin-bottom: 16px; display: flex; justify-content: center; }
.form-logo svg { width: 48px; height: 48px; border-radius: 14px; }
.form-title { font-family: var(--font-body); font-size: 26px; font-weight: 700; color: var(--text); letter-spacing: -0.7px; }
.form-subtitle { margin-top: 6px; font-size: 14px; color: var(--text3); }

.required-star { color: #ef4444; }

.alert-success {
    margin: 20px 40px 0;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    background: rgba(16,185,129,0.08);
    border: 1px solid rgba(16,185,129,0.25);
    border-radius: 12px;
    padding: 12px 16px;
    font-size: 13px;
    color: #065f46;
    animation: fadeIn 0.3s ease;
}
.dark-mode .alert-success { color: #6ee7b7; background: rgba(16,185,129,0.1); border-color: rgba(16,185,129,0.2); }
.alert-success svg { width: 18px; height: 18px; flex-shrink: 0; color: #10b981; margin-top: 1px; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }

.form-body { padding: 28px 40px 32px; display: flex; flex-direction: column; gap: 20px; }
.field-group { display: flex; flex-direction: column; gap: 6px; }
.field-label { font-size: 13px; font-weight: 600; color: var(--text2); letter-spacing: 0.1px; transition: color 0.2s; }
.field-group.focused .field-label { color: var(--teal); }
.field-label-row { display: flex; align-items: center; justify-content: space-between; }
.forgot-link { font-size: 12px; font-weight: 600; color: var(--teal); text-decoration: none; }

.field-wrap {
    position: relative;
    display: flex;
    align-items: center;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-field);
    background: var(--surface);
    transition: border-color 0.2s, box-shadow 0.2s, background 0.3s;
    overflow: hidden;
}
.field-group.focused .field-wrap { border-color: var(--border-focus); box-shadow: 0 0 0 3px var(--teal-ring); }
.field-group.error .field-wrap { border-color: var(--error); box-shadow: 0 0 0 3px rgba(239,68,68,0.15); }
.field-group.valid .field-wrap { border-color: var(--success); }

.field-icon { display: flex; align-items: center; justify-content: center; width: 44px; flex-shrink: 0; color: var(--text3); transition: color 0.2s; }
.field-group.focused .field-icon { color: var(--teal); }
.field-icon svg { width: 18px; height: 18px; }

.field-input {
    flex: 1;
    padding: 13px 12px 13px 0;
    font-family: var(--font-body);
    font-size: 14px;
    color: var(--text);
    background: transparent;
    border: none;
    outline: none;
    min-width: 0;
}
.field-input::placeholder { color: var(--text3); }

.field-check { width: 36px; display: flex; align-items: center; justify-content: center; color: var(--success); animation: popIn 0.2s cubic-bezier(0.34,1.56,0.64,1); }
.field-check svg { width: 16px; height: 16px; }
@keyframes popIn { from { opacity: 0; transform: scale(0.5); } to { opacity: 1; transform: scale(1); } }

.pass-toggle { width: 44px; height: 100%; display: flex; align-items: center; justify-content: center; background: none; border: none; cursor: pointer; color: var(--text3); flex-shrink: 0; padding: 0; }
.pass-toggle:hover { color: var(--teal); }
.pass-toggle svg { width: 18px; height: 18px; }

.field-error { display: flex; align-items: center; gap: 5px; font-size: 12px; color: var(--error); font-weight: 500; animation: fadeIn 0.2s ease; }
.field-error svg { width: 14px; height: 14px; flex-shrink: 0; }

.strength-wrap { display: flex; align-items: center; gap: 8px; margin-top: 2px; }
.strength-bars { display: flex; gap: 4px; flex: 1; }
.sbar { height: 3px; flex: 1; border-radius: 100px; background: var(--border); transition: background 0.3s; }
.strength-label { font-size: 11px; font-weight: 600; white-space: nowrap; min-width: 40px; }

.remember-row { margin-top: -4px; }
.checkbox-label { display: inline-flex; align-items: center; gap: 10px; cursor: pointer; user-select: none; }
.checkbox-wrap { position: relative; flex-shrink: 0; }
.checkbox-input { position: absolute; opacity: 0; width: 0; height: 0; }
.checkbox-custom { display: flex; align-items: center; justify-content: center; width: 18px; height: 18px; border: 1.5px solid var(--border); border-radius: 5px; background: var(--surface); transition: all 0.2s; }
.checkbox-custom svg { width: 12px; height: 10px; opacity: 0; transition: opacity 0.15s; }
.checkbox-input:checked + .checkbox-custom { background: var(--teal); border-color: var(--teal); }
.checkbox-input:checked + .checkbox-custom svg { opacity: 1; }
.checkbox-text { font-size: 13px; color: var(--text2); }

.submit-btn {
    width: 100%;
    padding: 14px 24px;
    background: linear-gradient(135deg, var(--teal) 0%, var(--teal-dark) 100%);
    color: white;
    border: none;
    border-radius: var(--radius-field);
    font-family: var(--font-body);
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 4px 16px rgba(13,148,136,0.35);
    letter-spacing: -0.2px;
    margin-top: 4px;
}
.submit-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(13,148,136,0.45); }
.submit-btn:active:not(:disabled) { transform: translateY(0); }
.submit-btn:disabled { opacity: 0.75; cursor: not-allowed; }
.btn-inner { display: flex; align-items: center; justify-content: center; gap: 8px; }
.btn-inner svg { width: 18px; height: 18px; }

.spinner { width: 18px; height: 18px; border: 2.5px solid rgba(255,255,255,0.35); border-top-color: #fff; border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.divider { display: flex; align-items: center; gap: 12px; color: var(--text3); font-size: 12px; }
.divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }

.form-footer { padding: 16px 40px 24px; border-top: 1px solid var(--border); text-align: center; }
.footer-copy { font-size: 12px; color: var(--text3); }
.footer-copy a { color: var(--text3); text-decoration: none; }
.footer-copy a:hover { color: var(--teal); }

@media (max-width: 520px) {
    .form-header { padding: 28px 24px 20px; }
    .form-body { padding: 24px 24px 28px; }
    .form-footer { padding: 14px 24px 20px; }
    .alert-success { margin: 16px 24px 0; }
    .form-card { border-radius: 20px; }
    .top-bar { top: 14px; right: 14px; }
    .need-help { display: none; }
}

* { transition-property: background-color, border-color, color, box-shadow; transition-duration: 200ms; transition-timing-function: ease; }
</style>
