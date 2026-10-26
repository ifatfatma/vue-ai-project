
<script setup>
import { ref } from 'vue';

const props = defineProps({
    sendOtpUrl: { type: String, required: true },
    csrfToken: { type: String, required: true },
    initialEmail: { type: String, default: '' },
    initialError: { type: String, default: '' },
    initialSuccess: { type: String, default: '' },
    validationErrors: { type: Object, default: () => ({}) },
});

const email = ref(props.initialEmail);
const loading = ref(false);
const errorMessage = ref(props.initialError);
const successMessage = ref(props.initialSuccess);

const emailError = ref(props.validationErrors.email?.[0] || '');

async function sendOtp() {
    errorMessage.value = '';
    emailError.value = '';

    const cleanEmail = email.value.trim();

    if (!cleanEmail) {
        emailError.value = 'Please enter your email address.';
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = props.sendOtpUrl;

    const fields = {
        _token: props.csrfToken,
        email: cleanEmail,
    };

    Object.entries(fields).forEach(([name, value]) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.appendChild(input);
    });

    loading.value = true;
    document.body.appendChild(form);
    form.submit();
}
</script>

<template>
    <div class="background-dashboard-wrapper d-flex flex-column min-vh-100 m-0">
        <div class="real-dashboard-content flex-grow-1">
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 shadow-sm">
                <div class="container-fluid">
                    <a class="navbar-brand d-flex align-items-center" href="/">
                        <span class="fw-bold">AI Prompt Hub</span>
                    </a>

                    <div class="collapse navbar-collapse show">
                        <ul class="navbar-nav ms-auto align-items-center flex-row gap-3">
                            <li class="nav-item">
                                <button type="button" class="btn btn-primary btn-sm fw-bold px-3 py-2">
                                    + Add Prompt
                                </button>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center text-white fw-semibold px-0" href="#">
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
                            <div class="row g-2 justify-content-center">
                                <div class="col-md-9">
                                    <input
                                        type="text"
                                        class="form-control form-control-lg shadow-sm"
                                        placeholder="Search prompts (e.g. SEO, Email, Marketing)..."
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
                    <div class="col-md-4 mb-4" v-for="item in [
                        {
                            category: 'Content & Blog Writing',
                            title: 'SEO Optimized Blog Post Outline Generator',
                            text: 'You are an expert content writer and SEO specialist. Create a comprehensive and SEO-optimized blog post outline for the given topic: [Insert Topic Name here]. Include catchy H1, H2s, H3s, target keywords, and a brief description for each section.'
                        },
                        {
                            category: 'Social Media & Copywriting',
                            title: 'High-Converting Instagram Caption Generator',
                            text: 'You are a professional social media copywriter. Write an engaging Instagram caption for my product/service: [Insert Product Details here]. The caption should include a strong hook, value proposition, a clear Call-to-Action (CTA), and relevant trending hashtags.'
                        },
                        {
                            category: 'Coding & Software Development',
                            title: 'Clean Code Bug Fixer & Refactor Assistant',
                            text: 'You are a senior software developer. Review the code snippet provided below, identify and fix any bugs, and refactor the code for better performance. Also, explain the changes made clearly. Code: [Paste your code here]'
                        }
                    ]" :key="item.title">
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

        <div class="login-overlay-container px-3">
            <div class="card shadow-lg p-4 rounded-4 border-0 bg-white">
                <h3 class="fw-bold text-center mb-2 text-dark">Login with Email OTP</h3>
                <p class="text-muted small text-center mb-4">
                    Enter your registered email to receive a verification code.
                </p>

                <div v-if="errorMessage" class="alert alert-danger py-2 small">
                    {{ errorMessage }}
                </div>

                <div v-if="successMessage" class="alert alert-success py-2 small">
                    {{ successMessage }}
                </div>

                <form :action="sendOtpUrl" method="POST" @submit.prevent="sendOtp">
                    <input type="hidden" name="_token" :value="csrfToken">

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark">Email Address</label>
                        <input
                            v-model="email"
                            type="email"
                            name="email"
                            class="form-control"
                            required
                            autocomplete="email"
                            placeholder="name@example.com"
                        >
                        <small v-if="emailError" class="text-danger">{{ emailError }}</small>
                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary w-100 fw-bold py-2 rounded-pill shadow-sm"
                        :disabled="loading"
                    >
                        <span v-if="loading">
                            <span class="spinner-border spinner-border-sm me-2"></span>
                            Sending OTP...
                        </span>
                        <span v-else>Send OTP</span>
                    </button>
                </form>
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
    white-space: pre-wrap;
}

.background-dashboard-wrapper {
    position: relative;
    min-height: 100vh;
    overflow-x: hidden;
    background-color: #f8f9fa;
}

.real-dashboard-content {
    filter: blur(2px);
    -webkit-filter: blur(2px);
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
</style>
