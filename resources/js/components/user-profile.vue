
<script setup>
import { computed, onBeforeUnmount, ref } from 'vue';

const props = defineProps({
    initialUser: {
        type: Object,
        required: true,
    },
    updateUrl: {
        type: String,
        required: true,
    },
    homeUrl: {
        type: String,
        required: true,
    },
    csrfToken: {
        type: String,
        required: true,
    },
    successMessage: {
        type: String,
        default: '',
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const name = ref(props.initialUser.name || '');
const email = ref(props.initialUser.email || '');
const profilePreview = ref(
    props.initialUser.profileImage ||
    'https://placehold.co/120x120/1a1528/ffffff?text=Profile'
);

const imageInput = ref(null);
const selectedImage = ref(null);
const imageError = ref('');
let previewUrl = null;

const allErrors = computed(() => Object.values(props.errors).flat());

function handleImageChange(event) {
    const file = event.target.files?.[0];

    imageError.value = '';

    if (!file) {
        return;
    }

    const allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
    ];

    if (!allowedTypes.includes(file.type)) {
        imageError.value = 'Please select a JPG, JPEG, PNG or GIF image.';
        event.target.value = '';
        selectedImage.value = null;
        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        imageError.value = 'Image size must not exceed 2 MB.';
        event.target.value = '';
        selectedImage.value = null;
        return;
    }

    if (previewUrl) {
        URL.revokeObjectURL(previewUrl);
    }

    selectedImage.value = file;
    previewUrl = URL.createObjectURL(file);
    profilePreview.value = previewUrl;
}

onBeforeUnmount(() => {
    if (previewUrl) {
        URL.revokeObjectURL(previewUrl);
    }
});
</script>

<template>
    <div class="profile-page">

        <!-- Top Navigation -->
        <header class="profile-topbar">
            <div class="profile-topbar-inner">

                <a :href="homeUrl" class="profile-brand">
                    AI Prompt Hub
                </a>

                <a :href="homeUrl" class="profile-back-home">
                    <i class="bi bi-arrow-left"></i>
                    Back to Home
                </a>

            </div>
        </header>

        <!-- Hero Section -->
        <section class="profile-hero">
            <div class="profile-hero-content">
                <h1>My Profile</h1>
                <p>Manage your personal information and profile photo.</p>
            </div>
        </section>

        <!-- Profile Content -->
        <main class="profile-content">

            <div class="profile-card">

                <!-- Card Heading -->
                <div class="profile-card-heading">

                    <div class="profile-heading-icon">
                        <i class="bi bi-person-fill"></i>
                    </div>

                    <div>
                        <h2>Profile Information</h2>
                        <p>Update your account details below.</p>
                    </div>

                </div>

                <!-- Success Message -->
                <div
                    v-if="successMessage"
                    class="alert alert-success alert-dismissible fade show"
                    role="alert"
                >
                    {{ successMessage }}

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Close"
                    ></button>
                </div>

                <!-- Validation Errors -->
                <div
                    v-if="allErrors.length"
                    class="alert alert-danger alert-dismissible fade show"
                    role="alert"
                >
                    <strong>Please correct the following errors:</strong>

                    <ul class="mb-0 mt-2">
                        <li
                            v-for="(error, index) in allErrors"
                            :key="index"
                        >
                            {{ error }}
                        </li>
                    </ul>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Close"
                    ></button>
                </div>

                <form
                    :action="updateUrl"
                    method="POST"
                    enctype="multipart/form-data"
                >

                    <input
                        type="hidden"
                        name="_token"
                        :value="csrfToken"
                    >

                    <input
                        type="hidden"
                        name="_method"
                        value="PATCH"
                    >

                    <div class="profile-form-grid">

                        <!-- Profile Image -->
                        <section class="profile-photo-section">

                            <div class="profile-photo-frame">
                                <img
                                    :src="profilePreview"
                                    alt="Profile Image Preview"
                                    class="profile-image"
                                >
                            </div>

                            <div class="profile-photo-details">

                                <h3>Profile Photo</h3>

                                <p>
                                    Choose a clear photo for your account.
                                </p>

                                <label
                                    for="profile_image"
                                    class="form-label"
                                >
                                    Upload profile image
                                </label>

                                <input
                                    ref="imageInput"
                                    type="file"
                                    id="profile_image"
                                    name="profile_image"
                                    class="form-control"
                                    accept=".jpg,.jpeg,.png,.gif,image/jpeg,image/png,image/gif"
                                    @change="handleImageChange"
                                >

                                <small class="image-hint">
                                    JPG, JPEG, PNG or GIF.
                                    Maximum size: 2 MB.
                                </small>

                                <small
                                    v-if="imageError"
                                    class="field-error"
                                >
                                    {{ imageError }}
                                </small>

                                <small
                                    v-if="errors.profile_image?.[0]"
                                    class="field-error"
                                >
                                    {{ errors.profile_image[0] }}
                                </small>

                            </div>
                        </section>

                        <div class="profile-divider"></div>

                        <!-- Full Name -->
                        <div class="profile-field">

                            <label for="name" class="form-label">
                                Full Name
                            </label>

                            <input
                                type="text"
                                id="name"
                                name="name"
                                v-model="name"
                                maxlength="255"
                                required
                                class="form-control"
                                :class="{
                                    'is-invalid': errors.name?.length
                                }"
                                placeholder="Enter your full name"
                            >

                            <div
                                v-if="errors.name?.[0]"
                                class="invalid-feedback"
                            >
                                {{ errors.name[0] }}
                            </div>

                        </div>

                        <!-- Email -->
                        <div class="profile-field">

                            <label for="email" class="form-label">
                                Email Address
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                v-model="email"
                                maxlength="255"
                                required
                                class="form-control"
                                :class="{
                                    'is-invalid': errors.email?.length
                                }"
                                placeholder="Enter your email address"
                            >

                            <div
                                v-if="errors.email?.[0]"
                                class="invalid-feedback"
                            >
                                {{ errors.email[0] }}
                            </div>

                        </div>

                    </div>

                    <!-- Update Button -->
                    <div class="profile-form-actions">

                        <button
                            type="submit"
                            class="update-btn"
                        >
                            <i class="bi bi-check2-circle"></i>
                            Update Profile
                        </button>

                    </div>

                </form>

            </div>

        </main>

    </div>
