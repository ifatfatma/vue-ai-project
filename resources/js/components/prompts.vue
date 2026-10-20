<script setup>
import { computed, onMounted, ref } from 'vue';

const prompts = ref([]);
const categories = ref([]);
const loading = ref(false);
const error = ref('');
const searchQuery = ref('');
const currentPage = ref(1);
const lastPage = ref(1);
const selectedCategory = ref('All Prompts');
const savedPrompts = ref([]);
const copiedPrompt = ref(null);
const selectedPrompt = ref(null);
const variableValues = ref({});
const savingPrompts = ref([]);

function toast(message, type = 'success') {
    if (typeof window.showToast === 'function') window.showToast('vuePromptToast', message, type);
}

const selectedPromptVariables = computed(() => {
    const text = selectedPrompt.value?.prompt_text || '';
    return [...new Set([...text.matchAll(/\[([^\[\]]+)\]/g)].map(match => match[1].trim()).filter(Boolean))];
});

const filledSelectedPrompt = computed(() => {
    const text = selectedPrompt.value?.prompt_text || '';
    return text.replace(/\[([^\[\]]+)\]/g, (match, token) => {
        const key = token.trim();
        const value = variableValues.value[key];
        return value && value.trim() ? value.trim() : match;
    });
});

// Auth state is supplied by the Blade mount element.
const appElement = document.getElementById('app');
const isAuthenticated = appElement?.dataset.authenticated === 'true';
const loginUrl = appElement?.dataset.loginUrl || '/login';
const saveUrlTemplate = appElement?.dataset.saveUrlTemplate || '';

function requireLogin() {
    if (isAuthenticated) return true;

    // Send guests directly to the login page without a confirmation popup.
    window.location.href = loginUrl;
    return false;
}

const categoryNames = computed(() => [
    'All Prompts',
    ...categories.value.map(category => category.name),
]);

const filteredPrompts = computed(() => {
    let result = prompts.value;

    if (selectedCategory.value !== 'All Prompts') {
        result = result.filter(
            prompt => prompt.category?.name === selectedCategory.value
        );
    }

    const query = searchQuery.value.trim().toLowerCase();

    if (query) {
        result = result.filter(prompt =>
            [
                prompt.title,
                prompt.prompt_text,
                prompt.label,
                prompt.category?.name,
                prompt.ai_tool,
            ]
                .filter(Boolean)
                .some(value => String(value).toLowerCase().includes(query))
        );
    }

    return result;
});

async function loadPrompts(page = 1) {
    loading.value = true;
    error.value = '';

    try {
        const response = await fetch(`/api/prompts?page=${page}`, {
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error(`Unable to load prompts. Status: ${response.status}`);
        }

        const result = await response.json();

        if (result.success === false) {
            throw new Error(result.message || 'Unable to load prompts.');
        }

        prompts.value = Array.isArray(result.data) ? result.data : [];

        // API pagination is inside the pagination object.
        // Laravel's standard paginator puts these fields at the top level;
        // some APIs wrap them in `pagination`. Support both response shapes.
        const pagination = result.pagination || result.meta || result;
        currentPage.value = Number(
            pagination.current_page ?? pagination.currentPage ?? page
        );
        lastPage.value = Math.max(1, Number(
            pagination.last_page ?? pagination.lastPage ?? 1
        ));

        // Build categories from the prompts returned by the API.
        const categoryMap = new Map();

        prompts.value.forEach(prompt => {
            if (prompt.category?.name) {
                categoryMap.set(
                    prompt.category.name,
                    prompt.category
                );
            }
        });

        // Preserve categories from previous pages as well.
        categories.value = [
            ...new Map(
                [...categories.value, ...categoryMap.values()].map(category => [
                    category.name,
                    category,
                ])
            ).values(),
        ];
    } catch (err) {
        error.value = err.message || 'Something went wrong.';
    } finally {
        loading.value = false;
    }
}

function changePage(page) {
    if (page < 1 || page > lastPage.value || loading.value) {
        return;
    }

    loadPrompts(page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function setCategory(category) {
    selectedCategory.value = category;
}

async function toggleSave(prompt) {
    if (!requireLogin()) return;
    const id = prompt.id;
    if (id === undefined || id === null) {
        toast('This prompt has no valid ID.', 'error');
        return;
    }
    if (savingPrompts.value.includes(id)) return;

    if (!saveUrlTemplate) {
        toast('Unable to save this prompt. Save URL is not configured.', 'error');
        return;
    }

    savingPrompts.value.push(id);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const saveUrl = saveUrlTemplate.replace('__PROMPT_ID__', encodeURIComponent(id));

    try {
        const response = await fetch(saveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({}),
        });

        let data = {};
        try { data = await response.json(); } catch (_) {}

        if (response.status === 401 || response.redirected) {
            window.location.href = loginUrl;
            return;
        }
        if (!response.ok) throw new Error(data.message || 'Unable to update saved prompt.');

        const isSaved = data.saved === true || data.saved === 1 || data.saved === '1';
        const index = savedPrompts.value.indexOf(id);
        if (isSaved && index === -1) savedPrompts.value.push(id);
        if (!isSaved && index !== -1) savedPrompts.value.splice(index, 1);
        toast(isSaved ? 'Prompt saved successfully!' : 'Removed from saved prompts.', 'success');
    } catch (err) {
        toast(err.message || 'Unable to save this prompt. Please try again.', 'error');
    } finally {
        savingPrompts.value = savingPrompts.value.filter(savedId => savedId !== id);
    }
}

async function trackPromptCopy(promptId) {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    try {
        const response = await fetch(`/prompts/${encodeURIComponent(promptId)}/copy-track`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            // Clipboard copying should still succeed even if analytics tracking fails.
            console.warn('Prompt copy tracking failed:', response.status);
            return null;
        }

        return await response.json();
    } catch (err) {
        console.warn('Prompt copy tracking request failed:', err);
        return null;
    }
}

async function copyPrompt(prompt) {
    if (!requireLogin()) return;

    const text = prompt.id === selectedPrompt.value?.id
        ? filledSelectedPrompt.value
        : (prompt.prompt_text || '');

    if (!String(text).trim()) {
        toast('There is no prompt text to copy.', 'error');
        return;
    }

    try {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(text);
        } else {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.setAttribute('readonly', '');
            textarea.style.position = 'fixed';
            textarea.style.left = '-9999px';
            document.body.appendChild(textarea);
            textarea.select();
            const copied = document.execCommand('copy');
            textarea.remove();
            if (!copied) throw new Error('Clipboard unavailable');
        }

        copiedPrompt.value = prompt.id;
        toast('Prompt copied to clipboard!', 'success');

        // Keep copy tracking separate so a tracking error cannot undo a successful copy.
        void trackPromptCopy(prompt.id);

        window.setTimeout(() => {
            if (copiedPrompt.value === prompt.id) copiedPrompt.value = null;
        }, 1800);
    } catch (err) {
        console.error('Copy prompt failed:', err);
        toast('Unable to copy. Please try again.', 'error');
    }
}

async function sharePrompt(prompt) {
    const url = prompt.share_url || prompt.url || `${window.location.origin}/prompts/${encodeURIComponent(prompt.id)}`;

    if (navigator.share) {
        try {
            await navigator.share({
                title: prompt.title || 'AI Prompt',
                url,
            });
        } catch (err) {
            // User cancelled the native share sheet; no action is needed.
        }
        return;
    }

    try {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(url);
        } else {
            const textarea = document.createElement('textarea'); textarea.value = url; textarea.style.position = 'fixed'; textarea.style.left = '-9999px';
            document.body.appendChild(textarea); textarea.select(); const copied = document.execCommand('copy'); textarea.remove();
            if (!copied) throw new Error('Clipboard unavailable');
        }
        copiedPrompt.value = `share-${prompt.id}`;
        toast('Prompt link copied!', 'success');
        window.setTimeout(() => {
            if (copiedPrompt.value === `share-${prompt.id}`) {
                copiedPrompt.value = null;
            }
        }, 1800);
    } catch {
        toast('Unable to share this prompt from your browser.', 'error');
    }
}

