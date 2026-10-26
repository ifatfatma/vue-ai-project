<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
    prompt: { type: Object, required: true },
    categories: { type: Array, default: () => [] },
    availableTools: { type: Array, default: () => [] },
    selectedTools: { type: Array, default: () => [] },
    updateUrl: { type: String, required: true },
    myPromptsUrl: { type: String, required: true },
    homeUrl: { type: String, required: true },
    csrfToken: { type: String, required: true },
    successMessage: { type: String, default: '' },
    errors: { type: Object, default: () => ({}) },
    currentImageUrl: { type: String, default: '' },
});

const title = ref(props.prompt.title || '');
const categoryId = ref(String(props.prompt.category_id || ''));
const promptText = ref(props.prompt.prompt_text || '');
const selectedToolValues = ref(props.selectedTools.map(String));

const fileInput = ref(null);
const dropdown = ref(null);
const previewUrl = ref(null);
const selectedFile = ref(null);
const imageError = ref('');
const imagePreview = ref('');
const imageFileName = ref('');
const imageFileSize = ref('');
const imagePreviewVisible = ref(false);
const existingImageVisible = ref(Boolean(props.currentImageUrl));
const dropdownOpen = ref(false);
const submitting = ref(false);
const toastItems = ref([]);
let toastCounter = 0;
let toastTimers = [];

const selectedToolOptions = computed(() =>
    props.availableTools.filter(tool =>
        selectedToolValues.value.includes(String(tool.key))
    )
);

const allErrors = computed(() => Object.values(props.errors).flat());

function toggleTool(value) {
    const key = String(value);
    selectedToolValues.value = selectedToolValues.value.includes(key)
        ? selectedToolValues.value.filter(item => item !== key)
        : [...selectedToolValues.value, key];
}

function formatBytes(bytes) {
    return bytes < 1024 * 1024
        ? (bytes / 1024).toFixed(1) + ' KB'
        : (bytes / (1024 * 1024)).toFixed(2) + ' MB';
}

function showEditToast(message, type = 'success') {
    const id = ++toastCounter;
    toastItems.value.push({ id, message, type });

    const timer = setTimeout(() => {
        toastItems.value = toastItems.value.filter(item => item.id !== id);
    }, 3500);

    toastTimers.push(timer);
}

function handleImageChange(event) {
    const file = event.target.files?.[0];

    if (!file) return;

    const allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    if (!allowedTypes.includes(file.type)) {
        event.target.value = '';
        showEditToast('Please select a JPG, PNG, GIF or WEBP image.', 'danger');
        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        event.target.value = '';
        showEditToast('Image size must be 2 MB or less.', 'danger');
        return;
    }

    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }

    previewUrl.value = URL.createObjectURL(file);
    selectedFile.value = file;
    imagePreview.value = previewUrl.value;
    imageFileName.value = file.name;
    imageFileSize.value = formatBytes(file.size);
    imagePreviewVisible.value = true;
    existingImageVisible.value = false;
    imageError.value = '';
}

function clearNewImage() {
    if (fileInput.value) {
        fileInput.value.value = '';
    }

    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
    }

    selectedFile.value = null;
    imagePreview.value = '';
    imageFileName.value = '';
    imageFileSize.value = '';
    imagePreviewVisible.value = false;
    existingImageVisible.value = Boolean(props.currentImageUrl);
    imageError.value = '';
}

function handleSubmit(event) {
    if (submitting.value) {
        event.preventDefault();
        return;
    }

    const form = event.currentTarget;

    if (!form.checkValidity()) {
        event.preventDefault();
        form.reportValidity();
        return;
    }

    submitting.value = true;
}

function handleOutsideClick(event) {
    if (dropdown.value && !dropdown.value.contains(event.target)) {
        dropdownOpen.value = false;
    }
}

onMounted(() => {
    document.addEventListener('click', handleOutsideClick);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleOutsideClick);

    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }

    toastTimers.forEach(timer => clearTimeout(timer));
});
</script>

