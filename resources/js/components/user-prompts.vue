<script setup>
import { computed, onMounted, onBeforeUnmount, ref, watch } from 'vue';

const props = defineProps({
    initialPrompts: { type: Array, default: () => [] },
    currentPage: { type: Number, default: 1 },
    lastPage: { type: Number, default: 1 },
    total: { type: Number, default: 0 },
    perPage: { type: Number, default: 12 },
    search: { type: String, default: '' },
    successMessage: { type: String, default: '' },
    homeUrl: { type: String, required: true },
    promptsUrl: { type: String, required: true },
    suggestionsUrl: { type: String, required: true },
    editUrlTemplate: { type: String, required: true },
    deleteUrlTemplate: { type: String, required: true },
});

const prompts = ref(props.initialPrompts);
const searchQuery = ref(props.search);
const suggestions = ref([]);
const showSuggestions = ref(false);
const alertVisible = ref(Boolean(props.successMessage));

const deleteDialog = ref(null);
const deletingPrompt = ref(null);
const deleting = ref(false);

let suggestionTimer;
let requestController;
let alertTimer;

const startNumber = computed(() =>
    props.total === 0 ? 0 : (props.currentPage - 1) * props.perPage + 1
);

const endNumber = computed(() =>
    Math.min(props.currentPage * props.perPage, props.total)
);

const pages = computed(() => {
    const start = Math.max(1, props.currentPage - 2);
    const end = Math.min(props.lastPage, props.currentPage + 2);

    return Array.from(
        { length: Math.max(0, end - start + 1) },
        (_, index) => start + index
    );
});

function editUrl(prompt) {
    return props.editUrlTemplate.replace(
        '__PROMPT_ID__',
        encodeURIComponent(prompt.id)
    );
}

function deleteUrl(prompt) {
    return props.deleteUrlTemplate.replace(
        '__PROMPT_ID__',
        encodeURIComponent(prompt.id)
    );
}

function categoryName(prompt) {
    return prompt.category?.name || 'General';
}

function statusLabel(status) {
    if (status === 'approved') return 'Approved';
    if (status === 'pending') return 'Pending Approval';
    return 'Rejected';
}

function statusClass(status) {
    if (status === 'approved') return 'bg-success';
    if (status === 'pending') return 'bg-warning text-dark';
    return 'bg-danger';
}

function ratingValue(prompt) {
    const value = Number(prompt.rating ?? 0);

    return Number.isFinite(value)
        ? Math.max(0, Math.min(5, Math.round(value)))
        : 0;
}

function formattedDate(value) {
    if (!value) return '—';

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) return '—';

    return new Intl.DateTimeFormat('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(date);
}

function searchPrompts() {
    showSuggestions.value = false;

    const url = new URL(props.promptsUrl, window.location.origin);
    const query = searchQuery.value.trim();

    if (query) {
        url.searchParams.set('search', query);
    } else {
        url.searchParams.delete('search');
    }

    url.searchParams.delete('page');

    window.location.href = url.toString();
}

function clearSearch() {
    searchQuery.value = '';
    suggestions.value = [];
    showSuggestions.value = false;

    if (requestController) {
        requestController.abort();
    }

    window.location.href = props.promptsUrl;
}

function selectPrompt(title) {
    searchQuery.value = title;
    showSuggestions.value = false;
}

async function fetchSuggestions(query) {
    if (requestController) {
        requestController.abort();
    }

    requestController = new AbortController();
    const controller = requestController;

    try {
        const url = new URL(props.suggestionsUrl, window.location.origin);
        url.searchParams.set('query', query);

        const response = await fetch(url, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            signal: controller.signal,
        });

        if (!response.ok) {
            throw new Error('Unable to load suggestions.');
        }

        const data = await response.json();

        if (requestController !== controller) return;

        suggestions.value = Array.isArray(data) ? data : [];
        showSuggestions.value = suggestions.value.length > 0;
    } catch (error) {
        if (error.name !== 'AbortError') {
            suggestions.value = [];
            showSuggestions.value = false;
            console.error(error);
        }
    }
}