function openPrompt(prompt) {
    selectedPrompt.value = prompt;
    variableValues.value = {};
}

function closePrompt() {
    selectedPrompt.value = null;
}

function imageUrl(prompt) {
    if (prompt.image_url) {
        return prompt.image_url;
    }

    if (prompt.image) {
        if (
            prompt.image.startsWith('http://') ||
            prompt.image.startsWith('https://') ||
            prompt.image.startsWith('/')
        ) {
            return prompt.image;
        }

        const storedPath = String(prompt.image).replace(/^\/+/, '');
        return storedPath.startsWith('storage/')
            ? `/${storedPath}`
            : `/storage/${storedPath}`;
    }

    return '';
}

function handleImageError(event) {
    event.target.style.display = 'none';
}

onMounted(() => {
    loadPrompts(1);
});
</script>

<template>
    <div class="prompt-hub">

        <!-- Compact page header: categories appear directly below the navbar -->
        <section class="prompts-section">
            <!-- Search -->
            <div class="prompt-search-wrap">
                <label class="sr-only" for="prompt-hub-search">Search prompts</label>
                <span class="prompt-search-icon" aria-hidden="true">⌕</span>
                <input
                    id="prompt-hub-search"
                    v-model="searchQuery"
                    class="prompt-search-input"
                    type="search"
                    placeholder="Search prompts, topics, or AI tools..."
                    autocomplete="off"
                />
                <button
                    v-if="searchQuery"
                    type="button"
                    class="prompt-search-clear"
                    aria-label="Clear search"
                    @click="searchQuery = ''"
                >
                    ×
                </button>
            </div>

            <!-- Category Filters -->
            <div class="category-list">
                <button
                    v-for="category in categoryNames"
                    :key="category"
                    type="button"
                    class="category-pill"
                    :class="{ active: selectedCategory === category }"
                    @click="setCategory(category)"
                >
                    {{ category }}
                </button>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="status-box">
                <div class="loader"></div>
                <p>Loading amazing prompts...</p>
            </div>

            <!-- Error -->
            <div v-else-if="error" class="status-box error-box">
                <div class="status-icon">!</div>
                <h3>Unable to load prompts</h3>
                <p>{{ error }}</p>
                <button class="retry-btn" @click="loadPrompts(currentPage)">
                    Try Again
                </button>
            </div>

            <!-- Empty State -->
            <div v-else-if="filteredPrompts.length === 0" class="status-box">
                <div class="empty-icon">⌕</div>
                <h3>No prompts found</h3>
                <p>
                    Try another search term or select a different category.
                </p>
                <button
                    class="retry-btn"
                    @click="searchQuery = ''; selectedCategory = 'All Prompts'"
                >
                    Clear Filters
                </button>
            </div>

            <!-- Prompt Cards -->
            <div v-else class="prompts-grid">
                <article
                    v-for="prompt in filteredPrompts"
                    :key="prompt.id"
                    class="prompt-card"
                >
                    <!-- Image -->
                    <div class="prompt-image-wrapper">
                        <img
                            v-if="imageUrl(prompt)"
                            :src="imageUrl(prompt)"
                            :alt="prompt.title || 'AI prompt image'"
                            class="prompt-image"
                            loading="lazy"
                            @error="handleImageError"
                        />

                        <div v-else class="image-placeholder">
                            <span>✦</span>
                            <small>AI Prompt</small>
                        </div>

                        <div class="image-overlay"></div>

                        <span v-if="prompt.category?.name" class="image-category">
                            {{ prompt.category.name }}
                        </span>

                        <span v-if="prompt.ai_tool" class="tool-badge">
                            {{ Array.isArray(prompt.ai_tool)
                                ? prompt.ai_tool.join(', ')
                                : prompt.ai_tool }}
                        </span>

                        <h3 class="image-title">
                            {{ prompt.title || 'Untitled Prompt' }}
                        </h3>

                        <button
                            type="button"
                            class="prompt-view-overlay"
                            :aria-label="`View ${prompt.title || 'prompt'}`"
                            @click="openPrompt(prompt)"
                        >
                            <span class="prompt-view-icon" aria-hidden="true">◎</span>
                            <span>View</span>
                        </button>
                    </div>

                    <!-- Compact action bar -->
                    <div class="prompt-card-body">
                        <div class="prompt-actions">
                            <button
                                type="button"
                                class="action-btn save-btn"
                                :class="{ saved: savedPrompts.includes(prompt.id) }"
                                :disabled="savingPrompts.includes(prompt.id)"
                                @click="toggleSave(prompt)"
                            >
                                <span aria-hidden="true">♧</span>
                                {{ savedPrompts.includes(prompt.id) ? 'Saved' : 'Save' }}
                            </button>

                            <button
                                type="button"
                                class="action-btn copy-btn"
                                @click="copyPrompt(prompt)"
                            >
                                <span aria-hidden="true">▢</span>
                                {{ copiedPrompt === prompt.id ? 'Copied' : 'Copy' }}
                            </button>

                            <button
                                type="button"
                                class="action-btn share-btn"
                                @click="sharePrompt(prompt)"
                            >
                                <span aria-hidden="true">♧</span>
                                Share
                            </button>
                        </div>
                    </div>
                </article>
            </div>

            <!-- Pagination -->
            <div
                v-if="!loading && !error && lastPage > 1"
                class="pagination"
            >
                <button
                    type="button"
                    class="page-btn"
                    :disabled="currentPage <= 1"
                    @click="changePage(currentPage - 1)"
                >
                    ← Previous
                </button>

                <button
                    v-for="page in lastPage"
                    :key="page"
                    type="button"
                    class="page-number"
                    :class="{ active: currentPage === page }"
                    @click="changePage(page)"
                >
                    {{ page }}
                </button>

                <button
                    type="button"
                    class="page-btn"
                    :disabled="currentPage >= lastPage"
                    @click="changePage(currentPage + 1)"
                >
                    Next →
                </button>
            </div>
        </section>

        <!-- Prompt Details Modal -->
        <div
            v-if="selectedPrompt"
            class="modal-backdrop"
            @click.self="closePrompt"
            @keydown.esc.window="closePrompt"
        >
            <div
                class="prompt-modal"
                role="dialog"
                aria-modal="true"
                :aria-label="selectedPrompt.title || 'Prompt details'"
            >
                <button
                    type="button"
                    class="modal-close"
                    aria-label="Close"
                    @click="closePrompt"
                >
                    ×
                </button>

                <img
                    v-if="imageUrl(selectedPrompt)"
                    :src="imageUrl(selectedPrompt)"
                    :alt="selectedPrompt.title"
                    class="modal-image"
                    @error="handleImageError"
                />

                <div class="modal-body">
                    <span v-if="selectedPrompt.category?.name" class="modal-category">
                        {{ selectedPrompt.category.name }}
                    </span>

                    <h2>{{ selectedPrompt.title }}</h2>

                    <div v-if="selectedPromptVariables.length" class="prompt-variable-fields">
                        <label v-for="variable in selectedPromptVariables" :key="variable" class="prompt-variable-field">
                            <span>{{ /^\d+$/.test(variable) ? `Variable ${variable}` : `Enter ${variable}` }}</span>
                            <input v-model="variableValues[variable]" type="text" :placeholder="variable" autocomplete="off">
                        </label>
                    </div>

                    <p class="modal-prompt-text">
                        {{ filledSelectedPrompt || 'No prompt text available.' }}
                    </p>

                    <div class="modal-actions">
                        <button
                            class="copy-btn modal-copy-btn"
                            @click="copyPrompt(selectedPrompt)"
                        >
                            {{ copiedPrompt === selectedPrompt.id ? '✓ Copied' : '▢ Copy Prompt' }}
                        </button>

                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

  <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #8b5cf6;
            --dark: #0f172a;
            --dark-2: #1e1b4b;
            --body-bg: #f4f7ff;
            --card-border: rgba(99, 102, 241, .10);
            --text-dark: #111827;
            --text-muted: #64748b;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
            width: 100%;
            max-width: 100%;
        }

        body {
            margin: 0;
            width: 100%;
            max-width: 100%;
            min-width: 320px;
            overflow-x: hidden;
            background: radial-gradient(circle at 10% 10%, rgba(99, 102, 241, .08), transparent 28%),
                radial-gradient(circle at 90% 20%, rgba(139, 92, 246, .07), transparent 25%), var(--body-bg);
            color: var(--text-dark);
            font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        img {
            max-width: 100%;
        }

        .modern-navbar {
            position: sticky;
            top: 0;
            z-index: 1030;
            background: rgba(15, 23, 42, .96);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, .08);
            box-shadow: 0 8px 30px rgba(15, 23, 42, .12);
        }

        .navbar-brand {
            color: #fff !important;
            font-weight: 800;
            letter-spacing: -.3px;
        }

        .brand-icon {
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            box-shadow: 0 6px 20px rgba(99, 102, 241, .35);
            margin-right: 10px;
        }

        .navbar-account {
            color: rgba(255, 255, 255, .9) !important;
            font-weight: 600;
            border-radius: 10px;
            padding: 8px 12px !important;
        }

        .navbar-account:hover {
            background: rgba(255, 255, 255, .08);
        }

        .navbar-account i {
            color: #a5b4fc;
        }

        .add-prompt-btn {
            border: 0;
            color: #fff;
            background: linear-gradient(135deg, #f59e0b, #f97316);
            border-radius: 10px;
            box-shadow: 0 6px 18px rgba(249, 115, 22, .22);
            transition: .2s ease;
            white-space: nowrap !important;
            min-width: 125px;
            width: auto !important;
        }

        .add-prompt-btn:hover {
            color: #fff;
            transform: translateY(-1px);
        }

        .navbar-toggler {
            background: rgba(255, 255, 255, .9);
        }

        /* ==========================================
   HERO SECTION STYLES (Optimized & Fixed)
   ================================          */
.hero-section {
    position: relative;
    overflow: hidden;
    color: #fff;
    padding: 78px 0 86px;
    background: radial-gradient(circle at 15% 20%, rgba(99, 102, 241, .3), transparent 30%),
        radial-gradient(circle at 85% 15%, rgba(139, 92, 246, .26), transparent 28%),
        linear-gradient(135deg, #0f172a 0%, #171a46 48%, #312e81 100%);
}

.hero-section::before,
.hero-section::after {
    content: "";
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
    filter: blur(12px);
}

.hero-section::before {
    width: 420px;
    height: 420px;
    right: -160px;
    top: -220px;
    background: rgba(139, 92, 246, .16);
}

.hero-section::after {
    width: 280px;
    height: 280px;
    left: -130px;
    bottom: -180px;
    background: rgba(59, 130, 246, .12);
}

.hero-content {
    position: relative;
    z-index: 2;
}

.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 7px 13px;
    border-radius: 999px;
    color: #c7d2fe;
    background: rgba(99, 102, 241, .15);
    border: 1px solid rgba(165, 180, 252, .22);
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 20px;
}

.hero-title {
    font-size: clamp(2.3rem, 5vw, 4.2rem);
    line-height: 1.08;
    font-weight: 850;
    letter-spacing: -2px;
    margin-bottom: 20px;
}

.hero-title span {
    background: linear-gradient(90deg, #a5b4fc, #c4b5fd, #93c5fd);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.hero-description {
    max-width: 700px;
    margin: 0 auto 30px;
    color: rgba(255, 255, 255, .75);
    font-size: 17px;
    line-height: 1.7;
}

/* ==========================================
   SEARCH WRAPPER & DROPDOWN FIXES
   ================================          */
.hero-section {
            position: relative;
            overflow: hidden;
            color: #fff;
            padding: 78px 0 86px;
            background: radial-gradient(circle at 15% 20%, rgba(99, 102, 241, .3), transparent 30%),
                radial-gradient(circle at 85% 15%, rgba(139, 92, 246, .26), transparent 28%),
                linear-gradient(135deg, #0f172a 0%, #171a46 48%, #312e81 100%);
        }

        .hero-section::before,
        .hero-section::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            filter: blur(12px);
        }

        .hero-section::before {
            width: 420px;
            height: 420px;
            right: -160px;
            top: -220px;
            background: rgba(139, 92, 246, .16);
        }

        .hero-section::after {
            width: 280px;
            height: 280px;
            left: -130px;
            bottom: -180px;
            background: rgba(59, 130, 246, .12);
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 13px;
            border-radius: 999px;
            color: #c7d2fe;
            background: rgba(99, 102, 241, .15);
            border: 1px solid rgba(165, 180, 252, .22);
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .hero-title {
            font-size: clamp(2.3rem, 5vw, 4.2rem);
            line-height: 1.08;
            font-weight: 850;
            letter-spacing: -2px;
            margin-bottom: 20px;
        }

        .hero-title span {
            background: linear-gradient(90deg, #a5b4fc, #c4b5fd, #93c5fd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-description {
            max-width: 700px;
            margin: 0 auto 30px;
            color: rgba(255, 255, 255, .75);
            font-size: 17px;
            line-height: 1.7;
        }

        .hero-search-wrapper {
            max-width: 850px;
            margin: auto;
            padding: 7px;
            border-radius: 18px;
            background: rgba(255, 255, 255, .1);
            border: 1px solid rgba(255, 255, 255, .14);
            box-shadow: 0 20px 50px rgba(0, 0, 0, .2);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .hero-search {
            display: flex;
            gap: 8px;
            padding: 5px;
            border-radius: 13px;
            background: #fff;
            align-items: center;
        }

        .hero-search-input {
            flex: 1;
            min-width: 0;
            border: 0;
            outline: 0;
            box-shadow: none !important;
            font-size: 15px;
            padding: 13px 15px;
            color: #111827;
        }

        .hero-search-input::placeholder {
            color: #94a3b8;
        }

        .btn-hero-search {
            border: 0;
            min-width: 120px;
            border-radius: 11px;
            color: #fff;
            font-weight: 700;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            transition: .2s ease;
            padding: 12px 20px;
        }

        .btn-hero-search:hover {
            color: #fff;
            transform: translateY(-1px);
        }

        #suggestionList {
            max-height: 300px;
            overflow-y: auto;
            border: 0;
            border-radius: 12px;
            margin-top: 8px !important;
            box-shadow: 0 15px 35px rgba(15, 23, 42, .18);
            z-index: 1060;
        }

        #suggestionList .list-group-item {
            border: 0;
            border-bottom: 1px solid #f1f5f9;
            padding: 12px 15px;
            transition: .15s;
        }

        #suggestionList .list-group-item:hover {
            background: #f5f3ff;
        }

        .main-content {
            width: 100%;
            max-width: 1320px;
            margin: 0 auto;
            padding: 38px 15px 40px;
        }

        .category-filter-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 9px;
            justify-content: center;
            align-items: center;
            width: 100%;
            margin-bottom: 32px;
        }

        .category-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 999px;
            border: 1px solid #dbe3f0;
            background: rgba(255, 255, 255, .85);
            color: #475569;
            font-size: 13px;
            font-weight: 650;
            transition: .2s ease;
        }

        .category-pill:hover {
            color: #4f46e5;
            border-color: #c7d2fe;
            background: #fff;
            transform: translateY(-1px);
        }

        .category-pill.active {
            color: #fff;
            border-color: transparent;
            background: linear-gradient(135deg, #6366f1, #7c3aed);
            box-shadow: 0 7px 18px rgba(99, 102, 241, .22);
        }

        .prompts-container {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 22px;
            align-items: start;
            width: 100%;
            max-width: 100%;
        }

        .prompt-card {
            position: relative;
            display: flex;
            flex-direction: column;
            width: 100%;
            min-width: 0;
            margin: 0;
            padding: 0;
            overflow: hidden;
            background: rgba(255, 255, 255, .96);
            border: 1px solid var(--card-border);
            border-radius: 18px;
            box-shadow: 0 8px 28px rgba(15, 23, 42, .07);
            transition: transform .22s, box-shadow .22s, border-color .22s;
        }

        .prompt-card:hover {
            transform: translateY(-4px);
            border-color: rgba(99, 102, 241, .2);
            box-shadow: 0 18px 40px rgba(15, 23, 42, .12);
        }

        .prompt-card-image-wrapper,
        .prompt-card-no-image {
            position: relative;
            width: 100%;
            height: 260px;
            overflow: hidden;
            cursor: pointer;
            background: #f8fafc;
        }

        .prompt-card-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform .35s, filter .35s;
        }

        .prompt-card-image-wrapper:hover .prompt-card-image {
            transform: scale(1.04);
            filter: brightness(.72);
        }

        .image-view-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 5px;
            color: #fff;
            background: rgba(15, 23, 42, .28);
            opacity: 0;
            transition: opacity .25s;
            font-size: 14px;
            font-weight: 700;
        }

        .image-view-overlay i {
            font-size: 27px;
        }

        .prompt-card-image-wrapper:hover .image-view-overlay,
        .prompt-card-image-wrapper:focus .image-view-overlay {
            opacity: 1;
        }

        .prompt-card-no-image {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 8px;
            color: #6366f1;
            background: linear-gradient(135deg, #f8fafc, #eef2ff);
            font-weight: 700;
        }

        .prompt-card-no-image i {
            font-size: 36px;
        }

        .prompt-card-actions {
            display: flex;
            align-items: stretch;
            gap: 8px;
            width: 100%;
            padding: 14px 12px;
            background: #fff;
        }

        .prompt-card-actions .prompt-action-btn {
            flex: 1 1 0;
            min-width: 0;
            min-height: 44px;
            border-radius: 9px;
            font-size: 12px;
            font-weight: 750;
            padding: 9px 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            white-space: normal;
        }

        .save-btn {
            color: #111827;
            border: 1px solid transparent;
            transition: .2s;
        }

        .save-btn.saved {
            background: #eef2ff;
            color: #6366f1;
            border-color: #c7d2fe;
        }

        .save-btn.saved:hover {
            background: #e0e7ff;
            color: #4f46e5;
        }

        .copy-btn {
            border: 0;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            box-shadow: 0 5px 13px rgba(16, 185, 129, .16);
        }

        .copy-btn:hover {
            color: #fff;
            transform: translateY(-1px);
        }

        .share-btn {
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            color: #4f46e5;
        }

        .share-btn:hover {
            background: #e0e7ff;
            color: #4338ca;
            transform: translateY(-1px);
        }

        .prompt-variables-box {
            margin: 0 0 20px;
            padding: 16px;
            background: linear-gradient(135deg, #f8fafc, #f5f3ff);
            border: 1px solid #e5e7eb;
            border-radius: 13px;
        }

        .prompt-variables-title {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 4px;
            color: #312e81;
            font-size: 14px;
            font-weight: 800;
        }

        .prompt-variable-hint {
            margin-bottom: 14px;
            color: #6b7280;
            font-size: 12px;
            line-height: 1.5;
        }

        .prompt-variable-field {
            margin-bottom: 12px;
        }

        .prompt-variable-field:last-of-type {
            margin-bottom: 0;
        }

        .prompt-variable-label {
            display: block;
            margin-bottom: 5px;
            color: #374151;
            font-size: 12px;
            font-weight: 750;
        }

        .prompt-variable-input {
            width: 100%;
            min-height: 42px;
            padding: 9px 12px;
            border: 1px solid #d1d5db;
            border-radius: 9px;
            background: #fff;
            color: #111827;
            font-size: 13px;
            outline: none;
        }

        .prompt-variable-input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, .1);
        }

        .prompt-variable-reset {
            margin-top: 12px;
            padding: 6px 10px;
            border: 0;
            border-radius: 7px;
            background: #fff;
            color: #6b7280;
            font-size: 11px;
            font-weight: 700;
        }

        .ai-tool-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: linear-gradient(135deg, #eef2ff, #f5f3ff);
            color: #5b21b6;
            border: 1px solid #ddd6fe;
            padding: 4px 8px;
            border-radius: 7px;
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
        }

        .modal-content {
            border: 1px solid rgba(99, 102, 241, .1) !important;
            box-shadow: 0 25px 70px rgba(15, 23, 42, .2) !important;
        }

        .modern-modal-header {
            background: linear-gradient(135deg, #0f172a, #312e81);
            color: #fff;
            border-radius: 16px 16px 0 0;
        }

        .modal-prompt-text {
            background: linear-gradient(135deg, #f8fafc, #f5f3ff);
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow-wrap: anywhere;
        }

        .modern-footer {
            margin-top: auto;
            background: linear-gradient(135deg, #0f172a, #171a46);
            color: rgba(255, 255, 255, .7);
            border-top: 1px solid rgba(255, 255, 255, .06);
        }

        .modern-footer a {
            color: #c4b5fd;
            text-decoration: none;
        }

        .modern-footer a:hover {
            color: #ddd6fe;
        }

        .copy-success {
            background: #047857 !important;
            color: #fff !important;
        }

        @media(max-width:1024px) {
            .prompts-container {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media(max-width:768px) {
            .hero-section {
                padding: 60px 0 68px;
            }

            .hero-title {
                letter-spacing: -1px;
            }

            .hero-description {
                font-size: 15px;
            }

            .navbar-nav {
                padding-top: 12px;
                padding-bottom: 10px;
                align-items: stretch !important;
            }
        }

        @media(max-width:640px) {
            .prompts-container {
                grid-template-columns: minmax(0, 1fr);
                gap: 18px;
            }

            .main-content {
                padding: 25px 14px 30px;
            }

            .prompt-card-image-wrapper,
            .prompt-card-no-image {
                height: 220px;
            }

            .prompt-card-actions {
                padding: 12px;
                gap: 8px;
            }

            .hero-search {
                flex-direction: column;
                align-items: stretch;
                padding: 6px;
            }

            .hero-search-input {
                width: 100%;
            }

            .btn-hero-search {
                width: 100%;
                min-height: 45px;
            }
        }

        @media(max-width:400px) {
            .prompt-card-actions .prompt-action-btn {
                font-size: 11px;
                padding: 8px 3px;
            }

            .category-filter-wrapper {
                gap: 6px;
            }

            .category-pill {
                padding: 7px 10px;
                font-size: 11px;
            }

            .hero-title {
                font-size: 2rem;
            }
        }


    
/* =========================================================
   ADD PROMPT MODAL - SCROLL FIX
   ========================================================= */

#addPromptModal .modal-dialog {
    max-height: calc(100vh - 30px);
    margin-top: 15px;
    margin-bottom: 15px;
}

#addPromptModal .modal-content {
    max-height: calc(100vh - 30px);
    height: auto;
    overflow: hidden;
}

/* Only the form content scrolls */
#addPromptModal form {
    min-height: 0;
    display: flex;
    flex-direction: column;
}

#addPromptModal .modal-body {
    overflow-y: auto !important;
    overflow-x: hidden;
    max-height: calc(100vh - 180px);
    min-height: 0;
}

/* Custom scrollbar */
#addPromptModal .modal-body::-webkit-scrollbar {
    width: 7px;
}

