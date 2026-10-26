<script setup>
import { computed, onMounted, onBeforeUnmount, ref } from 'vue';

const props = defineProps({
    initialPrompts: { type: Array, default: () => [] },
    currentPage: { type: Number, default: 1 },
    lastPage: { type: Number, default: 1 },
    total: { type: Number, default: 0 },
    exploreUrl: { type: String, default: '/' },
    saveUrlTemplate: { type: String, default: '/prompts/__PROMPT_ID__/save' },
});

const prompts = ref([...props.initialPrompts]);
const page = ref(props.currentPage);
const pages = ref(props.lastPage);
const totalSaved = ref(props.total);
const busyPromptId = ref(null);
const modalPrompt = ref(null);
const removeDialog = ref(null);
const removingPrompt = ref(null);
const removing = ref(false);
const variableValues = ref({});
const expandedPromptIds = ref([]);
const toastMessage = ref('');
const toastVisible = ref(false);

let toastTimer = null;

function rawPromptText(prompt) {
    return String(
        prompt?.prompt_text ??
        prompt?.prompt ??
        prompt?.description ??
        ''
    );
}

function getVariableMeta(prompt) {
    const text = rawPromptText(prompt);
    const match = text.match(/<!--AI_PROMPT_VARIABLES:([\s\S]*?)-->\s*$/i);

    if (!match) return {};

    try {
        const parsed = JSON.parse(decodeURIComponent(match[1]));
        return parsed && typeof parsed === 'object' ? parsed : {};
    } catch {
        try {
            return JSON.parse(match[1]);
        } catch {
            return {};
        }
    }
}

function promptText(prompt) {
    return rawPromptText(prompt)
        .replace(/<!--AI_PROMPT_VARIABLES:[\s\S]*?-->\s*$/i, '')
        .trim();
}

function variablesFor(prompt) {
    const text = promptText(prompt);
    const matches = [...text.matchAll(/\[([^[\]]+)\]/g)];
    const keys = [
        ...new Set(
            matches.map(match => match[1].trim()).filter(Boolean)
        )
    ];
    const metadata = getVariableMeta(prompt);

    return keys.map((key, index) => {
        const numeric = /^\d+$/.test(key);
        const settings = numeric ? (metadata[key] || {}) : {};

        return {
            key,
            index: index + 1,
            label: String(settings.label || `Variable ${index + 1}`),
            example: String(settings.example || (!numeric ? key : '')),
        };
    });
}

function toolsFor(prompt) {
    const value = prompt?.ai_tool;

    if (Array.isArray(value)) return value;

    if (typeof value === 'string' && value.trim()) {
        try {
            const decoded = JSON.parse(value);
            return Array.isArray(decoded) ? decoded : [value];
        } catch {
            return value.split(',').map(item => item.trim()).filter(Boolean);
        }
    }

    return [];
}

function readableTool(tool) {
    return String(tool)
        .replaceAll('_', ' ')
        .replace(/\b\w/g, letter => letter.toUpperCase());
}

function toolUrl(tool) {
    const urls = {
        chatgpt: 'https://chatgpt.com/',
        claude: 'https://claude.ai/',
        gemini: 'https://gemini.google.com/',
        midjourney: 'https://www.midjourney.com/',
        dalle: 'https://openai.com/',
        perplexity: 'https://www.perplexity.ai/',
        copilot: 'https://copilot.microsoft.com/',
        grok: 'https://grok.com/',
        deepseek: 'https://chat.deepseek.com/',
        mistral: 'https://chat.mistral.ai/',
        meta_ai: 'https://www.meta.ai/',
    };

    return urls[tool] || '#';
}

function imageUrl(prompt) {
    if (prompt?.image_url) return prompt.image_url;
    if (!prompt?.image) return '';

    if (
        /^(https?:)?\/\//i.test(prompt.image) ||
        prompt.image.startsWith('/')
    ) {
        return prompt.image;
    }

    return `/storage/${prompt.image}`;
}

function showToast(message) {
    toastMessage.value = message;
    toastVisible.value = true;

    if (toastTimer) clearTimeout(toastTimer);

    toastTimer = setTimeout(() => {
        toastVisible.value = false;
    }, 2500);
}

function openPrompt(prompt) {
    modalPrompt.value = prompt;
    variableValues.value[prompt.id] = {};
    expandedPromptIds.value = expandedPromptIds.value.filter(
        id => id !== prompt.id
    );
}

function closePrompt(prompt) {
    if (prompt?.id) variableValues.value[prompt.id] = {};
    modalPrompt.value = null;
}