<template>
    <div class="edit-prompt-page d-flex flex-column min-vh-100">
        <!-- Navbar -->
        <nav class="navbar modern-navbar shadow-sm">
            <div class="container-fluid px-3 px-lg-4">
                <a class="navbar-brand d-flex align-items-center" :href="homeUrl">
                    <span class="brand-icon"><i class="bi bi-stars"></i></span>
                    <span>AI Prompt Hub</span>
                </a>

                <a :href="myPromptsUrl" class="btn btn-outline-light btn-sm fw-bold">
                    <i class="bi bi-arrow-left me-1"></i> Back to My Prompts
                </a>
            </div>
        </nav>

        <!-- Hero -->
        <section class="hero-section text-center">
            <div class="container">
                <h1 class="fw-bold display-6 mb-2">
                    <i class="bi bi-pencil-square me-2"></i>Edit Your Prompt
                </h1>
                <p class="mb-0" style="font-size:14px">
                    Update your details. Editing may send your prompt for re-approval.
                </p>
            </div>
        </section>

        <main class="container edit-container flex-grow-1">
            <div v-if="successMessage" class="alert alert-success shadow-sm mb-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ successMessage }}
            </div>

            <div v-if="allErrors.length" class="alert alert-danger shadow-sm mb-3" role="alert">
                <div class="fw-bold mb-1">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    Please correct the following:
                </div>
                <ul class="mb-0 ps-4">
                    <li v-for="(error, index) in allErrors" :key="index">
                        {{ error }}
                    </li>
                </ul>
            </div>

            <div class="edit-card">
                <form
                    :action="updateUrl"
                    method="POST"
                    enctype="multipart/form-data"
                    @submit="handleSubmit"
                >
                    <input type="hidden" name="_token" :value="csrfToken">
                    <input type="hidden" name="_method" value="PUT">

                    <!-- Basic information -->
                    <section class="form-section">
                        <div class="section-heading">
                            <div class="section-icon"><i class="bi bi-info-circle"></i></div>
                            <div>
                                <h6>Basic Information</h6>
                                <p>Update the title and category of your prompt.</p>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label">
                                Prompt Title <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                id="title"
                                name="title"
                                v-model="title"
                                class="form-control"
                                :class="{ 'is-invalid': errors.title?.length }"
                                placeholder="Enter prompt title..."
                                required
                            >
                            <div v-if="errors.title?.[0]" class="invalid-feedback">
                                {{ errors.title[0] }}
                            </div>
                        </div>

                        <div>
                            <label for="category_id" class="form-label">
                                Category <span class="text-danger">*</span>
                            </label>
                            <select
                                id="category_id"
                                name="category_id"
                                v-model="categoryId"
                                class="form-select"
                                :class="{ 'is-invalid': errors.category_id?.length }"
                                required
                            >
                                <option value="">Select Category</option>
                                <option
                                    v-for="category in categories"
                                    :key="category.id"
                                    :value="String(category.id)"
                                >
                                    {{ category.name }}
                                </option>
                            </select>
                            <div v-if="errors.category_id?.[0]" class="invalid-feedback">
                                {{ errors.category_id[0] }}
                            </div>
                        </div>
                    </section>

                    <!-- AI tools -->
                    <section class="form-section">
                        <div class="section-heading">
                            <div class="section-icon"><i class="bi bi-robot"></i></div>
                            <div>
                                <h6>AI Tools / Platforms</h6>
                                <p>Select all AI platforms this prompt is useful for.</p>
                            </div>
                        </div>

                        <label class="form-label">
                            AI Tools / Platforms <span class="text-danger">*</span>
                        </label>

                        <div
                            ref="dropdown"
                            class="ai-tools-dropdown"
                            :class="{ open: dropdownOpen }"
                        >
                            <button
                                type="button"
                                class="ai-tools-dropdown-btn"
                                :aria-expanded="String(dropdownOpen)"
                                @click="dropdownOpen = !dropdownOpen"
                            >
                                <span>
                                    {{ selectedToolOptions.length
                                        ? selectedToolOptions.length + ' tool' +
                                            (selectedToolOptions.length === 1 ? '' : 's') +
                                            ' selected'
                                        : 'Select AI Tools' }}
                                </span>
                                <i class="bi bi-chevron-down"></i>
                            </button>

                            <div class="ai-tools-dropdown-menu">
                                <label
                                    v-for="tool in availableTools"
                                    :key="tool.key"
                                    class="ai-tool-option"
                                >
                                    <input
                                        type="checkbox"
                                        name="ai_tools[]"
                                        :value="tool.key"
                                        :checked="selectedToolValues.includes(String(tool.key))"
                                        @change="toggleTool(tool.key)"
                                    >
                                    <span>{{ tool.label }}</span>
                                </label>

                                <div v-if="!availableTools.length" class="text-muted small p-2">
                                    AI tools are not configured.
                                </div>
                            </div>
                        </div>

                        <div class="selected-tool-badges">
                            <span
                                v-for="tool in selectedToolOptions"
                                :key="tool.key"
                                class="tool-badge"
                            >
                                {{ tool.label }}
                            </span>
                        </div>

                        <small class="field-hint">
                            <i class="bi bi-info-circle me-1"></i>You can select multiple AI tools.
                        </small>

                        <div v-if="errors.ai_tools?.[0]" class="text-danger small mt-2">
                            {{ errors.ai_tools[0] }}
                        </div>
                        <div v-if="errors['ai_tools.0']?.[0]" class="text-danger small mt-2">
                            {{ errors['ai_tools.0'][0] }}
                        </div>
                    </section>

                    <!-- Prompt text -->
                    <section class="form-section">
                        <div class="section-heading">
                            <div class="section-icon"><i class="bi bi-pencil-square"></i></div>
                            <div>
                                <h6>Prompt Details</h6>
                                <p>Edit the prompt users can copy and customize.</p>
                            </div>
                        </div>

                        <label for="prompt_text" class="form-label">
                            Prompt Text <span class="text-danger">*</span>
                        </label>
                        <textarea
                            id="prompt_text"
                            name="prompt_text"
                            v-model="promptText"
                            class="form-control"
                            :class="{ 'is-invalid': errors.prompt_text?.length }"
                            rows="7"
                            placeholder="Write your prompt here...&#10;&#10;Use [1], [2], [3] for details users can customize."
                            required
                        ></textarea>

                        <div class="prompt-hint">
                            <i class="bi bi-lightbulb me-1"></i>
                            Tip: Use <strong>[1]</strong>, <strong>[2]</strong>,
                            <strong>[3]</strong> for customizable values.
                        </div>

                        <div v-if="errors.prompt_text?.[0]" class="text-danger small mt-2">
                            {{ errors.prompt_text[0] }}
                        </div>
                    </section>

                    <!-- Image upload -->
                    <section class="form-section">
                        <div class="section-heading">
                            <div class="section-icon"><i class="bi bi-image"></i></div>
                            <div>
                                <h6>Prompt Image</h6>
                                <p>Keep your current image or choose a replacement.</p>
                            </div>
                        </div>

                        <label for="image" class="form-label">
                            Image / Output Example <span class="text-muted fw-normal">(Optional)</span>
                        </label>

                        <label for="image" class="image-upload-box" id="imageUploadBox">
                            <span class="image-upload-icon">
                                <i class="bi bi-cloud-arrow-up"></i>
                            </span>
                            <span>
                                <span class="image-upload-title d-block">Choose Prompt Image</span>
                                <span class="image-upload-text d-block">
                                    JPG, JPEG, PNG, GIF or WEBP · Maximum 2 MB
                                </span>
                            </span>
                        </label>

                        <input
                            ref="fileInput"
                            type="file"
                            id="image"
                            name="image"
                            class="visually-hidden"
                            :class="{ 'is-invalid': errors.image?.length }"
                            accept="image/jpeg,image/png,image/gif,image/webp"
                            @change="handleImageChange"
                        >

                        <div v-if="errors.image?.[0]" class="text-danger small mt-2">
                            {{ errors.image[0] }}
                        </div>

                        <div v-if="imagePreviewVisible" class="image-preview-box">
                            <div class="image-preview-layout">
                                <img :src="imagePreview" class="image-preview" alt="Selected prompt image preview">

                                <div class="flex-grow-1 min-w-0">
                                    <div class="image-preview-name">{{ imageFileName }}</div>
                                    <div class="image-preview-size">{{ imageFileSize }}</div>

                                    <div class="image-preview-actions">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            @click="fileInput?.click()"
                                        >
                                            <i class="bi bi-arrow-repeat me-1"></i>Change Image
                                        </button>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            @click="clearNewImage"
                                        >
                                            <i class="bi bi-trash me-1"></i>Remove New Image
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="currentImageUrl && existingImageVisible" class="image-preview-box">
                            <div class="image-preview-layout">
                                <img :src="currentImageUrl" class="image-preview" alt="Current prompt image">

                                <div class="flex-grow-1 min-w-0">
                                    <span class="current-image-label">Current saved image</span>
                                    <div class="image-preview-name">Existing prompt image</div>
                                    <div class="image-preview-size">
                                        Choose a new image above to replace it.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <small class="field-hint">
                            Leave this field empty to keep the existing image. Removing the image
                            from this preview only clears a newly selected file; it does not
                            delete the saved image.
                        </small>
                    </section>

                    <!-- Footer buttons -->
                    <div class="form-footer">
                        <a :href="myPromptsUrl" class="btn-cancel">
                            <i class="bi bi-x-lg me-1"></i>Cancel
                        </a>

                        <button type="submit" class="btn-update" :disabled="submitting">
                            <span
                                v-if="submitting"
                                class="spinner-border spinner-border-sm me-2"
                                role="status"
                                aria-hidden="true"
                            ></span>
                            <i v-else class="bi bi-send me-1"></i>
                            {{ submitting ? 'Updating Prompt...' : 'Update Prompt' }}
                        </button>
                    </div>
                </form>
            </div>
        </main>

        <!-- Footer -->
        <footer class="text-center mt-auto">
            <div class="p-3 small" style="background:rgba(0,0,0,.2)">
                © {{ new Date().getFullYear() }} Copyright:
                <a class="text-white fw-bold text-decoration-none" :href="homeUrl">
                    AI Prompt Hub
                </a>. All rights reserved.
            </div>
        </footer>

        <!-- Toast messages -->
        <div
            class="toast-container position-fixed top-0 end-0 p-3"
            aria-live="polite"
            aria-atomic="true"
        >
            <div
                v-for="toast in toastItems"
                :key="toast.id"
                class="toast show align-items-center border-0 mb-2"
                :class="'text-bg-' + toast.type"
                role="alert"
            >
                <div class="d-flex">
                    <div class="toast-body">{{ toast.message }}</div>
                    <button
                        type="button"
                        class="btn-close btn-close-white me-2 m-auto"
                        aria-label="Close"
                        @click="toastItems = toastItems.filter(item => item.id !== toast.id)"
                    ></button>
                </div>
            </div>
        </div>
    </div>