#addPromptModal .modal-body::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
}

#addPromptModal .modal-body::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

#addPromptModal .modal-body::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Firefox */
#addPromptModal .modal-body {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f1f5f9;
}

/* Mobile */
@media (max-width: 576px) {

    #addPromptModal .modal-dialog {
        max-height: calc(100vh - 20px);
        margin: 10px;
    }

    #addPromptModal .modal-content {
        max-height: calc(100vh - 20px);
    }

    #addPromptModal .modal-body {
        max-height: calc(100vh - 170px);
        padding: 1rem !important;
    }
}


/* =========================================================
   FRONTEND ADD PROMPT MODAL - MODERN FORM DESIGN
   ========================================================= */

.frontend-prompt-modal-dialog {
    max-height: calc(100vh - 30px);
    margin-top: 15px;
    margin-bottom: 15px;
}

.frontend-prompt-modal-content {
    max-height: calc(100vh - 30px);
    height: auto;
    overflow: hidden;
    border: 1px solid rgba(99, 102, 241, .12) !important;
    border-radius: 18px !important;
    background: #fff;
    box-shadow: 0 25px 70px rgba(15, 23, 42, .22) !important;
}


/* ---------------------------------------------------------
   HEADER
   --------------------------------------------------------- */

.frontend-prompt-modal-header {
    flex-shrink: 0;
    padding: 18px 22px;
    border: 0 !important;
    background: linear-gradient(135deg, #0f172a, #312e81);
    color: #fff;
    border-radius: 17px 17px 0 0 !important;
}

.frontend-prompt-header-icon {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border-radius: 12px;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    box-shadow: 0 7px 20px rgba(99, 102, 241, .35);
    font-size: 20px;
}

.frontend-prompt-modal-title {
    font-size: 17px;
    font-weight: 800;
    letter-spacing: -.2px;
}

.frontend-prompt-modal-subtitle {
    color: rgba(255, 255, 255, .62);
    font-size: 11px;
}

.frontend-prompt-close {
    opacity: .9;
}

.frontend-prompt-close:hover {
    opacity: 1;
}


/* ---------------------------------------------------------
   SCROLLABLE BODY
   --------------------------------------------------------- */

.frontend-prompt-modal-body {
    overflow-y: auto !important;
    overflow-x: hidden;
    max-height: calc(100vh - 185px);
    min-height: 0;
    padding: 22px !important;
    background: #f8fafc;
}


/* Custom scrollbar */

.frontend-prompt-modal-body::-webkit-scrollbar {
    width: 7px;
}

.frontend-prompt-modal-body::-webkit-scrollbar-track {
    background: #eef2f7;
    border-radius: 10px;
}

.frontend-prompt-modal-body::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

.frontend-prompt-modal-body::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.frontend-prompt-modal-body {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #eef2f7;
}


/* ---------------------------------------------------------
   FORM SECTIONS
   --------------------------------------------------------- */

.frontend-form-section {
    padding: 18px;
    margin-bottom: 16px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    box-shadow: 0 4px 16px rgba(15, 23, 42, .035);
}

.frontend-form-section:last-child {
    margin-bottom: 0;
}

.frontend-form-section-heading {
    display: flex;
    align-items: center;
    gap: 11px;
    padding-bottom: 14px;
    margin-bottom: 17px;
    border-bottom: 1px solid #eef2f7;
}

.frontend-section-icon {
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border-radius: 10px;
    background: linear-gradient(135deg, #eef2ff, #f5f3ff);
    color: #6366f1;
    border: 1px solid #e0e7ff;
}

.frontend-form-section-heading h6 {
    color: #1e293b;
    font-size: 14px;
    font-weight: 800;
}

.frontend-form-section-heading p {
    color: #94a3b8;
    font-size: 11px;
}


/* ---------------------------------------------------------
   FORM FIELDS
   --------------------------------------------------------- */

.frontend-form-group {
    margin-bottom: 17px;
}

.frontend-form-label {
    display: block;
    margin-bottom: 7px;
    color: #374151;
    font-size: 12px;
    font-weight: 750;
}

.frontend-optional-label {
    display: inline-block;
    margin-left: 5px;
    padding: 2px 7px;
    border-radius: 999px;
    background: #f1f5f9;
    color: #64748b;
    font-size: 10px;
    font-weight: 700;
}

.frontend-form-control {
    min-height: 46px;
    border: 1px solid #dbe3ef !important;
    border-radius: 10px !important;
    background: #fff !important;
    color: #1e293b;
    font-size: 13px;
    box-shadow: none !important;
    transition: border-color .2s, box-shadow .2s;
}

.frontend-form-control:focus {
    border-color: #818cf8 !important;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, .10) !important;
}

.frontend-form-control::placeholder,
.frontend-prompt-textarea::placeholder {
    color: #94a3b8;
}


/* ---------------------------------------------------------
   PROMPT TEXTAREA
   --------------------------------------------------------- */

.frontend-prompt-textarea {
    min-height: 170px;
    resize: vertical;
    padding: 13px 14px;
    border: 1px solid #dbe3ef !important;
    border-radius: 11px !important;
    background: #fff !important;
    color: #1e293b;
    font-size: 13px;
    line-height: 1.65;
    box-shadow: none !important;
}

.frontend-prompt-textarea:focus {
    border-color: #818cf8 !important;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, .10) !important;
}

.frontend-prompt-hint {
    margin-top: 7px;
    color: #64748b;
    font-size: 11px;
    line-height: 1.5;
}

.frontend-prompt-hint i {
    color: #f59e0b;
}


/* ---------------------------------------------------------
   AI TOOLS DROPDOWN
   --------------------------------------------------------- */

.frontend-ai-tools-dropdown {
    position: relative;
    width: 100%;
}

.frontend-ai-tools-dropdown-btn {
    width: 100%;
    min-height: 46px;
    padding: 10px 13px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    border: 1px solid #dbe3ef;
    border-radius: 10px;
    background: #fff;
    color: #64748b;
    font-size: 13px;
    text-align: left;
    cursor: pointer;
    transition: border-color .2s, box-shadow .2s;
}

.frontend-ai-tools-dropdown-btn:hover,
.frontend-ai-tools-dropdown-btn[aria-expanded="true"] {
    border-color: #818cf8;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, .08);
}

.frontend-ai-tools-dropdown-btn i {
    color: #64748b;
    transition: transform .2s;
}

.frontend-ai-tools-dropdown-btn[aria-expanded="true"] i {
    transform: rotate(180deg);
}

.frontend-ai-tools-dropdown-menu {
    display: none;
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    right: 0;
    z-index: 1055;
    max-height: 250px;
    overflow-y: auto;
    padding: 6px 0;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 11px;
    box-shadow: 0 18px 40px rgba(15, 23, 42, .15);
}

.frontend-ai-tools-dropdown-menu::-webkit-scrollbar {
    width: 6px;
}

.frontend-ai-tools-dropdown-menu::-webkit-scrollbar-track {
    background: #f8fafc;
}

.frontend-ai-tools-dropdown-menu::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

.frontend-ai-tool-option {
    position: relative;
    display: flex;
    align-items: center;
    gap: 9px;
    min-height: 42px;
    margin: 0;
    padding: 8px 13px;
    cursor: pointer;
    color: #334155;
    font-size: 13px;
    transition: background .15s;
}

.frontend-ai-tool-option:hover {
    background: #f8f7ff;
}

.frontend-ai-tool-option input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.frontend-ai-tool-check {
    width: 18px;
    height: 18px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border: 1px solid #cbd5e1;
    border-radius: 5px;
    background: #fff;
    color: transparent;
    font-size: 11px;
    transition: .15s;
}

.frontend-ai-tool-option input:checked + .frontend-ai-tool-check {
    border-color: #6366f1;
    background: linear-gradient(135deg, #6366f1, #7c3aed);
    color: #fff;
}

.frontend-ai-tool-name {
    font-weight: 600;
}

.frontend-field-hint {
    display: block;
    margin-top: 7px;
    color: #94a3b8;
    font-size: 10px;
}


/* ---------------------------------------------------------
   IMAGE UPLOAD
   --------------------------------------------------------- */

.frontend-image-upload-wrapper {
    position: relative;
    min-height: 82px;
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 14px;
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
    background: linear-gradient(135deg, #f8fafc, #fafaff);
    cursor: pointer;
    transition: border-color .2s, background .2s;
}

.frontend-image-upload-wrapper:hover {
    border-color: #818cf8;
    background: linear-gradient(135deg, #f8faff, #f5f3ff);
}

.frontend-image-upload-icon {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border-radius: 11px;
    background: #eef2ff;
    color: #6366f1;
    font-size: 20px;
}

.frontend-image-upload-content {
    min-width: 0;
}

.frontend-image-upload-title {
    color: #334155;
    font-size: 12px;
    font-weight: 750;
}

.frontend-image-upload-text {
    margin-top: 3px;
    color: #94a3b8;
    font-size: 10px;
}

.frontend-image-input {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}


/* Prompt image upload */
.frontend-image-upload-wrapper {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 22px;
    border: 2px dashed #cbd5e1;
    border-radius: 14px;
    background: #f8fafc;
    cursor: pointer;
    transition: all 0.25s ease;
}

.frontend-image-upload-wrapper:hover {
    border-color: #6366f1;
    background: #f1f5ff;
}

.frontend-image-upload-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    width: 52px;
    height: 52px;
    border-radius: 12px;
    background: #e0e7ff;
    color: #4f46e5;
    font-size: 27px;
}

.frontend-image-upload-title {
    color: #1e293b;
    font-size: 15px;
    font-weight: 700;
}

.frontend-image-upload-text {
    margin-top: 4px;
    color: #64748b;
    font-size: 13px;
}

/* Selected image preview */
.frontend-image-preview-box {
    margin-top: 14px;
    padding: 16px;
    border: 1px solid #dbe3ef;
    border-radius: 14px;
    background: #ffffff;
    overflow: hidden;
}

.frontend-image-preview-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 12px;
    color: #334155;
    font-size: 14px;
}

.frontend-image-preview-badge {
    padding: 5px 9px;
    border-radius: 20px;
    background: #dcfce7;
    color: #166534;
    font-size: 11px;
    font-weight: 600;
}

#imagePreview {
    display: block;
    width: 100%;
    max-height: 260px;
    object-fit: contain;
    border-radius: 10px;
    background: #f1f5f9;
}

.frontend-image-preview-details {
    margin-top: 10px;
}

.frontend-image-file-name {
    overflow-wrap: anywhere;
    color: #334155;
    font-size: 13px;
    font-weight: 600;
}

.frontend-image-file-size {
    margin-top: 3px;
    color: #64748b;
    font-size: 12px;
}

.frontend-image-preview-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 14px;
}

.frontend-image-preview-actions label {
    cursor: pointer;
}

@media (max-width: 480px) {
    .frontend-image-upload-wrapper {
        padding: 16px;
        gap: 12px;
    }

    .frontend-image-upload-icon {
        width: 44px;
        height: 44px;
        font-size: 23px;
    }

    .frontend-image-preview-actions > * {
        flex: 1 1 auto;
        text-align: center;
    }
}



/* ---------------------------------------------------------
   FOOTER
   --------------------------------------------------------- */

.frontend-prompt-modal-footer {
    flex-shrink: 0;
    display: flex;
    justify-content: flex-end;
    gap: 9px;
    padding: 14px 20px;
    border-top: 1px solid #e5e7eb !important;
    background: #fff;
}

.frontend-cancel-btn {
    min-height: 42px;
    padding: 9px 17px;
    border: 1px solid #dbe3ef;
    border-radius: 9px;
    background: #fff;
    color: #475569;
    font-size: 12px;
    font-weight: 700;
}

.frontend-cancel-btn:hover {
    background: #f8fafc;
    color: #1e293b;
    border-color: #cbd5e1;
}

.frontend-submit-btn {
    min-height: 42px;
    padding: 9px 19px;
    border: 0;
    border-radius: 9px;
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    color: #fff;
    font-size: 12px;
    font-weight: 750;
    box-shadow: 0 6px 16px rgba(99, 102, 241, .20);
    transition: .2s ease;
}

.frontend-submit-btn:hover {
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 9px 20px rgba(99, 102, 241, .25);
}


/* ---------------------------------------------------------
   VALIDATION
   --------------------------------------------------------- */

.frontend-form-control.is-invalid,
.frontend-prompt-textarea.is-invalid {
    border-color: #ef4444 !important;
}

.frontend-form-control.is-invalid:focus,
.frontend-prompt-textarea.is-invalid:focus {
    box-shadow: 0 0 0 3px rgba(239, 68, 68, .10) !important;
}


/* ---------------------------------------------------------
   MOBILE
   --------------------------------------------------------- */

@media (max-width: 576px) {

    .frontend-prompt-modal-dialog {
        max-height: calc(100vh - 20px);
        margin: 10px;
    }

    .frontend-prompt-modal-content {
        max-height: calc(100vh - 20px);
        border-radius: 15px !important;
    }

    .frontend-prompt-modal-header {
        padding: 15px;
    }

    .frontend-prompt-header-icon {
        width: 37px;
        height: 37px;
        border-radius: 10px;
        font-size: 17px;
    }

    .frontend-prompt-modal-title {
        font-size: 15px;
    }

    .frontend-prompt-modal-subtitle {
        display: none;
    }

    .frontend-prompt-modal-body {
        max-height: calc(100vh - 165px);
        padding: 14px !important;
    }

    .frontend-form-section {
        padding: 14px;
        margin-bottom: 12px;
    }

    .frontend-form-section-heading {
        margin-bottom: 14px;
        padding-bottom: 12px;
    }

    .frontend-section-icon {
        width: 32px;
        height: 32px;
    }

    .frontend-prompt-textarea {
        min-height: 150px;
    }

    .frontend-prompt-modal-footer {
        padding: 12px 14px;
    }

    .frontend-cancel-btn,
    .frontend-submit-btn {
        min-height: 40px;
        padding: 8px 13px;
    }
}






/* =========================================================
   GLOBAL RIGHT-SIDE TOAST NOTIFICATIONS
   ========================================================= */
#toastContainer {
    position: fixed;
    top: 90px;
    right: 22px;
    z-index: 20000;
    width: min(390px, calc(100vw - 28px));
    display: flex;
    flex-direction: column;
    gap: 12px;
    pointer-events: none;
}
.app-toast {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 15px 16px;
    background: #fff;
    color: #1e293b;
    border: 1px solid #e2e8f0;
    border-left: 4px solid #10b981;
    border-radius: 13px;
    box-shadow: 0 12px 35px rgba(15, 23, 42, .16);
    pointer-events: auto;
    opacity: 0;
    transform: translateX(25px);
    transition: opacity .25s ease, transform .25s ease;
    overflow-wrap: anywhere;
}
.app-toast.show { opacity: 1; transform: translateX(0); }
.app-toast.toast-error { border-left-color: #ef4444; }
.app-toast.toast-warning { border-left-color: #f59e0b; }
.app-toast.toast-info { border-left-color: #3b82f6; }
.app-toast-icon { font-size: 21px; line-height: 1.2; flex-shrink: 0; color: #10b981; }
.toast-error .app-toast-icon { color: #ef4444; }
.toast-warning .app-toast-icon { color: #f59e0b; }
.toast-info .app-toast-icon { color: #3b82f6; }
.app-toast-content { flex: 1; min-width: 0; font-size: 13px; font-weight: 600; line-height: 1.55; }
.app-toast-close { padding: 0; border: 0; background: transparent; color: #94a3b8; font-size: 19px; line-height: 1; cursor: pointer; }
.app-toast-close:hover { color: #1e293b; }
@media (max-width: 576px) {
    #toastContainer { top: 76px; right: 12px; width: calc(100vw - 24px); }
    .app-toast { padding: 13px; }
}



        /* Prompt Hub component styles (matching the template class names) */
        .prompt-hub { width: 100%; min-width: 0; }
        .prompts-section { width: 100%; max-width: 1320px; margin: 0 auto; padding: 24px 16px 42px; }
        .prompt-search-wrap { position: relative; display: flex; align-items: center; gap: 10px; width: min(100%, 760px); margin: 0 auto 22px; padding: 5px 12px; border: 1px solid #dbe3f0; border-radius: 14px; background: #fff; box-shadow: 0 5px 18px rgba(15,23,42,.05); }
        .prompt-search-icon { color: #64748b; font-size: 24px; line-height: 1; }
        .prompt-search-input { width: 100%; min-width: 0; padding: 10px 2px; border: 0; outline: 0; background: transparent; color: #111827; font: inherit; }
        .prompt-search-input:focus { outline: none; box-shadow: none; }
        .prompt-search-clear { border: 0; border-radius: 50%; width: 30px; height: 30px; flex: 0 0 30px; background: #f1f5f9; color: #475569; font-size: 20px; cursor: pointer; }
        .category-list { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 9px; margin: 0 0 26px; }
        .prompts-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 22px; align-items: stretch; width: 100%; }
        .prompt-image-wrapper { position: relative; width: 100%; height: 260px; overflow: hidden; background: linear-gradient(135deg,#f8fafc,#eef2ff); }
        .prompt-image { display: block; width: 100%; height: 100%; object-fit: cover; transition: transform .3s ease, filter .3s ease; }
        .prompt-card:hover .prompt-image { transform: scale(1.035); filter: brightness(.78); }
        .image-placeholder { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; color: #6366f1; background: linear-gradient(135deg,#f8fafc,#eef2ff); font-weight: 700; }
        .image-placeholder span { font-size: 40px; }
        .image-overlay { position: absolute; inset: 0; pointer-events: none; background: linear-gradient(180deg,rgba(15,23,42,.05) 20%,rgba(15,23,42,.78) 100%); }
        .image-category, .tool-badge { position: absolute; z-index: 1; top: 12px; max-width: 48%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; padding: 6px 9px; border-radius: 999px; font-size: 11px; font-weight: 750; }
        .image-category { left: 12px; color: #3730a3; background: rgba(238,242,255,.96); }
        .tool-badge { right: 12px; color: #fff; background: rgba(15,23,42,.78); }
        .image-title { position: absolute; z-index: 1; left: 16px; right: 16px; bottom: 18px; margin: 0; color: #fff; font-size: 19px; line-height: 1.35; font-weight: 800; text-shadow: 0 2px 8px rgba(0,0,0,.25); }
        .prompt-view-overlay { position: absolute; z-index: 2; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 5px; border: 0; background: rgba(15,23,42,.35); color: #fff; font-weight: 750; opacity: 0; transition: opacity .2s ease; cursor: pointer; }
        .prompt-image-wrapper:hover .prompt-view-overlay, .prompt-view-overlay:focus-visible { opacity: 1; }
        .prompt-view-icon { font-size: 30px; }
        .prompt-card-body { padding: 12px; background: #fff; }
        .prompt-actions { display: flex; gap: 8px; width: 100%; }
        .action-btn { display: inline-flex; align-items: center; justify-content: center; gap: 5px; flex: 1 1 0; min-width: 0; min-height: 42px; padding: 8px 6px; border: 1px solid #dbe3f0; border-radius: 9px; background: #fff; color: #334155; font-size: 12px; font-weight: 750; cursor: pointer; transition: .2s ease; }
        .action-btn:hover { transform: translateY(-1px); }
        .action-btn:disabled { opacity: .55; cursor: wait; transform: none; }
        .action-btn.save-btn.saved { color: #4f46e5; background: #eef2ff; border-color: #c7d2fe; }
        .action-btn.copy-btn, .modal-copy-btn { color: #fff; background: linear-gradient(135deg,#10b981,#059669); border-color: transparent; }
        .action-btn.share-btn { color: #4f46e5; background: #eef2ff; border-color: #c7d2fe; }
        .status-box { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; min-height: 220px; padding: 28px 18px; text-align: center; color: #475569; background: rgba(255,255,255,.8); border: 1px solid #e2e8f0; border-radius: 16px; }
        .status-box h3, .status-box p { margin: 0; }
        .status-icon, .empty-icon { display: grid; place-items: center; width: 44px; height: 44px; border-radius: 50%; background: #eef2ff; color: #4f46e5; font-size: 24px; font-weight: 800; }
        .error-box .status-icon { color: #dc2626; background: #fef2f2; }
        .loader { width: 32px; height: 32px; border: 3px solid #e0e7ff; border-top-color: #6366f1; border-radius: 50%; animation: prompt-hub-spin .8s linear infinite; }
        @keyframes prompt-hub-spin { to { transform: rotate(360deg); } }
        .retry-btn { padding: 9px 15px; border: 0; border-radius: 9px; color: #fff; background: #4f46e5; font-weight: 700; cursor: pointer; }
        .pagination { display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: 8px; margin-top: 28px; }
        .page-btn, .page-number { min-height: 38px; padding: 7px 12px; border: 1px solid #dbe3f0; border-radius: 9px; background: #fff; color: #334155; font-weight: 700; cursor: pointer; }
        .page-number.active { color: #fff; border-color: #4f46e5; background: #4f46e5; }
        .page-btn:disabled { opacity: .45; cursor: not-allowed; }
        .modal-backdrop { position: fixed; z-index: 11000; inset: 0; display: flex; align-items: center; justify-content: center; padding: 20px; overflow-y: auto; background: rgba(15,23,42,.72); backdrop-filter: blur(5px); }
        .prompt-modal { position: relative; width: min(100%, 760px); max-height: calc(100vh - 40px); overflow-y: auto; border-radius: 18px; background: #fff; box-shadow: 0 25px 80px rgba(0,0,0,.3); }
        .modal-close { position: absolute; z-index: 3; top: 12px; right: 12px; width: 38px; height: 38px; border: 0; border-radius: 50%; background: rgba(15,23,42,.75); color: #fff; font-size: 27px; line-height: 1; cursor: pointer; }
        .modal-image { display: block; width: 100%; max-height: 300px; object-fit: cover; }
        .modal-body { padding: 24px; }
        .modal-body h2 { margin: 10px 0 18px; color: #111827; font-size: clamp(22px,3vw,30px); line-height: 1.25; }
        .modal-category { display: inline-block; padding: 5px 10px; border-radius: 999px; color: #4338ca; background: #eef2ff; font-size: 12px; font-weight: 750; }
        .prompt-variable-fields { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 12px; margin-bottom: 18px; }
        .prompt-variable-field { display: flex; flex-direction: column; gap: 6px; color: #475569; font-size: 12px; font-weight: 700; }
        .prompt-variable-field input { width: 100%; min-width: 0; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 9px; font: inherit; font-weight: 400; }
        .modal-prompt-text { white-space: pre-wrap; overflow-wrap: anywhere; padding: 16px; border: 1px solid #e2e8f0; border-radius: 12px; background: #f8fafc; color: #334155; line-height: 1.7; }
        .modal-actions { display: flex; justify-content: flex-end; margin-top: 16px; }
        .modal-copy-btn { min-height: 42px; padding: 10px 16px; border: 0; border-radius: 9px; font-weight: 750; cursor: pointer; }
        .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0; }
        @media (max-width: 900px) { .prompts-grid { grid-template-columns: repeat(2,minmax(0,1fr)); gap: 16px; } .prompt-image-wrapper { height: 230px; } }
        @media (max-width: 580px) { .prompts-section { padding: 18px 12px 30px; } .prompts-grid { grid-template-columns: minmax(0,1fr); gap: 16px; } .prompt-image-wrapper { height: 240px; } .prompt-variable-fields { grid-template-columns: minmax(0,1fr); } .modal-backdrop { padding: 10px; } .prompt-modal { max-height: calc(100vh - 20px); } .modal-body { padding: 18px; } .action-btn { font-size: 12px; } }

    </style>