function valueFor(promptId, variable) {
    return variableValues.value[promptId]?.[variable.key] || '';
}

function setVariable(promptId, key, value) {
    if (!variableValues.value[promptId]) {
        variableValues.value[promptId] = {};
    }

    variableValues.value[promptId][key] = value;
}

function resetVariables(prompt) {
    variableValues.value[prompt.id] = {};
}

async function copyText(text) {
    if (!String(text || '').trim()) {
        showToast('Nothing to copy.');
        return false;
    }

    try {
        await navigator.clipboard.writeText(text);
        showToast('Prompt copied successfully!');
        return true;
    } catch {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';

        document.body.appendChild(textarea);
        textarea.select();

        const success = document.execCommand('copy');
        textarea.remove();

        showToast(
            success
                ? 'Prompt copied successfully!'
                : 'Unable to copy the prompt.'
        );

        return success;
    }
}

async function copyPrompt(prompt) {
    await copyText(promptText(prompt));
}

async function copyCustomizedPrompt(prompt) {
    const variables = variablesFor(prompt);
    const values = variableValues.value[prompt.id] || {};

    const empty = variables.find(
        variable => !String(values[variable.key] || '').trim()
    );

    if (empty) {
        showToast(`Please fill ${empty.label}.`);
        return;
    }

    const text = promptText(prompt).replace(
        /\[([^[\]]+)\]/g,
        (match, key) => {
            const variable = variables.find(
                item => item.key === key.trim()
            );

            return variable
                ? String(values[variable.key] || '').trim()
                : match;
        }
    );

    await copyText(text);
}

/* Modern remove confirmation dialog */

function openRemoveDialog(prompt) {
    if (removing.value) return;

    removingPrompt.value = prompt;

    if (removeDialog.value && !removeDialog.value.open) {
        removeDialog.value.showModal();
    }
}

function closeRemoveDialog() {
    if (removing.value) return;

    if (removeDialog.value?.open) {
        removeDialog.value.close();
    }

    removingPrompt.value = null;
}

async function confirmRemovePrompt() {
    const prompt = removingPrompt.value;

    if (!prompt || removing.value) return;

    removing.value = true;
    busyPromptId.value = prompt.id;

    try {
        const csrf =
            document.querySelector('meta[name="csrf-token"]')
                ?.getAttribute('content') || '';

        const response = await fetch(
            props.saveUrlTemplate.replace('__PROMPT_ID__', prompt.id),
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({}),
            }
        );

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(
                data.message || 'Unable to remove prompt.'
            );
        }

        const removed =
            data.saved === false ||
            data.saved === 0 ||
            data.saved === '0';

        if (!removed) {
            throw new Error(
                data.message || 'Prompt was not removed.'
            );
        }

        prompts.value = prompts.value.filter(
            item => item.id !== prompt.id
        );

        totalSaved.value = Math.max(0, totalSaved.value - 1);

        if (modalPrompt.value?.id === prompt.id) {
            modalPrompt.value = null;
        }

        if (removeDialog.value?.open) {
            removeDialog.value.close();
        }

        removingPrompt.value = null;
        showToast('Prompt removed from saved list.');
    } catch (error) {
        showToast(error.message || 'Something went wrong.');
    } finally {
        busyPromptId.value = null;
        removing.value = false;
    }
}

function toggleReadMore(promptId) {
    expandedPromptIds.value = expandedPromptIds.value.includes(promptId)
        ? expandedPromptIds.value.filter(id => id !== promptId)
        : [...expandedPromptIds.value, promptId];
}

async function goToPage(nextPage) {
    if (
        nextPage < 1 ||
        nextPage > pages.value ||
        nextPage === page.value
    ) {
        return;
    }

    const url = new URL(window.location.href);
    url.searchParams.set('page', nextPage);
    window.location.href = url.toString();
}

const pageNumbers = computed(() => {
    const start = Math.max(1, page.value - 2);
    const end = Math.min(pages.value, page.value + 2);

    return Array.from(
        { length: end - start + 1 },
        (_, index) => start + index
    );
});

onMounted(() => {
    document.addEventListener('hidden.bs.modal', handleModalHidden);
});

onBeforeUnmount(() => {
    document.removeEventListener('hidden.bs.modal', handleModalHidden);

    if (toastTimer) clearTimeout(toastTimer);
});