</template>


<style scoped>
* {
    box-sizing: border-box;
}

.edit-prompt-page {
    min-height: 100vh;
    background: #f4f6fc;
    color: #172033;
    font-family: Inter, system-ui, -apple-system, "Segoe UI", sans-serif;
}

/* Navbar */
.modern-navbar {
    background: #201747;
    padding: 10px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.navbar-brand {
    color: #fff !important;
    font-size: 18px;
    font-weight: 800;
    letter-spacing: -0.35px;
}

.brand-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    margin-right: 10px;
    border-radius: 11px;
    background: linear-gradient(135deg, #9747ff, #6d28d9);
    color: #fff;
    font-size: 19px;
    box-shadow: 0 4px 12px rgba(109, 40, 217, 0.2);
}

.modern-navbar .btn {
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 12px;
    font-weight: 700;
}

/* Hero header */
.hero-section {
    position: relative;
    background: linear-gradient(115deg, #171333 0%, #251850 58%, #382477 100%);
    color: #fff;
    padding: 27px 12px 43px;
}

.hero-section h1 {
    margin-bottom: 7px !important;
    font-size: clamp(26px, 3vw, 38px);
    font-weight: 800;
    line-height: 1.2;
    letter-spacing: -0.8px;
}

.hero-section p {
    color: #d8d7f3 !important;
    font-size: 13px !important;
    line-height: 1.5;
}

/* Centered form container */
.edit-prompt-page .edit-container {
    width: calc(100% - 32px);
    max-width: 700px !important;
    margin: -23px auto 40px;
    padding: 0;
    position: relative;
    z-index: 2;
}

.edit-prompt-page .edit-card {
    width: 100%;
    background: #fff;
    border: 1px solid #e7eaf3;
    border-radius: 16px;
    box-shadow: 0 15px 42px rgba(27, 25, 67, 0.10);
    overflow: hidden;
}

.edit-card form {
    width: 100%;
    margin: 0;
}

/* Form sections */
.edit-prompt-page .form-section {
    position: relative;
    display: block;
    width: 100%;
    padding: 20px 24px;
    border-bottom: 1px solid #edf0f6;
    background: #fff;
}

.edit-prompt-page .form-section:last-of-type {
    border-bottom: 0;
}

.section-heading {
    display: flex;
    align-items: center;
    gap: 11px;
    margin-bottom: 19px;
}

.section-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 37px;
    width: 37px;
    height: 37px;
    border-radius: 11px;
    background: #f0edff;
    color: #6845ed;
    font-size: 17px;
}

.section-heading h6 {
    margin: 0 0 3px;
    color: #171b2c;
    font-size: 13px;
    font-weight: 800;
    line-height: 1.4;
}

.section-heading p {
    margin: 0;
    color: #778198;
    font-size: 11px;
    line-height: 1.5;
}

/* Labels and form controls */
.edit-prompt-page .form-label {
    display: block;
    margin-bottom: 7px;
    color: #29334a;
    font-size: 11px;
    font-weight: 750;
}

.edit-prompt-page .form-control,
.edit-prompt-page .form-select {
    display: block;
    width: 100%;
    min-height: 41px;
    padding: 9px 12px;
    border: 1px solid #dbe2f0;
    border-radius: 8px;
    background-color: #fff;
    color: #27324a;
    font-size: 12px;
    box-shadow: none;
}

.edit-prompt-page .form-control:focus,
.edit-prompt-page .form-select:focus {
    border-color: #8b7cf6;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.11);
}

.edit-prompt-page textarea.form-control {
    min-height: 145px;
    resize: vertical;
    line-height: 1.65;
}

.edit-prompt-page .invalid-feedback {
    font-size: 11px;
}

.field-hint {
    display: block;
    margin-top: 8px;
    color: #778198;
    font-size: 10px;
    line-height: 1.6;
}

/* AI tools dropdown */
.ai-tools-dropdown {
    position: relative;
    display: block;
    width: 100%;
    clear: both;
}

.ai-tools-dropdown-btn {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    min-height: 40px;
    padding: 9px 12px;
    border: 1px solid #dbe2f0;
    border-radius: 8px;
    background: #fff;
    color: #33405a;
    font-size: 12px;
    text-align: left;
    cursor: pointer;
}

.ai-tools-dropdown-btn:focus {
    outline: 0;
    border-color: #8b7cf6;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.11);
}

.ai-tools-dropdown-btn i {
    margin-left: 12px;
    flex-shrink: 0;
}

.ai-tools-dropdown-menu {
    display: none;
    position: absolute;
    top: calc(100% + 5px);
    left: 0;
    right: 0;
    z-index: 100;
    max-height: 230px;
    overflow-y: auto;
    padding: 6px;
    border: 1px solid #e2e6f0;
    border-radius: 9px;
    background: #fff;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.14);
}

.ai-tools-dropdown.open .ai-tools-dropdown-menu {
    display: block;
}

.ai-tool-option {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 9px;
    border-radius: 7px;
    color: #334155;
    font-size: 12px;
    cursor: pointer;
}

.ai-tool-option:hover {
    background: #f4f5fb;
}

.ai-tool-option input {
    width: 15px;
    height: 15px;
    flex-shrink: 0;
    accent-color: #6845ed;
}

/*
 * IMPORTANT:
 * Keep selected tool badges in normal document flow.
 * This prevents a global .tool-badge rule from positioning
 * a badge in the Basic Information section.
 */
.edit-prompt-page .selected-tool-badges {
    position: static !important;
    inset: auto !important;
    display: flex !important;
    flex-direction: row !important;
    align-items: center;
    justify-content: flex-start;
    flex-wrap: wrap;
    gap: 6px;
    width: 100%;
    min-height: 0;
    margin: 8px 0 0 !important;
    padding: 0 !important;
    float: none !important;
    clear: both;
    transform: none !important;
}

.edit-prompt-page .selected-tool-badges .tool-badge {
    position: static !important;
    inset: auto !important;
    display: inline-flex !important;
    align-items: center;
    flex: 0 0 auto;
    width: auto !important;
    max-width: 100%;
    margin: 0 !important;
    padding: 5px 9px;
    border: 0;
    border-radius: 20px;
    background: #f0edff;
    color: #5035c6;
    font-size: 10px;
    font-weight: 750;
    line-height: 1.35;
    float: none !important;
    transform: none !important;
}

/* Prompt customization tip */
.prompt-hint {
    margin-top: 9px;
    padding: 9px 11px;
    border: 1px solid #eee5ff;
    border-radius: 8px;
    background: #f7f3ff;
    color: #6838b5;
    font-size: 11px;
    line-height: 1.5;
}

/* Image upload */
.image-upload-box {
    display: flex;
    align-items: center;
    gap: 11px;
    width: 100%;
    padding: 12px;
    border: 1px dashed #b9b7ff;
    border-radius: 9px;
    background: #fbfaff;
    cursor: pointer;
    transition: background 0.2s, border-color 0.2s;
}

.image-upload-box:hover {
    border-color: #7160ef;
    background: #f6f4ff;
}

.image-upload-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 36px;
    width: 36px;
    height: 36px;
    border-radius: 9px;
    background: #f0edff;
    color: #6246ea;
    font-size: 19px;
}

