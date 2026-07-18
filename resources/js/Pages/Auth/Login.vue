<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm, usePage } from '@inertiajs/inertia-vue3';
import { computed, ref } from 'vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const page = usePage();
const showPassword = ref(false);

const companyName = computed(
    () => page.props.company_name || 'شركة سلام جلال أيوب'
);
const systemLogo = computed(
    () => page.props.systemLogo || '/img/logo.png'
);
const loginBackground = computed(
    () => page.props.loginBackground || null
);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const onLogoError = (event) => {
    event.target.src = '/img/logo.jpg';
    event.target.onerror = null;
};
</script>

<template>
    <div class="login-page" :class="{ 'has-photo-bg': !!loginBackground }">
        <Head title="تسجيل الدخول">
            <link
                rel="stylesheet"
                href="https://fonts.bunny.net/css?family=ibm-plex-sans-arabic:400,500,600,700&display=swap"
            />
        </Head>

        <div class="login-atmosphere" aria-hidden="true">
            <div
                v-if="loginBackground"
                class="login-photo"
                :style="{ backgroundImage: `url('${loginBackground}')` }"
            />
            <div class="login-photo-veil" />
            <div class="login-glow login-glow--a" />
            <div class="login-glow login-glow--b" />
            <div class="login-grid" />
            <div class="login-route" />
        </div>

        <main class="login-shell">
            <section class="login-brand">
                <div class="login-brand__inner">
                    <img
                        :src="systemLogo"
                        alt=""
                        class="login-logo"
                        @error="onLogoError"
                    />
                    <p class="login-kicker">نظام إدارة الشحن</p>
                    <h1 class="login-title">{{ companyName }}</h1>
                    <p class="login-subtitle">
                        دخول آمن إلى لوحة العمليات، العقود، والمحاسبة
                    </p>
                </div>
            </section>

            <section class="login-panel">
                <div class="login-panel__inner">
                    <header class="login-panel__header">
                        <h2>تسجيل الدخول</h2>
                        <p>أدخل بياناتك للمتابعة</p>
                    </header>

                    <div v-if="status" class="login-status" role="status">
                        {{ status }}
                    </div>

                    <form class="login-form" @submit.prevent="submit">
                        <div class="login-field">
                            <label for="email">اسم المستخدم</label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="text"
                                class="login-input"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="اسم المستخدم"
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div class="login-field">
                            <label for="password">كلمة المرور</label>
                            <div class="login-input-wrap">
                                <input
                                    id="password"
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    class="login-input login-input--password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="••••••••"
                                />
                                <button
                                    type="button"
                                    class="login-toggle"
                                    :aria-label="showPassword ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور'"
                                    @click="showPassword = !showPassword"
                                >
                                    <svg v-if="!showPassword" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                    <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path d="M3 3l18 18" />
                                        <path d="M10.6 10.6a3 3 0 0 0 4.2 4.2" />
                                        <path d="M9.9 5.1A10.7 10.7 0 0 1 12 5c6.5 0 10 7 10 7a17.3 17.3 0 0 1-3.2 4.4" />
                                        <path d="M6.1 6.1A17.5 17.5 0 0 0 2 12s3.5 7 10 7a10.4 10.4 0 0 0 4.2-.9" />
                                    </svg>
                                </button>
                            </div>
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <label class="login-remember">
                            <Checkbox name="remember" v-model:checked="form.remember" />
                            <span>تذكرني</span>
                        </label>

                        <button
                            type="submit"
                            class="login-submit"
                            :disabled="form.processing"
                            :class="{ 'is-loading': form.processing }"
                        >
                            <span v-if="form.processing" class="login-spinner" aria-hidden="true" />
                            <span>{{ form.processing ? 'جارٍ الدخول...' : 'دخول' }}</span>
                        </button>
                    </form>

                    <footer class="login-foot">
                        وصول مخصص للموظفين المصرّح لهم فقط
                    </footer>
                </div>
            </section>
        </main>
    </div>
</template>

<style scoped>
.login-page {
    --bg: #0b1220;
    --bg-soft: #121a2b;
    --surface: rgba(255, 255, 255, 0.06);
    --surface-strong: rgba(255, 255, 255, 0.1);
    --border: rgba(255, 255, 255, 0.12);
    --text: #f8fafc;
    --muted: #94a3b8;
    --accent: #e11d48;
    --accent-hover: #be123c;
    --ring: rgba(225, 29, 72, 0.45);
    --font: 'IBM Plex Sans Arabic', 'Nunito', Tahoma, sans-serif;

    position: relative;
    min-height: 100vh;
    min-height: 100dvh;
    overflow: hidden;
    background: var(--bg);
    color: var(--text);
    font-family: var(--font);
}

