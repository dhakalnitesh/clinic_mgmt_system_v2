<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    email: { type: String, required: true },
    token: { type: String, required: true },
});

const form = useForm({
    token:                 props.token,
    email:                 props.email,
    password:              '',
    password_confirmation: '',
});

const showPass    = ref(false);
const showConfirm = ref(false);
const passFocused = ref(false);
const confFocused = ref(false);

function submit() {
    form.post(route('password.store'));
}

const passStrength = computed(() => {
    const p = form.password;
    if (!p) return 0;
    let s = 0;
    if (p.length >= 8)          s++;
    if (/[A-Z]/.test(p))        s++;
    if (/[0-9]/.test(p))        s++;
    if (/[^A-Za-z0-9]/.test(p)) s++;
    return s;
});
const strengthLabel = computed(() => ['', 'Weak', 'Fair', 'Good', 'Strong'][passStrength.value]);
const strengthColor = computed(() => ['', '#ef4444', '#f59e0b', '#3b82f6', '#10b981'][passStrength.value]);
const passwordsMatch = computed(() =>
    form.password_confirmation.length > 0 &&
    form.password === form.password_confirmation
);

const requirements = computed(() => [
    { label: 'At least 8 characters',      met: form.password.length >= 8 },
    { label: 'One uppercase letter (A–Z)',  met: /[A-Z]/.test(form.password) },
    { label: 'One number (0–9)',            met: /[0-9]/.test(form.password) },
    { label: 'One special character (!@#)', met: /[^A-Za-z0-9]/.test(form.password) },
]);
</script>

<template>
    <Head title="Create New Password — ClinicOS" />

    <div class="auth-root">
        <div class="bg-orb orb-a"></div>
        <div class="bg-orb orb-b"></div>

        <div class="reset-card">
            <!-- Header -->
            <div class="card-header">
                <div class="logo-wrap">
                    <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="40" height="40" rx="11" fill="#0d9488"/>
                        <path d="M7 20h4l3-8 4 16 3-11 2 3h10" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h1 class="card-title">Create New Password</h1>
                <p class="card-sub">Choose a strong password for <strong>{{ email }}</strong></p>
            </div>

            <form @submit.prevent="submit" class="card-body" novalidate>

                <!-- New password -->
                <div class="field-group" :class="{ focused: passFocused, error: form.errors.password }">
                    <label for="password" class="field-label">New Password</label>
                    <div class="field-wrap">
                        <span class="field-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="3" y="11" width="18" height="11" rx="2"/>
                                <path stroke-linecap="round" d="M7 11V7a5 5 0 0110 0v4"/>
                            </svg>
                        </span>
                        <input
                            id="password"
                            v-model="form.password"
                            :type="showPass ? 'text' : 'password'"
                            autocomplete="new-password"
                            placeholder="Create a strong password"
                            class="field-input"
                            @focus="passFocused = true"
                            @blur="passFocused = false"
                            required
                        />
                        <button type="button" class="pass-toggle" @click="showPass = !showPass" :aria-label="showPass ? 'Hide' : 'Show'">
                            <svg v-if="!showPass" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Strength meter -->
                    <div v-if="form.password.length > 0" class="strength-wrap">
                        <div class="strength-bars">
                            <span class="sbar" :style="passStrength >= 1 ? `background:${strengthColor}` : ''"></span>
                            <span class="sbar" :style="passStrength >= 2 ? `background:${strengthColor}` : ''"></span>
                            <span class="sbar" :style="passStrength >= 3 ? `background:${strengthColor}` : ''"></span>
                            <span class="sbar" :style="passStrength >= 4 ? `background:${strengthColor}` : ''"></span>
                        </div>
                        <span class="strength-label" :style="`color:${strengthColor}`">{{ strengthLabel }}</span>
                    </div>

                    <p v-if="form.errors.password" class="field-error">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        {{ form.errors.password }}
                    </p>
                </div>

                <!-- Requirements checklist -->
                <div v-if="form.password.length > 0" class="requirements">
                    <div
                        v-for="req in requirements"
                        :key="req.label"
                        class="req-item"
                        :class="{ met: req.met }"
                    >
                        <svg v-if="req.met" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <svg v-else viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        {{ req.label }}
                    </div>
                </div>

                <!-- Confirm password -->
                <div class="field-group" :class="{ focused: confFocused, error: form.errors.password_confirmation }">
                    <label for="password_confirmation" class="field-label">Confirm New Password</label>
                    <div class="field-wrap">
                        <span class="field-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </span>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            :type="showConfirm ? 'text' : 'password'"
                            autocomplete="new-password"
                            placeholder="Re-enter your password"
                            class="field-input"
                            @focus="confFocused = true"
                            @blur="confFocused = false"
                            required
                        />
                        <span v-if="passwordsMatch" class="field-check">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        </span>
                        <button type="button" class="pass-toggle" @click="showConfirm = !showConfirm" :aria-label="showConfirm ? 'Hide' : 'Show'">
                            <svg v-if="!showConfirm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    <p v-if="form.errors.password_confirmation" class="field-error">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        {{ form.errors.password_confirmation }}
                    </p>
                </div>

                <button
                    type="submit"
                    class="submit-btn"
                    :disabled="form.processing || passStrength < 2"
                >
                    <span v-if="!form.processing" class="btn-inner">
                        Reset Password & Sign In
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </span>
                    <span v-else class="btn-inner"><span class="spinner"></span>Resetting…</span>
                </button>

            </form>

            <div class="card-footer">
                <Link :href="route('login')" class="back-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m7-7l-7 7 7 7"/></svg>
                    Back to Sign In
                </Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap');