function handleModalHidden(event) {
    const id = event.target?.id;

    if (!id?.startsWith('savedPromptModal')) return;

    const promptId = Number(id.replace('savedPromptModal', ''));

    variableValues.value[promptId] = {};
    expandedPromptIds.value = expandedPromptIds.value.filter(
        item => item !== promptId
    );

    if (modalPrompt.value?.id === promptId) {
        modalPrompt.value = null;
    }
}
</script>

<template>
    <div class="container saved-page">
        <div class="saved-header">
            <div class="saved-header-content">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="saved-icon">
                            <i class="bi bi-bookmark-fill"></i>
                        </div>

                        <div>
                            <h2 class="fw-bold mb-1">Saved Prompts</h2>
                            <p class="mb-0">
                                Your personal collection of saved AI prompts.
                            </p>

                            <span class="saved-count">
                                <i class="bi bi-bookmark-check-fill"></i>
                                {{ totalSaved }} Saved
                            </span>
                        </div>
                    </div>

                    <a :href="exploreUrl" class="btn explore-btn">
                        <i class="bi bi-grid-3x3-gap-fill"></i>
                        Explore Prompts
                    </a>
                </div>
            </div>
        </div>

        <div
            v-if="prompts.length"
            class="row g-4"
            id="savedPromptsGrid"
        >
            <div
                v-for="prompt in prompts"
                :key="prompt.id"
                class="col-md-6 col-lg-4 saved-prompt-item"
                :data-prompt-id="prompt.id"
            >
                <div class="saved-card">
                    <button
                        type="button"
                        class="saved-image-button"
                        title="View prompt"
                        @click="openPrompt(prompt)"
                        data-bs-toggle="modal"
                        :data-bs-target="`#savedPromptModal${prompt.id}`"
                    >
                        <img
                            v-if="imageUrl(prompt)"
                            :src="imageUrl(prompt)"
                            :alt="prompt.title"
                            class="saved-card-image"
                            @error="$event.target.style.display = 'none'"
                        >

                        <div v-else class="saved-card-placeholder">
                            <i class="bi bi-stars"></i>
                        </div>

                        <div class="saved-image-badge">
                            <i class="bi bi-image"></i> Prompt
                        </div>
                    </button>

                    <div class="saved-card-actions">
                        <button
                            v-if="variablesFor(prompt).length"
                            type="button"
                            class="btn saved-copy-btn"
                            data-bs-toggle="modal"
                            :data-bs-target="`#savedPromptModal${prompt.id}`"
                            @click="openPrompt(prompt)"
                        >
                            <i class="bi bi-sliders2"></i>
                            Customize &amp; Copy
                        </button>

                        <button
                            v-else
                            type="button"
                            class="btn saved-copy-btn"
                            @click="copyPrompt(prompt)"
                        >
                            <i class="bi bi-clipboard"></i> Copy
                        </button>

                        <button
                            type="button"
                            class="btn saved-remove-btn"
                            :disabled="busyPromptId === prompt.id"
                            @click="openRemoveDialog(prompt)"
                            title="Remove from saved"
                        >
                            <span
                                v-if="busyPromptId === prompt.id"
                                class="spinner-border spinner-border-sm"
                            ></span>

                            <template v-else>
                                <i class="bi bi-bookmark-x"></i>
                                Remove
                            </template>
                        </button>
                    </div>
                </div>

                <!-- View and customize prompt modal -->
                <div
                    class="modal fade saved-view-modal"
                    :id="`savedPromptModal${prompt.id}`"
                    tabindex="-1"
                    aria-hidden="true"
                >
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content border-0 shadow-lg rounded-4">
                            <div class="modal-header modern-modal-header border-0">
                                <span class="badge bg-light text-primary fs-6">
                                    {{ prompt.category?.name || 'General' }}
                                </span>

                                <button
                                    type="button"
                                    class="btn-close btn-close-white"
                                    data-bs-dismiss="modal"
                                    aria-label="Close"
                                    @click="closePrompt(prompt)"
                                ></button>
                            </div>

                            <div class="modal-body p-4">
                                <h4 class="fw-bold text-dark mb-3">
                                    {{ prompt.title }}
                                </h4>

                                <div
                                    v-if="toolsFor(prompt).length"
                                    class="mb-3 d-flex flex-wrap gap-2"
                                >
                                    <a
                                        v-for="tool in toolsFor(prompt)"
                                        :key="tool"
                                        :href="toolUrl(tool)"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="ai-tool-badge text-decoration-none"
                                    >
                                        <i class="bi bi-robot"></i>
                                        {{ readableTool(tool) }}
                                    </a>
                                </div>

                                <div class="saved-modal-image-wrap">
                                    <img
                                        v-if="imageUrl(prompt)"
                                        :src="imageUrl(prompt)"
                                        :alt="prompt.title"
                                        class="saved-modal-image"
                                        @error="$event.target.style.display = 'none'"
                                    >

                                    <div
                                        v-else
                                        class="saved-modal-no-image"
                                    >
                                        <i class="bi bi-stars"></i>
                                    </div>
                                </div>

                                <div
                                    v-if="variablesFor(prompt).length"
                                    class="prompt-variables-box"
                                >
                                    <div class="prompt-variables-title">
                                        <i class="bi bi-sliders2-vertical"></i>
                                        Customize this prompt
                                    </div>

                                    <div class="prompt-variable-hint">
                                        Fill in all the details below before copying.
                                    </div>

                                    <div
                                        v-for="variable in variablesFor(prompt)"
                                        :key="variable.key"
                                        class="prompt-variable-field"
                                    >
                                        <label
                                            class="prompt-variable-label"
                                            :for="`saved-prompt-variable-${prompt.id}-${variable.index}`"
                                        >
                                            {{ variable.label }}
                                            <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            class="prompt-variable-input"
                                            :id="`saved-prompt-variable-${prompt.id}-${variable.index}`"
                                            :placeholder="variable.example || 'Enter your answer'"
                                            autocomplete="off"
                                            :value="valueFor(prompt.id, variable)"
                                            @input="setVariable(prompt.id, variable.key, $event.target.value)"
                                        >
                                    </div>

                                    <button
                                        type="button"
                                        class="prompt-variable-reset"
                                        @click="resetVariables(prompt)"
                                    >
                                        <i class="bi bi-arrow-counterclockwise me-1"></i>
                                        Reset
                                    </button>
                                </div>

                                <label class="fw-bold mb-2 text-muted small">
                                    PROMPT TEXT
                                </label>

                                <div class="p-3 modal-prompt-text position-relative">
                                    <pre
                                        v-if="!expandedPromptIds.includes(prompt.id)"
                                        style="white-space: pre-wrap; font-family: monospace; margin: 0; color:#475569;"
                                    >{{ promptText(prompt).length > 150 ? promptText(prompt).slice(0, 150) + '...' : promptText(prompt) }}</pre>

                                    <pre
                                        v-else
                                        style="white-space: pre-wrap; font-family: monospace; margin: 0; color:#475569;"
                                    >{{ promptText(prompt) }}</pre>

                                    <div
                                        v-if="promptText(prompt).length > 150"
                                        class="text-end mt-2"
                                    >
                                        <button
                                            type="button"
                                            class="btn btn-link btn-sm text-decoration-none fw-bold p-0"
                                            @click="toggleReadMore(prompt.id)"
                                        >
                                            {{
                                                expandedPromptIds.includes(prompt.id)
                                                    ? 'Read Less'
                                                    : 'Read More'
                                            }}

                                            <i
                                                :class="expandedPromptIds.includes(prompt.id)
                                                    ? 'bi bi-chevron-up'
                                                    : 'bi bi-chevron-down'"
                                            ></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer border-0 pt-0">
                                <button
                                    type="button"
                                    class="btn copy-btn fw-bold"
                                    @click="variablesFor(prompt).length
                                        ? copyCustomizedPrompt(prompt)
                                        : copyPrompt(prompt)"
                                >
                                    <i class="bi bi-clipboard-check me-1"></i>
                                    {{
                                        variablesFor(prompt).length
                                            ? 'Copy Filled Prompt'
                                            : 'Copy Prompt'
                                    }}
                                </button>

                                <button
                                    type="button"
                                    class="btn btn-light border"
                                    data-bs-dismiss="modal"
                                    @click="closePrompt(prompt)"
                                >
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty state -->
        <div v-else class="empty-saved" id="emptySavedState">
            <div class="empty-saved-icon">
                <i class="bi bi-bookmark-heart"></i>
            </div>

            <h4 class="fw-bold mb-2">No Saved Prompts Yet</h4>

            <p class="text-muted mb-4">
                Save your favourite prompts from the Prompt Hub and they will appear here.
            </p>

            <a :href="exploreUrl" class="btn explore-btn">
                <i class="bi bi-search"></i> Explore Prompts
            </a>
        </div>

        <!-- Pagination -->
        <nav
            v-if="pages > 1"
            class="saved-pagination d-flex justify-content-center mt-5"
            aria-label="Saved prompts pagination"
        >
            <ul class="pagination">
                <li class="page-item" :class="{ disabled: page <= 1 }">
                    <button
                        class="page-link"
                        type="button"
                        :disabled="page <= 1"
                        @click="goToPage(page - 1)"
                    >
                        Previous
                    </button>
                </li>

                <li
                    v-for="number in pageNumbers"
                    :key="number"
                    class="page-item"
                    :class="{ active: number === page }"
                >
                    <button
                        class="page-link"
                        type="button"
                        @click="goToPage(number)"
                    >
                        {{ number }}
                    </button>
                </li>

                <li class="page-item" :class="{ disabled: page >= pages }">
                    <button
                        class="page-link"
                        type="button"
                        :disabled="page >= pages"
                        @click="goToPage(page + 1)"
                    >
                        Next
                    </button>
                </li>
            </ul>
        </nav>

        <!-- Toast notification -->
        <transition name="saved-toast">
            <div v-if="toastVisible" id="savedPromptToast" role="status">
                <i class="bi bi-check-circle-fill"></i>
                {{ toastMessage }}
            </div>
        </transition>

        <!-- Compact centered Remove confirmation popup -->
        <dialog
            ref="removeDialog"
            class="remove-confirm-dialog"
            aria-labelledby="removeDialogTitle"
            @click.self="closeRemoveDialog"
            @cancel.prevent="closeRemoveDialog"
        >
            <div class="remove-dialog-content">
                <div class="remove-dialog-icon">
                    <i class="bi bi-bookmark-x-fill"></i>
                </div>

                <div class="remove-dialog-label">CONFIRM ACTION</div>

                <h2 id="removeDialogTitle">
                    Remove saved prompt?
                </h2>

                <p class="remove-dialog-description">
                    This prompt will be removed from your saved list.
                    You can save it again later.
                </p>

                <div
                    v-if="removingPrompt"
                    class="remove-prompt-preview"
                >
                    <span class="remove-preview-icon">
                        <i class="bi bi-file-earmark-text"></i>
                    </span>

                    <span class="remove-preview-title">
                        {{ removingPrompt.title || 'Selected prompt' }}
                    </span>
                </div>

                <div class="remove-dialog-actions">
                    <button
                        type="button"
                        class="remove-cancel-btn"
                        :disabled="removing"
                        @click="closeRemoveDialog"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        class="remove-confirm-btn"
                        :disabled="removing"
                        @click="confirmRemovePrompt"
                    >
                        <span
                            v-if="removing"
                            class="remove-spinner"
                        ></span>

                        <i v-else class="bi bi-bookmark-x"></i>

                        {{ removing ? 'Removing...' : 'Remove Prompt' }}
                    </button>
                </div>

                <div class="remove-dialog-footnote">
                    <i class="bi bi-shield-check"></i>
                    Only removes this prompt from your saved list.
                </div>
            </div>
        </dialog>
    </div>