watch(searchQuery, (value) => {
    clearTimeout(suggestionTimer);

    const query = value.trim();

    if (query.length < 2) {
        if (requestController) {
            requestController.abort();
        }

        suggestions.value = [];
        showSuggestions.value = false;
        return;
    }

    suggestionTimer = setTimeout(
        () => fetchSuggestions(query),
        250
    );
});

function pageUrl(page) {
    const url = new URL(props.promptsUrl, window.location.origin);
    const query = searchQuery.value.trim() || props.search;

    url.searchParams.set('page', page);

    if (query) {
        url.searchParams.set('search', query);
    }

    return url.pathname + url.search;
}

function handleOutsideClick(event) {
    if (!event.target.closest('.user-prompt-search')) {
        showSuggestions.value = false;
    }
}

function hideSuccessAlert() {
    alertVisible.value = false;
}

/*
|--------------------------------------------------------------------------
| Modern Delete Confirmation Dialog
|--------------------------------------------------------------------------
*/

function confirmDelete(prompt) {
    if (deleting.value) return;

    deletingPrompt.value = prompt;

    if (deleteDialog.value && !deleteDialog.value.open) {
        deleteDialog.value.showModal();
    }
}

function closeDeleteDialog() {
    if (deleting.value) return;

    if (deleteDialog.value?.open) {
        deleteDialog.value.close();
    }

    deletingPrompt.value = null;
}

function movePromptToTrash() {
    if (!deletingPrompt.value || deleting.value) return;

    deleting.value = true;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = deleteUrl(deletingPrompt.value);

    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value =
        document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    const method = document.createElement('input');
    method.type = 'hidden';
    method.name = '_method';
    method.value = 'DELETE';

    form.append(csrf, method);
    document.body.appendChild(form);
    form.submit();
}

onMounted(() => {
    document.addEventListener('click', handleOutsideClick);

    if (props.successMessage) {
        alertTimer = setTimeout(hideSuccessAlert, 1000);
    }
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleOutsideClick);

    clearTimeout(suggestionTimer);
    clearTimeout(alertTimer);

    if (requestController) {
        requestController.abort();
    }
});
</script>