.image-upload-title {
    color: #27324a;
    font-size: 11px;
    font-weight: 800;
}

.image-upload-text {
    margin-top: 3px;
    color: #7a8499;
    font-size: 10px;
    line-height: 1.5;
}

.image-preview-box {
    margin-top: 10px;
    padding: 10px;
    border: 1px solid #e2e6f0;
    border-radius: 9px;
    background: #fff;
}

.image-preview-layout {
    display: flex;
    align-items: center;
    gap: 11px;
}

.image-preview {
    width: 76px;
    height: 66px;
    flex-shrink: 0;
    object-fit: cover;
    border: 1px solid #e2e6f0;
    border-radius: 7px;
    background: #f1f5f9;
}

.image-preview-name {
    color: #27324a;
    font-size: 11px;
    font-weight: 750;
    overflow-wrap: anywhere;
}

.image-preview-size {
    margin-top: 3px;
    color: #7a8499;
    font-size: 10px;
}

.image-preview-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 8px;
}

.image-preview-actions .btn {
    padding: 5px 8px;
    border-radius: 6px;
    font-size: 10px;
}

.current-image-label {
    display: inline-block;
    margin-bottom: 5px;
    padding: 4px 8px;
    border-radius: 20px;
    background: #d1fae5;
    color: #047857;
    font-size: 9px;
    font-weight: 800;
}