</template>

<style>
/* Saved page */
.saved-page {
    min-height: calc(100vh - 80px);
    padding-top: 45px;
    padding-bottom: 80px;
    background:
        radial-gradient(circle at 10% 10%, rgba(99,102,241,.08), transparent 28%),
        radial-gradient(circle at 90% 80%, rgba(124,58,237,.07), transparent 30%);
}

.saved-header {
    position: relative;
    overflow: hidden;
    padding: 32px;
    margin-bottom: 35px;
    border-radius: 24px;
    border: 1px solid #e0e7ff;
    background: linear-gradient(135deg,#ffffff 0%,#f5f3ff 50%,#eef2ff 100%);
    box-shadow: 0 15px 45px rgba(79,70,229,.08);
}

.saved-header::before {
    content: "";
    position: absolute;
    width: 180px;
    height: 180px;
    right: -70px;
    top: -90px;
    border-radius: 50%;
    background: rgba(99,102,241,.10);
}

.saved-header::after {
    content: "";
    position: absolute;
    width: 120px;
    height: 120px;
    left: -60px;
    bottom: -70px;
    border-radius: 50%;
    background: rgba(124,58,237,.08);
}

.saved-header-content {
    position: relative;
    z-index: 2;
}

.saved-icon {
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border-radius: 18px;
    background: linear-gradient(135deg,#6366f1,#7c3aed);
    color: #fff;
    font-size: 27px;
    box-shadow: 0 12px 28px rgba(99,102,241,.25);
}

.saved-header h2 {
    color: #111827;
    font-size: 27px;
    letter-spacing: -.5px;
}

.saved-header p {
    color: #64748b;
    font-size: 14px;
}

.saved-count {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 10px;
    padding: 6px 12px;
    border-radius: 999px;
    background: #fff;
    border: 1px solid #ddd6fe;
    color: #6366f1;
    font-size: 12px;
    font-weight: 700;
}

.explore-btn {
    position: relative;
    z-index: 3;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    border: 0;
    border-radius: 12px;
    background: linear-gradient(135deg,#6366f1,#7c3aed);
    color: #fff;
    font-weight: 700;
    box-shadow: 0 10px 22px rgba(99,102,241,.20);
    transition: all .2s ease;
}

.explore-btn:hover {
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 14px 28px rgba(99,102,241,.28);
}

/* Saved prompt cards */
.saved-card {
    height: 100%;
    overflow: hidden;
    border-radius: 22px;
    border: 1px solid #e5e7eb;
    background: #fff;
    box-shadow: 0 8px 25px rgba(15,23,42,.05);
    transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
}

.saved-card:hover {
    transform: translateY(-6px);
    border-color: #c7d2fe;
    box-shadow: 0 20px 45px rgba(79,70,229,.12);
}

.saved-image-button {
    position: relative;
    display: block;
    width: 100%;
    padding: 0;
    border: 0;
    background: #eef2ff;
    overflow: hidden;
    cursor: pointer;
}

.saved-card-image {
    display: block;
    width: 100%;
    height: 310px;
    object-fit: cover;
    background: #f8fafc;
    transition: transform .4s ease;
}

.saved-card:hover .saved-card-image {
    transform: scale(1.03);
}

.saved-image-button::after {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(to top,rgba(15,23,42,.25),transparent 45%);
    pointer-events: none;
}

.saved-card-placeholder {
    width: 100%;
    height: 310px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg,#eef2ff,#f5f3ff);
    color: #6366f1;
    font-size: 48px;
}

.saved-image-badge {
    position: absolute;
    top: 14px;
    left: 14px;
    z-index: 5;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 11px;
    border-radius: 999px;
    background: rgba(15,23,42,.70);
    backdrop-filter: blur(8px);
    color: #fff;
    font-size: 11px;
    font-weight: 700;
}

.saved-card-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px;
    background: #fff;
}

.saved-copy-btn {
    flex: 1;
    min-height: 43px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    border: 0;
    border-radius: 11px;
    background: linear-gradient(135deg,#10b981,#059669);
    color: #fff;
    font-size: 14px;
    font-weight: 800;
    box-shadow: 0 7px 16px rgba(16,185,129,.15);
    transition: all .2s ease;
}

.saved-copy-btn:hover {
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 10px 22px rgba(16,185,129,.23);
}

.saved-remove-btn {
    min-width: 105px;
    min-height: 43px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    border-radius: 11px;
    border: 1px solid #fecaca;
    background: #fff7f7;
    color: #dc2626;
    font-size: 13px;
    font-weight: 700;
    transition: all .2s ease;
}

.saved-remove-btn:hover {
    color: #fff;
    background: #ef4444;
    border-color: #ef4444;
    transform: translateY(-2px);
}

/* Prompt detail modal */
.saved-view-modal .modal-dialog {
    max-width: 820px;
}

.saved-view-modal .modal-content {
    border: 0 !important;
    border-radius: 20px !important;
    overflow: hidden;
    box-shadow: 0 25px 70px rgba(15,23,42,.30);
}

.saved-view-modal .modern-modal-header {
    min-height: 58px;
    padding: 12px 18px;
    background: linear-gradient(135deg,#171447,#2e1b72);
    border: 0;
}

.saved-view-modal .modern-modal-header .badge {
    padding: 6px 10px;
    border-radius: 7px;
    font-size: 11px !important;
    font-weight: 700;
    color: #5b4cf0 !important;
    background: #fff !important;
}

.saved-view-modal .modal-body {
    padding: 18px 22px 12px;
}

.saved-view-modal .modal-body h4 {
    font-size: 18px;
    margin-bottom: 10px !important;
}

.saved-view-modal .ai-tool-badge {
    display: inline-flex !important;
    align-items: center;
    gap: 4px;
    padding: 3px 7px;
    border-radius: 5px;
    background: #fff;
    border: 1px solid #ddd6fe;
    color: #6d4aff;
    font-size: 8px;
    font-weight: 700;
}

.saved-view-modal .ai-tool-badge:hover {
    background: #f5f3ff;
}

.saved-modal-image-wrap {
    text-align: center;
    margin-bottom: 10px;
}

.saved-modal-image {
    display: inline-block;
    max-width: 100%;
    max-height: 300px;
    width: auto;
    height: auto;
    object-fit: contain;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
}

.saved-modal-no-image {
    width: 100%;
    min-height: 170px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: linear-gradient(135deg,#eef2ff,#f5f3ff);
    color: #6366f1;
    font-size: 45px;
    margin-bottom: 12px;
}

.saved-view-modal .prompt-variables-box {
    margin-bottom: 12px;
    padding: 11px;
    border-radius: 10px;
    border: 1px solid #e0e7ff;
    background: linear-gradient(135deg,#f8faff,#f5f3ff);
}

.saved-view-modal .prompt-variables-title {
    display: flex;
    align-items: center;
    gap: 7px;
    color: #3730a3;
    font-size: 12px;
    font-weight: 800;
    margin-bottom: 2px;
}

.saved-view-modal .prompt-variable-hint {
    color: #64748b;
    font-size: 9px;
    margin-bottom: 8px;
}

.saved-view-modal .prompt-variable-field + .prompt-variable-field {
    margin-top: 8px;
}

.saved-view-modal .prompt-variable-field {
    margin-bottom: 7px;
}

.saved-view-modal .prompt-variable-label {
    display: block;
    margin-bottom: 3px;
    color: #334155;
    font-size: 9px;
    font-weight: 700;
}

.saved-view-modal .prompt-variable-input {
    width: 100%;
    height: 30px;
    padding: 5px 9px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    outline: 0;
    background: #fff;
    color: #334155;
    font-size: 10px;
}

.saved-view-modal .prompt-variable-input::placeholder {
    color: #94a3b8;
    opacity: 1;
}

.saved-view-modal .prompt-variable-input:focus {
    border-color: #818cf8;
    box-shadow: 0 0 0 2px rgba(99,102,241,.10);
}

.saved-view-modal .prompt-variable-reset {
    border: 0;
    border-radius: 5px;
    padding: 4px 7px;
    background: #fff;
    color: #64748b;
    font-size: 9px;
    cursor: pointer;
}

.saved-view-modal .modal-prompt-text {
    min-height: 70px;
    max-height: 260px;
    overflow-y: auto;
    padding: 10px 12px !important;
    border: 1px solid #e0e7ff;
    border-radius: 9px;
    background: linear-gradient(135deg,#f8faff,#f5f3ff);
    font-size: 10px;
    line-height: 1.55;
}

.saved-view-modal .modal-prompt-text pre {
    font-size: 10px;
}

.saved-view-modal .modal-footer {
    padding: 10px 22px 16px;
}

.saved-view-modal .copy-btn {
    border: 0;
    border-radius: 7px;
    padding: 7px 12px;
    background: linear-gradient(135deg,#10b981,#059669);
    color: #fff;
    font-size: 11px;
}

.saved-view-modal .copy-btn:hover {
    color: #fff;
}

.saved-view-modal .btn-light {
    border-radius: 7px;
    font-size: 11px;
    padding: 7px 14px;
}

.saved-modal-copy-textarea {
    position: absolute;
    left: -99999px;
    width: 1px;
    height: 1px;
}

/* Empty state */
.empty-saved {
    padding: 90px 25px;
    text-align: center;
    border-radius: 25px;
    border: 1px dashed #cbd5e1;
    background: rgba(255,255,255,.75);
    box-shadow: 0 10px 35px rgba(15,23,42,.04);
}

.empty-saved-icon {
    width: 90px;
    height: 90px;
    margin: 0 auto 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 25px;
    background: linear-gradient(135deg,#eef2ff,#f5f3ff);
    color: #6366f1;
    font-size: 38px;
}

.empty-saved h4 {
    color: #111827;
}

/* Toast */
#savedPromptToast {
    position: fixed;
    right: 25px;
    bottom: 25px;
    z-index: 99999;
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 14px 18px;
    border-radius: 13px;
    background: linear-gradient(135deg,#6366f1,#7c3aed);
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    box-shadow: 0 15px 35px rgba(79,70,229,.28);
}

.saved-toast-enter-active,
.saved-toast-leave-active {
    transition: opacity .2s ease, transform .2s ease;
}

.saved-toast-enter-from,
.saved-toast-leave-to {
    opacity: 0;
    transform: translateY(8px);
}

/* Responsive page layout */
@media (max-width: 767px) {
    .saved-page {
        padding-top: 25px;
    }

    .saved-header {
        padding: 24px;
        border-radius: 20px;
    }

    .saved-header h2 {
        font-size: 23px;
    }

    .saved-icon {
        width: 54px;
        height: 54px;
        font-size: 23px;
    }

    .explore-btn {
        width: 100%;
        justify-content: center;
    }

    .saved-card-image,
    .saved-card-placeholder {
        height: 230px;
    }

    .saved-view-modal .modal-body {
        padding: 15px;
    }

    .saved-view-modal .modal-footer {
        padding: 10px 15px 14px;
    }
}

@media (max-width: 480px) {
    .saved-card-actions {
        flex-direction: column;
    }

    .saved-copy-btn,
    .saved-remove-btn {
        width: 100%;
    }
}

/* Compact, centered remove confirmation popup */
.remove-confirm-dialog {
    position: fixed;
    inset: 0;
    margin: auto;
    width: calc(100% - 32px);
    max-width: 420px;
    max-height: calc(100% - 32px);
    overflow-y: auto;
    padding: 0;
    border: 1px solid #eeeef4;
    border-radius: 18px;
    background: #fff;
    color: #24243a;
    box-shadow: 0 20px 60px rgba(20, 18, 45, .25);
}

.remove-confirm-dialog[open] {
    display: block;
    animation: remove-dialog-in .2s ease-out;
}

.remove-confirm-dialog::backdrop {
    background: rgba(20, 18, 45, .55);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

.remove-dialog-content {
    padding: 24px;
    text-align: center;
}

.remove-dialog-icon {
    display: flex;
    width: 54px;
    height: 54px;
    align-items: center;
    justify-content: center;
    margin: 0 auto 14px;
    border-radius: 16px;
    background: #fff0f1;
    color: #e04455;
    font-size: 23px;
}

.remove-dialog-label {
    margin-bottom: 6px;
    color: #88889d;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.5px;
}

.remove-dialog-content h2 {
    margin: 0;
    color: #24243a;
    font-size: 21px;
    font-weight: 750;
}

.remove-dialog-description {
    margin: 10px auto 17px;
    max-width: 320px;
    color: #77778b;
    font-size: 13px;
    line-height: 1.55;
}

.remove-prompt-preview {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 18px;
    padding: 10px 12px;
    border: 1px solid #ededf4;
    border-radius: 11px;
    background: #f8f8fc;
    text-align: left;
}

.remove-preview-icon {
    display: flex;
    flex: 0 0 34px;
    width: 34px;
    height: 34px;
    align-items: center;
    justify-content: center;
    border-radius: 9px;
    background: #eceaff;
    color: #6255d9;
    font-size: 16px;
}

.remove-preview-title {
    overflow: hidden;
    color: #39394f;
    font-size: 13px;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.remove-dialog-actions {
    display: flex;
    gap: 10px;
}

.remove-dialog-actions button {
    display: inline-flex;
    flex: 1;
    min-width: 0;
    min-height: 42px;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 10px 12px;
    border: 0;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
}

.remove-dialog-actions button:disabled {
    cursor: wait;
    opacity: .7;
}

.remove-cancel-btn {
    background: #f0f0f6;
    color: #55556c;
}

.remove-cancel-btn:hover:not(:disabled) {
    background: #e5e5ef;
}

.remove-confirm-btn {
    background: linear-gradient(135deg, #e84d5b, #cf3044);
    color: #fff;
    box-shadow: 0 5px 13px rgba(207, 48, 68, .2);
}

.remove-confirm-btn:hover:not(:disabled) {
    background: #c92e42;
}

.remove-spinner {
    width: 14px;
    height: 14px;
    border: 2px solid rgba(255, 255, 255, .45);
    border-top-color: #fff;
    border-radius: 50%;
    animation: remove-spin .7s linear infinite;
}

.remove-dialog-footnote {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    margin-top: 14px;
    color: #9696a8;
    font-size: 10px;
}

.remove-dialog-footnote i {
    color: #6e9d87;
}

@keyframes remove-dialog-in {
    from {
        opacity: 0;
        transform: scale(.95) translateY(6px);
    }

    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

@keyframes remove-spin {
    to {
        transform: rotate(360deg);
    }
}

@media (max-width: 480px) {
    .remove-confirm-dialog {
        max-width: 360px;
    }

    .remove-dialog-content {
        padding: 20px 17px;
    }

    .remove-dialog-content h2 {
        font-size: 19px;
    }

    .remove-dialog-actions {
        gap: 8px;
    }

    .remove-dialog-actions button {
        padding: 10px 7px;
        font-size: 11px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .remove-confirm-dialog[open],
    .remove-spinner {
        animation-duration: .01ms;
    }
}
</style>