:root {
    --teal:#0d9488;--teal-dark:#0f766e;--teal-ring:rgba(13,148,136,0.25);
    --bg:#f0f4f8;--surface:#ffffff;--surface2:#f8fafc;
    --border:#e2e8f0;--text:#0f172a;--text2:#475569;--text3:#94a3b8;
    --error:#ef4444;--success:#10b981;
}
.dark-mode{
    --bg:#0b0f1a;--surface:#141c2b;--surface2:#1a2439;
    --border:#1e2d45;--text:#f1f5f9;--text2:#94a3b8;--text3:#4a5568;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}

.auth-root{
    min-height:100vh;background:var(--bg);display:flex;align-items:center;justify-content:center;
    font-family:'DM Sans',sans-serif;color:var(--text);padding:24px;position:relative;overflow:hidden;
    transition:background 0.3s;
}
.bg-orb{position:fixed;border-radius:50%;filter:blur(80px);pointer-events:none;z-index:0;}
.orb-a{width:500px;height:500px;top:-150px;right:-100px;background:rgba(13,148,136,0.08);}
.orb-b{width:350px;height:350px;bottom:-100px;left:-80px;background:rgba(124,58,237,0.05);}

.reset-card{
    position:relative;z-index:1;width:100%;max-width:480px;
    background:var(--surface);border:1px solid var(--border);border-radius:24px;
    box-shadow:0 20px 60px rgba(0,0,0,0.1),0 4px 16px rgba(0,0,0,0.06);
    overflow:hidden;animation:slideUp 0.45s cubic-bezier(0.22,1,0.36,1) both;
    transition:background 0.3s,border-color 0.3s;
}
.dark-mode .reset-card{box-shadow:0 20px 60px rgba(0,0,0,0.5);}
@keyframes slideUp{from{opacity:0;transform:translateY(28px)}to{opacity:1;transform:translateY(0)}}

.card-header{padding:36px 40px 28px;text-align:center;border-bottom:1px solid var(--border);background:var(--surface2);transition:background 0.3s,border-color 0.3s;}
.logo-wrap{display:flex;justify-content:center;margin-bottom:16px;}
.logo-wrap svg{width:48px;height:48px;border-radius:13px;}
.card-title{font-size:22px;font-weight:800;color:var(--text);letter-spacing:-0.6px;margin-bottom:6px;}
.card-sub{font-size:13px;color:var(--text3);line-height:1.5;}
.card-sub strong{color:var(--text2);}

.card-body{padding:28px 40px;display:flex;flex-direction:column;gap:18px;}

.field-group{display:flex;flex-direction:column;gap:6px;}
.field-label{font-size:13px;font-weight:600;color:var(--text2);transition:color 0.2s;}
.field-group.focused .field-label{color:var(--teal);}

.field-wrap{
    display:flex;align-items:center;border:1.5px solid var(--border);border-radius:12px;
    background:var(--surface);transition:all 0.2s;overflow:hidden;
}
.field-group.focused .field-wrap{border-color:var(--teal);box-shadow:0 0 0 3px var(--teal-ring);}
.field-group.error .field-wrap{border-color:var(--error);box-shadow:0 0 0 3px rgba(239,68,68,0.15);}

.field-icon{width:44px;display:flex;align-items:center;justify-content:center;color:var(--text3);transition:color 0.2s;}
.field-group.focused .field-icon{color:var(--teal);}
.field-icon svg{width:18px;height:18px;}

.field-input{flex:1;padding:13px 4px 13px 0;font-family:'DM Sans',sans-serif;font-size:14px;color:var(--text);background:transparent;border:none;outline:none;}
.field-input::placeholder{color:var(--text3);}

.field-check{width:32px;display:flex;align-items:center;justify-content:center;color:var(--success);animation:popIn 0.2s cubic-bezier(0.34,1.56,0.64,1);}
.field-check svg{width:16px;height:16px;}
@keyframes popIn{from{opacity:0;transform:scale(0.5)}to{opacity:1;transform:scale(1)}}

.pass-toggle{width:40px;height:100%;display:flex;align-items:center;justify-content:center;background:none;border:none;cursor:pointer;color:var(--text3);transition:color 0.2s;padding:0;}
.pass-toggle:hover{color:var(--teal);}
.pass-toggle svg{width:18px;height:18px;}

.field-error{display:flex;align-items:center;gap:5px;font-size:12px;color:var(--error);font-weight:500;}
.field-error svg{width:14px;height:14px;}

.strength-wrap{display:flex;align-items:center;gap:8px;}
.strength-bars{display:flex;gap:4px;flex:1;}
.sbar{height:3px;flex:1;border-radius:100px;background:var(--border);transition:background 0.3s;}
.strength-label{font-size:11px;font-weight:600;min-width:36px;}

/* Requirements checklist */
.requirements{
    display:grid;grid-template-columns:1fr 1fr;gap:6px;
    padding:14px;background:var(--surface2);border:1px solid var(--border);border-radius:12px;
    transition:background 0.3s,border-color 0.3s;
}
.req-item{display:flex;align-items:center;gap:6px;font-size:12px;color:var(--text3);transition:color 0.2s;}
.req-item svg{width:14px;height:14px;flex-shrink:0;color:var(--border);}
.req-item.met{color:var(--success);}
.req-item.met svg{color:var(--success);}

.submit-btn{
    width:100%;padding:14px;background:linear-gradient(135deg,#0d9488,#0f766e);
    color:white;border:none;border-radius:12px;font-family:'DM Sans',sans-serif;
    font-size:15px;font-weight:700;cursor:pointer;transition:all 0.2s;
    box-shadow:0 4px 16px rgba(13,148,136,0.35);letter-spacing:-0.2px;margin-top:4px;
}
.submit-btn:hover:not(:disabled){transform:translateY(-2px);box-shadow:0 8px 24px rgba(13,148,136,0.45);}
.submit-btn:disabled{opacity:0.6;cursor:not-allowed;}
.btn-inner{display:flex;align-items:center;justify-content:center;gap:8px;}
.btn-inner svg{width:18px;height:18px;}
.spinner{width:18px;height:18px;border:2.5px solid rgba(255,255,255,0.35);border-top-color:#fff;border-radius:50%;animation:spin 0.7s linear infinite;}
@keyframes spin{to{transform:rotate(360deg)}}

.card-footer{padding:18px 40px 24px;border-top:1px solid var(--border);display:flex;justify-content:center;transition:border-color 0.3s;}
.back-link{display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:var(--text3);text-decoration:none;transition:color 0.2s;}
.back-link:hover{color:var(--teal);}
.back-link svg{width:15px;height:15px;}

@media(max-width:520px){
    .card-header,.card-body,.card-footer{padding-left:24px;padding-right:24px;}
    .requirements{grid-template-columns:1fr;}
}
</style>