.login-atmosphere {
    position: absolute;
    inset: 0;
    pointer-events: none;
}

.login-photo {
    position: absolute;
    inset: 0;
    background-position: center;
    background-size: cover;
    background-repeat: no-repeat;
    transform: scale(1.04);
    animation: bgDrift 18s ease-in-out infinite alternate;
}

.login-photo-veil {
    position: absolute;
    inset: 0;
    background:
        linear-gradient(120deg, rgba(11, 18, 32, 0.88) 0%, rgba(11, 18, 32, 0.62) 48%, rgba(11, 18, 32, 0.78) 100%),
        radial-gradient(ellipse at 70% 20%, rgba(225, 29, 72, 0.18), transparent 55%);
}

.login-page:not(.has-photo-bg) .login-photo-veil {
    background: transparent;
}

.login-page.has-photo-bg .login-glow {
    opacity: 0.28;
}

.login-page.has-photo-bg .login-grid {
    opacity: 0.18;
}

.login-glow {
    position: absolute;
    border-radius: 9999px;
    filter: blur(80px);
    opacity: 0.55;
    animation: drift 12s ease-in-out infinite alternate;
}

.login-glow--a {
    width: 42vw;
    height: 42vw;
    top: -12%;
    right: -8%;
    background: radial-gradient(circle, rgba(225, 29, 72, 0.35), transparent 70%);
}

.login-glow--b {
    width: 48vw;
    height: 48vw;
    bottom: -18%;
    left: -12%;
    background: radial-gradient(circle, rgba(14, 116, 144, 0.28), transparent 70%);
    animation-delay: -4s;
}

.login-grid {
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(148, 163, 184, 0.07) 1px, transparent 1px),
        linear-gradient(90deg, rgba(148, 163, 184, 0.07) 1px, transparent 1px);
    background-size: 48px 48px;
    mask-image: radial-gradient(ellipse at center, black 20%, transparent 75%);
    opacity: 0.45;
}

.login-route {
    position: absolute;
    inset: 18% 8% auto;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(248, 250, 252, 0.35), transparent);
    transform: rotate(-8deg);
    animation: routePulse 4.5s ease-in-out infinite;
}

.login-shell {
    position: relative;
    z-index: 1;
    display: grid;
    min-height: 100vh;
    min-height: 100dvh;
}

.login-brand,
.login-panel {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1.25rem;
}

.login-brand__inner {
    width: min(100%, 34rem);
    text-align: center;
    animation: riseIn 700ms cubic-bezier(0.22, 1, 0.36, 1) both;
}

.login-logo {
    width: auto;
    height: 4.5rem;
    max-width: 11rem;
    object-fit: contain;
    margin: 0 auto 1.25rem;
    filter: drop-shadow(0 10px 24px rgba(0, 0, 0, 0.35));
}

.login-kicker {
    margin: 0 0 0.5rem;
    color: var(--muted);
    font-size: 0.85rem;
    font-weight: 500;
    letter-spacing: 0.04em;
}

.login-title {
    margin: 0;
    font-size: clamp(1.75rem, 4vw, 2.5rem);
    font-weight: 700;
    line-height: 1.25;
    letter-spacing: -0.02em;
}

.login-subtitle {
    margin: 0.85rem auto 0;
    max-width: 28rem;
    color: var(--muted);
    font-size: 0.98rem;
    line-height: 1.7;
}

.login-panel__inner {
    width: min(100%, 26rem);
    padding: 1.75rem;
    border: 1px solid var(--border);
    border-radius: 1rem;
    background: linear-gradient(180deg, var(--surface-strong), var(--surface));
    backdrop-filter: blur(16px);
    box-shadow: 0 24px 60px rgba(0, 0, 0, 0.35);
    animation: riseIn 800ms cubic-bezier(0.22, 1, 0.36, 1) 120ms both;
}

.login-panel__header {
    margin-bottom: 1.5rem;
}

.login-panel__header h2 {
    margin: 0;
    font-size: 1.35rem;
    font-weight: 700;
}

.login-panel__header p {
    margin: 0.35rem 0 0;
    color: var(--muted);
    font-size: 0.9rem;
}