<template>
    <div class="user-prompts-page">
        <!-- Navbar -->
        <header class="site-navbar">
            <a :href="homeUrl" class="brand-title">
                AI Prompt Hub
            </a>

            <a :href="homeUrl" class="back-home-btn">
                <i class="bi bi-arrow-left me-2"></i>
                Back to Home
            </a>
        </header>

        <main class="page-content">
            <!-- Hero Section -->
            <section class="hero-section text-center">
                <div class="container">
                    <h1 class="fw-bold display-6 mb-2">
                        My Submitted Prompts Lists
                    </h1>

                    <p class="lead text-muted mb-4">
                        Manage your custom prompts, track their approval
                        status, and update them.
                    </p>

                    <!-- Search -->
                    <div class="row justify-content-center position-relative">
                        <div class="col-md-6">
                            <form
                                class="input-group shadow-sm user-prompt-search"
                                @submit.prevent="searchPrompts"
                            >
                                <input
                                    v-model="searchQuery"
                                    id="searchBox"
                                    type="text"
                                    name="search"
                                    class="form-control border-0 py-2"
                                    placeholder="Search your prompts by title..."
                                    autocomplete="off"
                                    @focus="
                                        showSuggestions =
                                            suggestions.length > 0
                                    "
                                />

                                <button
                                    class="btn btn-primary px-4"
                                    type="submit"
                                >
                                    <i class="bi bi-search"></i>
                                    Search
                                </button>

                                <button
                                    v-if="searchQuery || search"
                                    type="button"
                                    class="btn btn-secondary px-3"
                                    @click="clearSearch"
                                >
                                    <i class="bi bi-x-lg"></i>
                                </button>

                                <!-- Search Suggestions -->
                                <ul
                                    v-if="
                                        showSuggestions &&
                                        suggestions.length
                                    "
                                    class="list-group user-prompt-suggestions shadow-sm text-start"
                                >
                                    <li
                                        v-for="(item, index) in suggestions"
                                        :key="
                                            item.id ??
                                            `${item.title}-${index}`
                                        "
                                        class="list-group-item list-group-item-action text-dark"
                                        @click="
                                            selectPrompt(
                                                typeof item === 'string'
                                                    ? item
                                                    : item.title
                                            )
                                        "
                                    >
                                        {{
                                            typeof item === 'string'
                                                ? item
                                                : item.title
                                        }}
                                    </li>
                                </ul>
                            </form>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Prompts Table -->
            <div class="container mb-5 flex-grow-1">
                <!-- Success Message -->
                <div
                    v-if="alertVisible && successMessage"
                    class="alert alert-success alert-dismissible fade show"
                    role="alert"
                >
                    {{ successMessage }}

                    <button
                        type="button"
                        class="btn-close"
                        aria-label="Close"
                        @click="hideSuccessAlert"
                    ></button>
                </div>

                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="py-3 ps-4">#</th>
                                        <th class="py-3">Title</th>
                                        <th class="py-3">Category</th>
                                        <th class="py-3">Status</th>
                                        <th class="py-3">Ratings</th>
                                        <th class="py-3">Created Date</th>
                                        <th class="py-3 text-end pe-4">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr
                                        v-for="(prompt, index) in prompts"
                                        :key="prompt.id"
                                    >
                                        <td class="ps-4 fw-semibold">
                                            {{
                                                (currentPage - 1) * perPage +
                                                index +
                                                1
                                            }}
                                        </td>

                                        <td>
                                            <span class="fw-bold text-dark">
                                                {{ prompt.title }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ categoryName(prompt) }}
                                            </span>
                                        </td>

                                        <td>
                                            <span
                                                class="badge px-2 py-1"
                                                :class="
                                                    statusClass(prompt.status)
                                                "
                                            >
                                                {{
                                                    statusLabel(prompt.status)
                                                }}
                                            </span>
                                        </td>

                                        <td>
                                            <div
                                                class="rating-stars"
                                                title="Popularity Rating"
                                            >
                                                <i
                                                    v-for="star in 5"
                                                    :key="star"
                                                    class="bi"
                                                    :class="
                                                        star <=
                                                        ratingValue(prompt)
                                                            ? 'bi-star-fill'
                                                            : 'bi-star'
                                                    "
                                                ></i>
                                            </div>
                                        </td>

                                        <td class="text-muted small">
                                            {{
                                                formattedDate(prompt.created_at)
                                            }}
                                        </td>

                                        <td class="text-end pe-4">
                                            <!-- Edit -->
                                            <a
                                                :href="editUrl(prompt)"
                                                class="btn btn-sm btn-outline-primary me-1"
                                            >
                                                <i class="bi bi-pencil"></i>
                                                Edit
                                            </a>

                                            <!-- Delete -->
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                @click="confirmDelete(prompt)"
                                            >
                                                <i class="bi bi-trash"></i>
                                                Delete
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Empty State -->
                                    <tr v-if="prompts.length === 0">
                                        <td
                                            colspan="7"
                                            class="text-center py-5 text-muted"
                                        >
                                            <i
                                                class="bi bi-folder2-open display-6 d-block mb-2 text-secondary"
                                            ></i>

                                            You haven't submitted any prompts
                                            yet.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div
                    v-if="lastPage > 1"
                    class="d-flex justify-content-center mt-4"
                >
                    <ul class="pagination">
                        <li
                            class="page-item"
                            :class="{ disabled: currentPage <= 1 }"
                        >
                            <a
                                class="page-link"
                                :href="
                                    currentPage > 1
                                        ? pageUrl(currentPage - 1)
                                        : undefined
                                "
                                @click="
                                    currentPage <= 1 &&
                                        $event.preventDefault()
                                "
                            >
                                Previous
                            </a>
                        </li>

                        <li
                            v-for="page in pages"
                            :key="page"
                            class="page-item"
                            :class="{ active: page === currentPage }"
                        >
                            <a
                                class="page-link"
                                :href="pageUrl(page)"
                                @click="
                                    page === currentPage &&
                                        $event.preventDefault()
                                "
                            >
                                {{ page }}
                            </a>
                        </li>

                        <li
                            class="page-item"
                            :class="{ disabled: currentPage >= lastPage }"
                        >
                            <a
                                class="page-link"
                                :href="
                                    currentPage < lastPage
                                        ? pageUrl(currentPage + 1)
                                        : undefined
                                "
                                @click="
                                    currentPage >= lastPage &&
                                        $event.preventDefault()
                                "
                            >
                                Next
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </main>

        <!-- Modern Delete Confirmation Popup -->
        <dialog
            ref="deleteDialog"
            class="delete-confirm-dialog"
            aria-labelledby="deleteDialogTitle"
            @click.self="closeDeleteDialog"
            @cancel.prevent="closeDeleteDialog"
        >
            <div class="delete-dialog-content">
                <div class="delete-dialog-icon" aria-hidden="true">
                    <i class="bi bi-trash3-fill"></i>
                </div>

                <div class="delete-dialog-label">
                    CONFIRM ACTION
                </div>

                <h2 id="deleteDialogTitle">
                    Move prompt to trash?
                </h2>

                <p class="delete-dialog-description">
                    This prompt will be moved to trash. You can continue
                    only if you are sure.
                </p>

                <div
                    v-if="deletingPrompt"
                    class="delete-prompt-preview"
                >
                    <span class="delete-preview-icon">
                        <i class="bi bi-file-earmark-text"></i>
                    </span>

                    <span class="delete-preview-title">
                        {{
                            deletingPrompt.title || 'Selected prompt'
                        }}
                    </span>
                </div>

                <div class="delete-dialog-actions">
                    <button
                        type="button"
                        class="delete-cancel-btn"
                        :disabled="deleting"
                        @click="closeDeleteDialog"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        class="delete-confirm-btn"
                        :disabled="deleting"
                        @click="movePromptToTrash"
                    >
                        <span
                            v-if="deleting"
                            class="delete-spinner"
                            aria-hidden="true"
                        ></span>

                        <i
                            v-else
                            class="bi bi-trash3"
                        ></i>

                        {{
                            deleting
                                ? 'Moving...'
                                : 'Move to Trash'
                        }}
                    </button>
                </div>

                <div class="delete-dialog-footnote">
                    <i class="bi bi-shield-check"></i>
                    Please review before continuing.
                </div>
            </div>
        </dialog>
    </div>
