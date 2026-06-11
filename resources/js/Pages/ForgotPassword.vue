<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

defineProps({
    status: { type: String, default: null },
});

const form = useForm({ email: '' });
const emailFocused = ref(false);
const emailValid = computed(() =>
    form.email.length > 0 && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)
);

function submit() {
    form.post(route('password.email'));
}
</script>

<template>
    <Head title="Reset Password — ClinicOS" />

    <div class="auth-root">

        <!-- Background orbs -->
        <div class="bg-orb orb-a"></div>
        <div class="bg-orb orb-b"></div>

        <!-- Card -->
        <div class="reset-card">

            <!-- Back link -->
            <Link :href="route('login')" class="back-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m7-7l-7 7 7 7"/>
                </svg>
                Back to Sign In
            </Link>

            <!-- Icon -->
            <div class="icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>

            <h1 class="card-title">Forgot your password?</h1>
            <p class="card-sub">
                No worries — enter your registered email address and we'll send you a secure 
                link to reset your password.
            </p>

            <!-- Success state -->
            <div v-if="status" class="alert-success">
                <svg viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <strong>Email sent!</strong>
                    <p>{{ status }}</p>
                </div>
            </div>

            <form @submit.prevent="submit" novalidate>
                <div class="field-group" :class="{ focused: emailFocused, valid: emailValid, error: form.errors.email }">
                    <label for="email" class="field-label">Email Address</label>
                    <div class="field-wrap">
                        <span class="field-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </span>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            placeholder="doctor@yourclinic.com"
                            class="field-input"
                            @focus="emailFocused = true"
                            @blur="emailFocused = false"
                            required
                        />
                        <span v-if="emailValid" class="field-check">
                            <svg viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                    </div>
                    <p v-if="form.errors.email" class="field-error">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ form.errors.email }}
                    </p>
                </div>

                <!-- How it works steps -->
                <div class="steps">
                    <div class="step">
                        <div class="step-num">1</div>
                        <span>Enter your registered email address above</span>
                    </div>
                    <div class="step">
                        <div class="step-num">2</div>
                        <span>Check your inbox for a password reset email</span>
                    </div>
                    <div class="step">
                        <div class="step-num">3</div>
                        <span>Click the secure link and create a new password</span>
                    </div>
                </div>

                <button
                    type="submit"
                    class="submit-btn"
                    :class="{ loading: form.processing }"
                    :disabled="form.processing || !emailValid"
                >
                    <span v-if="!form.processing" class="btn-inner">
                        Send Reset Link
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </span>
                    <span v-else class="btn-inner">
                        <span class="spinner"></span>
                        Sending…
                    </span>
                </button>
            </form>

            <p class="footer-note">
                Remember your password?
                <Link :href="route('login')">Sign in instead</Link>
            </p>
        </div>

    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap');

:root {
    --teal: #0d9488; --teal-dark: #0f766e; --teal-ring: rgba(13,148,136,0.25);
    --bg: #f0f4f8; --surface: #ffffff; --surface2: #f8fafc;
    --border: #e2e8f0; --text: #0f172a; --text2: #475569; --text3: #94a3b8;
    --error: #ef4444; --success: #10b981;
}
.dark-mode {
    --bg: #0b0f1a; --surface: #141c2b; --surface2: #1a2439;
    --border: #1e2d45; --text: #f1f5f9; --text2: #94a3b8; --text3: #4a5568;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.auth-root {
    min-height: 100vh;
    background: var(--bg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'DM Sans', sans-serif;
    color: var(--text);
    padding: 24px;
    position: relative;
    overflow: hidden;
    transition: background 0.3s;
}

.bg-orb {
    position: fixed;
    border-radius: 50%;
    filter: blur(80px);
    pointer-events: none;
    z-index: 0;
}
.orb-a {
    width: 500px; height: 500px;
    top: -150px; right: -100px;
    background: rgba(13, 148, 136, 0.08);
}
.orb-b {
    width: 350px; height: 350px;
    bottom: -100px; left: -80px;
    background: rgba(37, 99, 235, 0.06);
}

.reset-card {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 460px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 24px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.10), 0 4px 16px rgba(0,0,0,0.06);
    padding: 40px;
    animation: slideUp 0.45s cubic-bezier(0.22,1,0.36,1) both;
    transition: background 0.3s, border-color 0.3s;
}
.dark-mode .reset-card {
    box-shadow: 0 20px 60px rgba(0,0,0,0.5), 0 4px 16px rgba(0,0,0,0.3);
}
@keyframes slideUp { from { opacity:0; transform:translateY(28px); } to { opacity:1; transform:translateY(0); } }

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    color: var(--text3);
    text-decoration: none;
    margin-bottom: 28px;
    transition: color 0.2s;
}
.back-link:hover { color: var(--teal); }
.back-link svg { width: 16px; height: 16px; }

