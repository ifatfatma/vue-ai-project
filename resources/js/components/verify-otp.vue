
<script setup>
import { ref } from 'vue';

const props = defineProps({
    verifyUrl: { type: String, required: true },
    loginUrl: { type: String, required: true },
    csrfToken: { type: String, required: true },
    initialSuccess: { type: String, default: '' },
    initialError: { type: String, default: '' },
    initialOtp: { type: String, default: '' },
    validationErrors: { type: Object, default: () => ({}) },
});

const otp = ref(props.initialOtp);
const loading = ref(false);
const errorMessage = ref(props.initialError);
const successMessage = ref(props.initialSuccess);
const otpError = ref(props.validationErrors.otp?.[0] || '');

function verifyOtp() {
    errorMessage.value = '';
    otpError.value = '';

    const code = otp.value.trim();

    if (!/^\d{6}$/.test(code)) {
        otpError.value = 'Please enter a valid 6-digit OTP.';
        return;
    }

    loading.value = true;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = props.verifyUrl;

    const fields = {
        _token: props.csrfToken,
        otp: code,
    };

    Object.entries(fields).forEach(([name, value]) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
}
</script>

<template>
    <div class="background-dashboard-wrapper d-flex flex-column min-vh-100 m-0">
        <!-- Background dashboard -->
        <div class="real-dashboard-content flex-grow-1">
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 shadow-sm">
                <div class="container-fluid">
                    <a class="navbar-brand fw-bold" href="/">AI Prompt Hub</a>

                    <div class="collapse navbar-collapse show">
                        <ul class="navbar-nav ms-auto align-items-center flex-row gap-3">
                            <li class="nav-item">
                                <button type="button" class="btn btn-primary btn-sm fw-bold px-3 py-2">
                                    + Add Prompt
                                </button>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white fw-semibold px-0" href="#">
                                    <i class="bi bi-person-circle fs-5 me-1 text-primary"></i>
                                    My Account
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <section class="hero-section text-center mb-5">
                <div class="container">
                    <h1 class="fw-bold display-5 mb-3">Find &amp; Copy Premium AI Prompts</h1>
                    <p class="lead mb-4">
                        Explore curated prompts for ChatGPT, Midjourney, and LLMs.
                    </p>

                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="row g-2">
                                <div class="col-md-9">
                                    <input
                                        class="form-control form-control-lg shadow-sm"
                                        placeholder="Search prompts..."
                                        disabled
                                    >
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-hero-search btn-lg w-100 fw-bold" disabled>
                                        Search
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="container mb-5">
                <div class="row">
                    <div
                        v-for="item in [
                            {
                                category: 'Content & Blog Writing',
                                title: 'SEO Optimized Blog Post Outline Generator',
                                text: 'You are an expert content writer and SEO specialist...'
                            },
                            {
                                category: 'Social Media & Copywriting',
                                title: 'High-Converting Instagram Caption Generator',
                                text: 'You are a professional social media copywriter...'
                            },
                            {
                                category: 'Coding & Software Development',
                                title: 'Clean Code Bug Fixer & Refactor Assistant',
                                text: 'You are a senior software developer...'
                            }
                        ]"
                        :key="item.title"
                        class="col-md-4 mb-4"
                    >
                        <div class="card h-100 prompt-card shadow-sm">
                            <div class="card-body d-flex flex-column">
                                <div class="mb-2">
                                    <span class="badge bg-primary">{{ item.category }}</span>
                                </div>
                                <h5 class="card-title fw-bold text-dark mb-2">{{ item.title }}</h5>
                                <div class="p-2 border rounded prompt-text-box mb-3 text-muted">
                                    {{ item.text }}
                                </div>
                                <div class="mt-auto">
                                    <button class="btn btn-success btn-sm w-100 fw-bold" disabled>
                                        Copy Prompt
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- OTP verification card -->
        <div class="login-overlay-container px-3">
            <div class="card shadow-lg p-4 rounded-4 border-0 bg-white">
                <div class="text-center mb-3">
                    <h3 class="fw-bold text-dark">Enter OTP</h3>
                    <p class="text-muted small">
                        Please enter the 6-digit code sent to your email.
                    </p>
                </div>

                <div v-if="successMessage" class="alert alert-success py-2 small">
                    {{ successMessage }}
                </div>

                <div v-if="errorMessage" class="alert alert-danger py-2 small">
                    {{ errorMessage }}
                </div>

                <form :action="verifyUrl" method="POST" @submit.prevent="verifyOtp">
                    <input type="hidden" name="_token" :value="csrfToken">

                    <div class="mb-3 text-center">
                        <input
                            v-model="otp"
                            type="text"
                            name="otp"
                            class="form-control text-center fs-4"
                            placeholder="------"
                            maxlength="6"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            pattern="[0-9]{6}"
                            required
                            style="letter-spacing: 5px;"
                        >

                        <small v-if="otpError" class="text-danger">
                            {{ otpError }}
                        </small>
                    </div>

                    <button
                        type="submit"
                        class="btn btn-success w-100 py-2 fw-bold rounded-pill shadow-sm"
                        :disabled="loading"
                    >
                        <span v-if="loading">
                            <span class="spinner-border spinner-border-sm me-2"></span>
                            Verifying...
                        </span>
                        <span v-else>Verify &amp; Login</span>
                    </button>
                </form>

                <div class="text-center mt-3">
                    <a :href="loginUrl" class="text-decoration-none small text-primary">
                        Resend OTP / Change Email
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.hero-section {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
    color: white;
    padding: 60px 0;
}

.btn-hero-search {
    background-color: #6366f1;
    color: #fff;
    border: 1px solid #4f46e5;
}

.prompt-card {
    border: 1px solid #e5e7eb;
}

.prompt-text-box {
    background: #f8fafc;
    font-family: monospace;
    font-size: 0.9rem;
    max-height: 120px;
    overflow-y: auto;
}

.background-dashboard-wrapper {
    position: relative;
    min-height: 100vh;
    overflow-x: hidden;
    background-color: #f8f9fa;
}

.real-dashboard-content {
    filter: blur(3px);
    -webkit-filter: blur(3px);
    opacity: 0.95;
    pointer-events: none;
    user-select: none;
}

.login-overlay-container {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1050;
    width: 100%;
    max-width: 420px;
}

@media (max-width: 480px) {
    .login-overlay-container {
        max-width: 100%;
    }
}
</style>