</template>

<style scoped>
.user-prompts-page {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    background: #f7f8fa;
    color: #212529;
}

/* Header */
.site-navbar {
    min-height: 70px;
    padding: 14px 2.3%;
    background: #212529;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, .12);
}

.brand-title {
    color: #fff;
    text-decoration: none;
    font-size: 1.35rem;
    font-weight: 800;
    letter-spacing: -.3px;
}

.brand-title:hover {
    color: #f8f9fa;
}

.back-home-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 7px 13px;
    border: 1px solid rgba(255, 255, 255, .8);
    border-radius: 5px;
    color: #fff;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
    transition: background .2s ease, color .2s ease;
}

.back-home-btn:hover {
    background: #fff;
    color: #212529;
}

/* Main page */
.page-content {
    flex: 1 0 auto;
    width: 100%;
    min-width: 0;
}

/* Hero section */
.hero-section {
    position: relative;
    z-index: 10;
    overflow: visible;
    background: linear-gradient(
        110deg,
        #111827 0%,
        #21194d 54%,
        #392681 100%
    );
    color: #fff;
    padding: 45px 0 46px;
    margin-bottom: 28px;
}

.hero-section h1 {
    color: #fff;
    font-size: clamp(2rem, 3vw, 3rem);
    letter-spacing: -.8px;
}

.hero-section .lead {
    color: #d5d8e8 !important;
    font-size: 1rem;
    margin-bottom: 28px !important;
}

/* Search form */
.user-prompt-search {
    position: relative;
    z-index: 20;
    border-radius: 8px;
    overflow: visible;
}

.user-prompt-search .form-control {
    min-width: 0;
    min-height: 47px;
    border-radius: 8px 0 0 8px;
    font-size: 1rem;
    box-shadow: none;
}

.user-prompt-search .form-control:focus {
    border-color: #ced4da;
    box-shadow: none;
    z-index: 1;
}

.user-prompt-search .btn-primary {
    min-height: 47px;
    background: #3164f4;
    border-color: #3164f4;
    border-radius: 0 7px 7px 0;
    font-weight: 600;
}

.user-prompt-search .btn-primary:hover {
    background: #2453d7;
    border-color: #2453d7;
}

