
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
        <div class="container py-5 profile-container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-7">

                    <div class="mb-3">
                        <a :href="homeUrl" class="back-link">
                            &larr; Back to Home
                        </a>
                    </div>

                    <div class="card profile-card p-4 p-md-5 shadow-lg">
                        <h3 class="mb-4 text-white">My Profile</h3>

                        <!-- Success message -->
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

                        <!-- Laravel validation errors -->
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

                            <!-- Profile Image -->
                            <div class="mb-4 text-center">
                                <img
                                    :src="profilePreview"
                                    alt="Profile Image Preview"
                                    class="rounded-circle mb-2 shadow profile-image"
                                >

                                <div class="mt-3">
                                    <label
                                        for="profile_image"
                                        class="form-label text-light d-block"
                                    >
                                        Profile Image
                                    </label>

                                    <input
                                        ref="imageInput"
                                        type="file"
                                        id="profile_image"
                                        name="profile_image"
                                        class="form-control form-control-sm"
                                        accept=".jpg,.jpeg,.png,.gif,image/jpeg,image/png,image/gif"
                                        @change="handleImageChange"
                                    >

                                    <small class="image-hint d-block mt-2">
                                        JPG, JPEG, PNG or GIF.
                                        Maximum size: 2 MB.
                                    </small>

                                    <small
                                        v-if="imageError"
                                        class="text-danger d-block mt-2"
                                    >
                                        {{ imageError }}
                                    </small>

                                    <small
                                        v-if="errors.profile_image?.[0]"
                                        class="text-danger d-block mt-2"
                                    >
                                        {{ errors.profile_image[0] }}
                                    </small>
                                </div>
                            </div>

                            <!-- Full Name -->
                            <div class="mb-3">
                                <label
                                    for="name"
                                    class="form-label text-light"
                                >
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
                                >

                                <div
                                    v-if="errors.name?.[0]"
                                    class="invalid-feedback"
                                >
                                    {{ errors.name[0] }}
                                </div>
                            </div>

                            <!-- Email Address -->
                            <div class="mb-3">
                                <label
                                    for="email"
                                    class="form-label text-light"
                                >
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
                                >

                                <div
                                    v-if="errors.email?.[0]"
                                    class="invalid-feedback"
                                >
                                    {{ errors.email[0] }}
                                </div>
                            </div>

                            <!-- Submit -->
                            <button
                                type="submit"
                                class="btn btn-primary update-btn w-100 py-2 mt-2"
                            >
                                Update Profile
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.profile-page {
    background-color: #0f0c1b;
    color: #fff;
    min-height: 100vh;
}

.profile-container {
    min-height: 100vh;
}

.profile-card {
    background-color: #1a1528;
    border: 1px solid #2d2640;
    border-radius: 15px;
}

.form-control {
    background-color: #100d1d !important;
    color: #fff !important;
    border: 1px solid #3b3450 !important;
}

.form-control:focus {
    border-color: #8b5cf6 !important;
    box-shadow: 0 0 0 0.2rem rgba(139, 92, 246, .2);
}

.form-control::placeholder {
    color: #aaa;
}

.form-control::file-selector-button {
    background-color: #2d2640;
    color: #fff;
    border: 0;
}

.profile-image {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border: 3px solid #8b5cf6;
    background-color: #100d1d;
}

.update-btn {
    background: linear-gradient(135deg, #6366f1, #a855f7);
    border: none;
    font-weight: 600;
}

.update-btn:hover {
    opacity: .9;
}

.back-link {
    color: #e5e7eb;
    text-decoration: none;
}

.back-link:hover {
    color: #a78bfa;
}

.image-hint {
    color: #aaa;
    font-size: 13px;
}
</style>