.icon-wrap {
    width: 60px; height: 60px;
    border-radius: 18px;
    background: rgba(13,148,136,0.08);
    border: 1px solid rgba(13,148,136,0.15);
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 20px;
    color: var(--teal);
}
.icon-wrap svg { width: 28px; height: 28px; }

.card-title {
    font-size: 24px;
    font-weight: 800;
    color: var(--text);
    letter-spacing: -0.6px;
    margin-bottom: 10px;
}
.card-sub {
    font-size: 14px;
    color: var(--text2);
    line-height: 1.65;
    margin-bottom: 24px;
}

.alert-success {
    display: flex; align-items: flex-start; gap: 12px;
    background: rgba(16,185,129,0.08);
    border: 1px solid rgba(16,185,129,0.25);
    border-radius: 12px;
    padding: 14px 16px;
    margin-bottom: 20px;
    font-size: 13px;
    animation: fadeIn 0.3s ease;
}
.alert-success svg { width: 20px; height: 20px; color: #10b981; flex-shrink: 0; margin-top: 1px; }
.alert-success strong { display: block; color: var(--text); font-weight: 700; margin-bottom: 2px; }
.alert-success p { color: var(--text2); }
@keyframes fadeIn { from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:translateY(0)} }

/* Field */
.field-group { margin-bottom: 20px; display: flex; flex-direction: column; gap: 6px; }
.field-label { font-size: 13px; font-weight: 600; color: var(--text2); transition: color 0.2s; }
.field-group.focused .field-label { color: var(--teal); }

.field-wrap {
    display: flex; align-items: center;
    border: 1.5px solid var(--border);
    border-radius: 12px;
    background: var(--surface);
    transition: border-color 0.2s, box-shadow 0.2s, background 0.3s;
    overflow: hidden;
}
.field-group.focused .field-wrap { border-color: var(--teal); box-shadow: 0 0 0 3px var(--teal-ring); }
.field-group.valid .field-wrap { border-color: var(--success); }
.field-group.error .field-wrap { border-color: var(--error); box-shadow: 0 0 0 3px rgba(239,68,68,0.15); }

.field-icon { width: 44px; display: flex; align-items: center; justify-content: center; color: var(--text3); transition: color 0.2s; }
.field-group.focused .field-icon { color: var(--teal); }
.field-icon svg { width: 18px; height: 18px; }

.field-input {
    flex: 1; padding: 13px 12px 13px 0;
    font-family: 'DM Sans', sans-serif; font-size: 14px;
    color: var(--text); background: transparent; border: none; outline: none;
}
.field-input::placeholder { color: var(--text3); }

.field-check { width: 36px; display: flex; align-items: center; justify-content: center; color: var(--success); animation: popIn 0.2s cubic-bezier(0.34,1.56,0.64,1); }
.field-check svg { width: 16px; height: 16px; }
@keyframes popIn { from{opacity:0;transform:scale(0.5)}to{opacity:1;transform:scale(1)} }

.field-error { display: flex; align-items: center; gap: 5px; font-size: 12px; color: var(--error); font-weight: 500; }
.field-error svg { width: 14px; height: 14px; }

/* Steps */
.steps {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 24px;
    padding: 18px;
    background: var(--surface2);
    border: 1px solid var(--border);
    border-radius: 14px;
    transition: background 0.3s, border-color 0.3s;
}
.step {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    font-size: 13px;
    color: var(--text2);
}
.step-num {
    width: 22px; height: 22px;
    border-radius: 50%;
    background: rgba(13,148,136,0.12);
    color: var(--teal);
    font-size: 11px; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; margin-top: 1px;
}

/* Submit */
.submit-btn {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #0d9488, #0f766e);
    color: white;
    border: none;
    border-radius: 12px;
    font-family: 'DM Sans', sans-serif;
    font-size: 15px; font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 4px 16px rgba(13,148,136,0.35);
    letter-spacing: -0.2px;
}
.submit-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(13,148,136,0.45); }
.submit-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-inner { display: flex; align-items: center; justify-content: center; gap: 8px; }
.btn-inner svg { width: 18px; height: 18px; }
.spinner { width: 18px; height: 18px; border: 2.5px solid rgba(255,255,255,0.35); border-top-color: #fff; border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to{ transform: rotate(360deg); } }

.footer-note { text-align: center; font-size: 13px; color: var(--text3); margin-top: 24px; }
.footer-note a { color: var(--teal); font-weight: 600; text-decoration: none; }
.footer-note a:hover { text-decoration: underline; }
</style>