/* Bottom action buttons */
.form-footer {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    padding: 14px 24px;
    border-top: 1px solid #edf0f6;
    border-radius: 0 0 16px 16px;
    background: #fafbff;
}

.btn-cancel {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    background: #fff;
    color: #526078;
    font-size: 11px;
    font-weight: 750;
    text-decoration: none;
}

.btn-cancel:hover {
    background: #f3f5fa;
    color: #27324a;
}

.btn-update {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 9px 14px;
    border: 0;
    border-radius: 7px;
    background: linear-gradient(135deg, #7057f2, #5737df);
    box-shadow: 0 5px 12px rgba(79, 70, 229, 0.18);
    color: #fff;
    font-size: 11px;
    font-weight: 800;
    transition: filter 0.2s, box-shadow 0.2s;
}

.btn-update:hover {
    color: #fff;
    filter: brightness(1.05);
    box-shadow: 0 7px 16px rgba(79, 70, 229, 0.23);
}

.btn-update:disabled {
    opacity: 0.75;
    cursor: wait;
}

.edit-prompt-page .alert {
    border-radius: 9px;
    font-size: 12px;
}

.edit-prompt-page footer {
    margin-top: auto;
    background: #201747;
    color: #fff;
}

.toast-container {
    z-index: 1090;
}

/* Tablet and mobile */
@media (max-width: 767px) {
    .edit-prompt-page .edit-container {
        width: calc(100% - 24px);
        margin-top: -19px;
        margin-bottom: 28px;
    }

    .edit-prompt-page .form-section {
        padding: 18px;
    }

    .form-footer {
        padding: 13px 18px;
    }
}

@media (max-width: 576px) {
    .modern-navbar {
        padding: 8px 0;
    }

    .navbar-brand {
        font-size: 14px;
    }

    .brand-icon {
        width: 31px;
        height: 31px;
        margin-right: 7px;
        font-size: 16px;
    }

    .modern-navbar .btn {
        padding: 6px 8px;
        font-size: 10px;
    }

    .hero-section {
        padding: 25px 10px 35px;
    }

    .hero-section h1 {
        font-size: 26px;
    }

    .hero-section p {
        max-width: 330px;
        margin: 0 auto;
    }

    .edit-prompt-page .edit-container {
        width: calc(100% - 20px);
        margin-top: -17px;
    }

    .edit-prompt-page .form-section {
        padding: 16px;
    }

    .section-heading {
        gap: 9px;
        margin-bottom: 16px;
    }

    .section-icon {
        flex-basis: 34px;
        width: 34px;
        height: 34px;
    }

    .edit-prompt-page textarea.form-control {
        min-height: 125px;
    }

    .form-footer {
        padding: 12px 16px;
    }

    .image-preview {
        width: 65px;
        height: 60px;
    }

    .form-footer > * {
        flex: 0 1 auto;
    }
}
</style>