/* Search suggestions dropdown */
.user-prompt-suggestions {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    right: 0;
    z-index: 2000;
    display: block;
    margin: 0;
    padding: 6px 0;
    max-height: 240px;
    overflow-y: auto;
    overflow-x: hidden;
    list-style: none;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, .16);
}

.user-prompt-suggestions li {
    display: block;
    padding: 11px 16px;
    margin: 0;
    cursor: pointer;
    color: #212529;
    background: #fff;
    border: 0;
    text-align: left;
    font-size: .95rem;
    transition: background-color .15s ease, color .15s ease;
}

.user-prompt-suggestions li + li {
    border-top: 1px solid #f1f3f5;
}

.user-prompt-suggestions li:hover,
.user-prompt-suggestions li:focus {
    background: #f1f5f9;
    color: #2453d7;
}

/* Table container */
.page-content > .container {
    position: relative;
    z-index: 1;
    width: min(100% - 32px, 1508px);
    max-width: 1508px;
}

.card {
    border-radius: 19px !important;
    box-shadow: 0 2px 5px rgba(15, 23, 42, .12) !important;
    background: #fff;
}

.table {
    color: #263238;
}

.table thead th {
    white-space: nowrap;
    font-weight: 700;
    vertical-align: middle;
}

.table thead.table-dark th:first-child {
    border-top-left-radius: 18px;
}

.table thead.table-dark th:last-child {
    border-top-right-radius: 18px;
}

.table tbody td {
    padding-top: 15px;
    padding-bottom: 15px;
}

.table tbody tr:last-child td {
    border-bottom: 0;
}

.table tbody tr:has(.bi-folder2-open) td {
    height: 205px;
    font-size: 1rem;
}

.table tbody tr:has(.bi-folder2-open) i {
    color: #737d86 !important;
    font-size: 2.5rem;
}

/* Ratings */
.rating-stars {
    color: #f59e0b;
    display: inline-flex;
    gap: 2px;
    white-space: nowrap;
}

/* Pagination */
.pagination .page-link {
    color: #315efb;
}

.pagination .page-item.active .page-link {
    background: #315efb;
    border-color: #315efb;
    color: #fff;
}

/* Mobile responsive */
@media (max-width: 767.98px) {
    .site-navbar {
        min-height: 62px;
        padding: 12px 15px;
    }

    .brand-title {
        font-size: 1.1rem;
    }

    .back-home-btn {
        padding: 6px 9px;
        font-size: .85rem;
    }

    .hero-section {
        padding: 34px 12px;
        margin-bottom: 22px;
    }

    .hero-section h1 {
        font-size: 1.8rem;
    }

    .hero-section .lead {
        font-size: .93rem;
    }

    .page-content > .container {
        width: calc(100% - 24px);
    }

    .table {
        min-width: 850px;
    }

    .user-prompt-search .form-control,
    .user-prompt-search .btn-primary {
        min-height: 44px;
    }

    .user-prompt-suggestions li {
        padding: 10px 12px;
    }
}

/* Centered, compact delete confirmation popup */
.delete-confirm-dialog {
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

    background: #ffffff;
    color: #24243a;

    box-shadow: 0 20px 60px rgba(20, 18, 45, 0.25);
}

.delete-confirm-dialog[open] {
    display: block;
    animation: delete-dialog-in 0.2s ease-out;
}