.login-status {
    margin-bottom: 1rem;
    padding: 0.75rem 0.9rem;
    border-radius: 0.65rem;
    background: rgba(16, 185, 129, 0.15);
    color: #6ee7b7;
    font-size: 0.875rem;
}

.login-form {
    display: grid;
    gap: 1rem;
}

.login-field label {
    display: block;
    margin-bottom: 0.4rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #e2e8f0;
}

.login-input {
    width: 100%;
    min-height: 48px;
    padding: 0.7rem 0.9rem;
    border: 1px solid var(--border);
    border-radius: 0.7rem;
    background: rgba(15, 23, 42, 0.55);
    color: var(--text);
    font-family: inherit;
    font-size: 1rem;
    transition: border-color 180ms ease, box-shadow 180ms ease, background 180ms ease;
}

.login-input::placeholder {
    color: #64748b;
}

.login-input:focus {
    outline: none;
    border-color: rgba(225, 29, 72, 0.7);
    box-shadow: 0 0 0 3px var(--ring);
    background: rgba(15, 23, 42, 0.75);
}

.login-input-wrap {
    position: relative;
}

.login-input--password {
    padding-left: 3rem;
}

.login-toggle {
    position: absolute;
    top: 50%;
    left: 0.55rem;
    transform: translateY(-50%);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border: 0;
    border-radius: 0.55rem;
    background: transparent;
    color: var(--muted);
    cursor: pointer;
    transition: color 160ms ease, background 160ms ease;
}

.login-toggle:hover,
.login-toggle:focus-visible {
    color: var(--text);
    background: rgba(255, 255, 255, 0.06);
    outline: none;
}

.login-toggle svg {
    width: 1.15rem;
    height: 1.15rem;
}

.login-remember {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    color: var(--muted);
    font-size: 0.9rem;
    user-select: none;
}

.login-submit {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.55rem;
    min-height: 48px;
    margin-top: 0.25rem;
    border: 0;
    border-radius: 0.75rem;
    background: var(--accent);
    color: #fff;
    font-family: inherit;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 180ms ease, transform 160ms ease, opacity 160ms ease;
}

.login-submit:hover:not(:disabled) {
    background: var(--accent-hover);
}

.login-submit:active:not(:disabled) {
    transform: scale(0.98);
}

.login-submit:disabled {
    opacity: 0.7;
    cursor: wait;
}

.login-spinner {
    width: 1rem;
    height: 1rem;
    border: 2px solid rgba(255, 255, 255, 0.35);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}

.login-foot {
    margin-top: 1.25rem;
    color: #64748b;
    font-size: 0.78rem;
    text-align: center;
    line-height: 1.6;
}

@keyframes riseIn {
    from {
        opacity: 0;
        transform: translateY(18px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes drift {
    from {
        transform: translate3d(0, 0, 0) scale(1);
    }
    to {
        transform: translate3d(2%, -3%, 0) scale(1.08);
    }
}

@keyframes bgDrift {
    from {
        transform: scale(1.04) translate3d(0, 0, 0);
    }
    to {
        transform: scale(1.08) translate3d(-1.5%, 1%, 0);
    }
}

@keyframes routePulse {
    0%,
    100% {
        opacity: 0.25;
    }
    50% {
        opacity: 0.7;
    }
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

@media (min-width: 960px) {
    .login-shell {
        grid-template-columns: 1.15fr 0.85fr;
    }

    .login-brand {
        justify-content: flex-end;
        padding-inline-end: 4vw;
        text-align: right;
    }

    .login-brand__inner {
        text-align: right;
    }

    .login-logo {
        margin-inline: 0;
    }

    .login-subtitle {
        margin-inline: 0;
    }

    .login-panel {
        justify-content: flex-start;
        padding-inline-start: 3vw;
        background: linear-gradient(180deg, rgba(18, 26, 43, 0.55), rgba(11, 18, 32, 0.2));
        border-inline-start: 1px solid rgba(255, 255, 255, 0.06);
    }
}

@media (max-width: 959px) {
    .login-brand {
        padding-bottom: 0.5rem;
        align-items: flex-end;
    }

    .login-panel {
        align-items: flex-start;
        padding-top: 1rem;
    }

    .login-logo {
        height: 3.75rem;
    }
}

@media (prefers-reduced-motion: reduce) {
    .login-brand__inner,
    .login-panel__inner,
    .login-glow,
    .login-route,
    .login-photo,
    .login-spinner {
        animation: none !important;
    }
}
</style>