</template>

<style scoped>

/* ==========================================
   PROFILE PAGE - AI PROMPT HUB DESIGN
========================================== */

.profile-page {
    min-height: 100vh;
    background: #f5f6fa;
    color: #1f2937;
    padding: 0;
}

/* Top Navigation */
.profile-topbar {
    width: 100%;
    min-height: 72px;
    background: #212529;
    display: flex;
    align-items: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, .12);
}

.profile-topbar-inner {
    width: 100%;
    min-height: 72px;
    padding: 0 2.3%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.profile-brand {
    color: #fff;
    text-decoration: none;
    font-size: 1.35rem;
    font-weight: 800;
    letter-spacing: -.3px;
    line-height: 1.2;
}

.profile-brand:hover,
.profile-brand:focus {
    color: #f8f9fa;
    text-decoration: none;
}

.profile-back-home {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 7px 13px;
    border: 1px solid rgba(255, 255, 255, .8);
    border-radius: 5px;
    color: #fff;
    background: transparent;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
    transition: background .2s ease, color .2s ease;
}

.profile-back-home:hover {
    background: #fff;
    color: #212529;
}

/* Hero Section */
.profile-hero {
    min-height: 235px;
    padding: 42px 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;

    background:
        radial-gradient(
            circle at 100% 0%,
            rgba(124, 58, 237, .34),
            transparent 34%
        ),
        radial-gradient(
            circle at 0% 100%,
            rgba(59, 130, 246, .12),
            transparent 28%
        ),
        linear-gradient(
            110deg,
            #111827 0%,
            #21153f 52%,
            #422078 100%
        );
}

.profile-hero-content h1 {
    margin: 0 0 10px;
    color: #fff;
    font-size: clamp(30px, 4vw, 42px);
    font-weight: 700;
    letter-spacing: -.8px;
}

.profile-hero-content p {
    margin: 0;
    color: #d1c6ed;
    font-size: 16px;
}

/* Main Content */
.profile-content {
    width: min(100% - 48px, 1050px);
    margin: 32px auto 60px;
}

/* Profile Card */
.profile-card {
    padding: 32px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 18px;
    box-shadow: 0 4px 16px rgba(15, 23, 42, .07);
}

/* Card Heading */
.profile-card-heading {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 28px;
    padding-bottom: 22px;
    border-bottom: 1px solid #edf0f5;
}

.profile-heading-icon {
    width: 48px;
    height: 48px;
    min-width: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 13px;
    color: #fff;
    background: linear-gradient(135deg, #6366f1, #7c3aed);
    font-size: 21px;
}

.profile-card-heading h2 {
    margin: 0 0 4px;
    color: #111827;
    font-size: 21px;
    font-weight: 700;
}

.profile-card-heading p {
    margin: 0;
    color: #64748b;
    font-size: 14px;
}

/* Alerts */
.profile-card .alert {
    border-radius: 10px;
    margin-bottom: 22px;
}

/* Profile Photo */
.profile-photo-section {
    display: flex;
    align-items: center;
    gap: 25px;
    padding: 4px 0 26px;
}

.profile-photo-frame {
    width: 116px;
    height: 116px;
    min-width: 116px;
    padding: 4px;
    border: 2px solid #ddd6fe;
    border-radius: 50%;
    background: #f5f3ff;
}

.profile-image {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    background: #f3f4f6;
}

.profile-photo-details {
    flex: 1;
    min-width: 0;
}

.profile-photo-details h3 {
    margin: 0 0 5px;
    color: #111827;
    font-size: 17px;
    font-weight: 700;
}

.profile-photo-details p {
    margin: 0 0 14px;
    color: #64748b;
    font-size: 13px;
}

.profile-photo-details .form-label,
.profile-field .form-label {
    display: block;
    margin-bottom: 8px;
    color: #374151;
    font-size: 14px;
    font-weight: 600;
}

.image-hint {
    display: block;
    margin-top: 8px;
    color: #64748b;
    font-size: 12px;
}

.field-error {
    display: block;
    margin-top: 7px;
    color: #dc2626;
    font-size: 13px;
}

/* Divider */
.profile-divider {
    height: 1px;
    margin-bottom: 25px;
    background: #edf0f5;
}

/* Form Fields */
.profile-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 22px;
}

.profile-field .form-control,
.profile-photo-details .form-control {
    min-height: 46px;
    padding: 10px 13px;
    color: #1f2937;
    background: #fff;
    border: 1px solid #dbe1ea;
    border-radius: 8px;
    font-size: 14px;
    box-shadow: none;
}

.profile-photo-details input[type="file"].form-control {
    min-height: auto;
    padding: 7px;
}

.profile-field .form-control::placeholder {
    color: #9ca3af;
}

.profile-field .form-control:focus,
.profile-photo-details .form-control:focus {
    color: #1f2937;
    background: #fff;
    border-color: #7c3aed;
    box-shadow: 0 0 0 3px rgba(124, 58, 237, .12);
}

/* File Input */
.profile-photo-details .form-control::file-selector-button {
    margin: -7px 12px -7px -7px;
    padding: 10px 13px;
    color: #5b35c9;
    background: #f3f0ff;
    border: 0;
    border-right: 1px solid #e5e7eb;
    cursor: pointer;
}

/* Submit Button */
.profile-form-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 28px;
    padding-top: 22px;
    border-top: 1px solid #edf0f5;
}