.delete-confirm-dialog::backdrop {
    background: rgba(20, 18, 45, 0.55);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

/* Compact popup content */
.delete-dialog-content {
    padding: 24px;
    text-align: center;
}

.delete-dialog-icon {
    width: 54px;
    height: 54px;
    margin: 0 auto 14px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 16px;
    background: #fff0f1;
    color: #e04455;
    font-size: 23px;
}

.delete-dialog-label {
    margin-bottom: 6px;
    color: #88889d;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.5px;
}

.delete-dialog-content h2 {
    margin: 0;
    color: #24243a;
    font-size: 21px;
    font-weight: 750;
    line-height: 1.3;
}

.delete-dialog-description {
    margin: 10px auto 17px;
    max-width: 320px;
    color: #77778b;
    font-size: 13px;
    line-height: 1.55;
}

/* Selected prompt preview */
.delete-prompt-preview {
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

.delete-preview-icon {
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

.delete-preview-title {
    overflow: hidden;
    color: #39394f;
    font-size: 13px;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Action buttons */
.delete-dialog-actions {
    display: flex;
    gap: 10px;
}

.delete-dialog-actions button {
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

    transition: background 0.2s ease, transform 0.2s ease;
}

.delete-dialog-actions button:hover:not(:disabled) {
    transform: translateY(-1px);
}

.delete-dialog-actions button:disabled {
    cursor: wait;
    opacity: 0.7;
}

.delete-cancel-btn {
    background: #f0f0f6;
    color: #55556c;
}

.delete-cancel-btn:hover:not(:disabled) {
    background: #e5e5ef;
}

.delete-confirm-btn {
    background: linear-gradient(135deg, #e84d5b, #cf3044);
    color: #ffffff;
    box-shadow: 0 5px 13px rgba(207, 48, 68, 0.2);
}

.delete-confirm-btn:hover:not(:disabled) {
    background: #c92e42;
}

/* Loading spinner */
.delete-spinner {
    width: 14px;
    height: 14px;
    border: 2px solid rgba(255, 255, 255, 0.45);
    border-top-color: #fff;
    border-radius: 50%;
    animation: delete-spin 0.7s linear infinite;
}

/* Footer note */
.delete-dialog-footnote {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;

    margin-top: 14px;
    color: #9696a8;
    font-size: 10px;
}

.delete-dialog-footnote i {
    color: #6e9d87;
}

@keyframes delete-dialog-in {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(6px);
    }

    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

@keyframes delete-spin {
    to {
        transform: rotate(360deg);
    }
}

/* Mobile screens */
@media (max-width: 480px) {
    .delete-confirm-dialog {
        max-width: 360px;
    }

    .delete-dialog-content {
        padding: 20px 17px;
    }

    .delete-dialog-content h2 {
        font-size: 19px;
    }

    .delete-dialog-actions {
        gap: 8px;
    }

    .delete-dialog-actions button {
        padding: 10px 7px;
        font-size: 11px;
    }
}

.delete-dialog-actions button:hover:not(:disabled) {
    transform: translateY(-1px);
}

.delete-dialog-actions button:focus-visible {
    outline: 3px solid rgba(98, 85, 217, .28);
    outline-offset: 3px;
}

.delete-dialog-actions button:disabled {
    cursor: wait;
    opacity: .7;
}

.delete-cancel-btn {
    color: #55556c;
    background: #f0f0f6;
}

.delete-cancel-btn:hover:not(:disabled) {
    background: #e7e7f0;
}

.delete-confirm-btn {
    color: #fff;
    background: linear-gradient(135deg, #e84d5b, #cf3044);
    box-shadow: 0 7px 16px rgba(207, 48, 68, .2);
}

.delete-confirm-btn:hover:not(:disabled) {
    box-shadow: 0 10px 22px rgba(207, 48, 68, .28);
}

.delete-spinner {
    width: 15px;
    height: 15px;
    border: 2px solid rgba(255, 255, 255, .45);
    border-top-color: #fff;
    border-radius: 50%;
    animation: delete-spin .7s linear infinite;
}

.delete-dialog-footnote {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-top: 18px;
    color: #9696a8;
    font-size: 11px;
}

.delete-dialog-footnote i {
    color: #6e9d87;
    font-size: 13px;
}

@keyframes delete-dialog-in {
    from {
        opacity: 0;
        transform: translateY(10px) scale(.97);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes delete-backdrop-in {
    from {
        opacity: 0;
    }

    to {
        opacity: 1;
    }
}

@keyframes delete-spin {
    to {
        transform: rotate(360deg);
    }
}

@media (max-width: 480px) {
    .delete-dialog-content {
        padding: 27px 20px 21px;
    }

    .delete-dialog-content h2 {
        font-size: 21px;
    }

    .delete-dialog-actions {
        flex-direction: column-reverse;
    }

    .delete-dialog-actions button {
        flex: auto;
        width: 100%;
    }
}

@media (prefers-reduced-motion: reduce) {
    .delete-confirm-dialog[open],
    .delete-confirm-dialog::backdrop,
    .delete-spinner {
        animation-duration: .01ms;
    }

    .delete-dialog-actions button {
        transition: none;
    }
}
</style>