.update-btn {
    min-height: 46px;
    padding: 11px 25px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: #fff;
    background: linear-gradient(110deg, #5145ee, #7c2ae8);
    border: 0;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    box-shadow: 0 5px 14px rgba(99, 70, 220, .18);
    transition: transform .2s ease, box-shadow .2s ease;
}

.update-btn:hover {
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 8px 18px rgba(99, 70, 220, .25);
}

/* Responsive */
@media (max-width: 767px) {

    .profile-topbar,
    .profile-topbar-inner {
        min-height: 65px;
    }

    .profile-topbar-inner {
        padding: 0 15px;
    }

    .profile-brand {
        font-size: 1.1rem;
    }

    .profile-back-home {
        padding: 6px 10px;
        font-size: 13px;
    }

    .profile-hero {
        min-height: 190px;
        padding: 35px 18px;
    }

    .profile-hero-content h1 {
        font-size: 30px;
    }

    .profile-hero-content p {
        font-size: 14px;
        line-height: 1.6;
    }

    .profile-content {
        width: calc(100% - 30px);
        margin-top: 22px;
    }

    .profile-card {
        padding: 22px 18px;
    }

    .profile-form-grid {
        grid-template-columns: 1fr;
        gap: 18px;
    }

    .profile-photo-section {
        align-items: flex-start;
        gap: 16px;
    }

    .profile-photo-frame {
        width: 86px;
        height: 86px;
        min-width: 86px;
    }

    .profile-photo-details h3 {
        font-size: 15px;
    }

    .profile-photo-details p {
        font-size: 12px;
    }

    .profile-form-actions {
        margin-top: 22px;
    }

    .update-btn {
        width: 100%;
    }
}

@media (max-width: 380px) {

    .profile-brand {
        font-size: 17px;
    }

    .profile-back-home {
        padding: 6px 8px;
        font-size: 12px;
    }

    .profile-card {
        padding: 18px 14px;
    }

    .profile-photo-section {
        flex-direction: column;
    }
}
</style>
