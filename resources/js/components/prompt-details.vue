```vue
<script setup>
import { computed, nextTick, onMounted, ref } from 'vue';

const props = defineProps({
    prompt: {
        type: Object,
        required: true,
    },
    homeUrl: {
        type: String,
        required: true,
    },
    copyTrackUrl: {
        type: String,
        default: '',
    },
});

const promptTextElement = ref(null);
const expanded = ref(false);
const canExpand = ref(false);
const message = ref('');
const messageType = ref('success');

const tools = computed(() => {
    const value = props.prompt.ai_tool;

    if (Array.isArray(value)) {
        return value;
    }

    if (typeof value === 'string') {
        try {
            const parsed = JSON.parse(value);

            if (Array.isArray(parsed)) {
                return parsed;
            }
        } catch {
            // Handle comma-separated tool names below.
        }

        return value
            .split(',')
            .map(item => item.trim())
            .filter(Boolean);
    }

    return [];
});

const imageUrl = computed(() => {
    if (!props.prompt.image) {
        return '';
    }

    if (props.prompt.image_url) {
        return props.prompt.image_url;
    }

    return `/storage/${props.prompt.image}`;
});

const promptUrl = computed(() => {
    return `https://thepromptszone.com/prompts/${props.prompt.id}`;
});

function checkPromptHeight() {
    const element = promptTextElement.value;

    if (!element) {
        return;
    }

    canExpand.value = element.scrollHeight > 181;

    if (!canExpand.value) {
        expanded.value = true;
    }
}

function showMessage(text, type = 'success') {
    message.value = text;
    messageType.value = type;
}

async function copyPrompt() {
    try {
        await navigator.clipboard.writeText(
            props.prompt.prompt_text || ''
        );

        showMessage('Prompt copied successfully!');

        // Track the copy after the prompt text has been copied.
        if (props.copyTrackUrl) {
            try {
                const response = await fetch(props.copyTrackUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN':
                            document.querySelector(
                                'meta[name="csrf-token"]'
                            )?.content || '',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    console.error(
                        'Copy tracking failed:',
                        response.status
                    );
                }
            } catch (error) {
                console.error(
                    'Copy tracking request failed:',
                    error
                );
            }
        }
    } catch (error) {
        console.error('Copy prompt failed:', error);

        showMessage(
            'Unable to copy. Please select and copy the prompt manually.',
            'danger'
        );
    }
}

async function sharePrompt() {
    const title = props.prompt.title || 'AI Prompt Hub';
    const url = promptUrl.value;

    // Include the URL in the text so it is available to the share target.
    const shareText = `${title}\n${url}`;

    try {
        if (navigator.share) {
            await navigator.share({
                title,
                text: shareText,
                url,
            });

            return;
        }

        await navigator.clipboard.writeText(shareText);
        showMessage('Prompt link copied successfully!');
    } catch (error) {
        // Closing the share dialog is not an error.
        if (error.name === 'AbortError') {
            return;
        }

        // If native sharing fails, try copying the live link instead.
        try {
            await navigator.clipboard.writeText(shareText);
            showMessage('Prompt link copied successfully!');
        } catch (copyError) {
            console.error('Sharing failed:', error);
            console.error('Copying share link failed:', copyError);

            showMessage(
                'Unable to share or copy the prompt link.',
                'danger'
            );
        }
    }
}

onMounted(async () => {
    await nextTick();
    checkPromptHeight();
});
</script>



<template>
    <div class="prompt-details-page">
        <main class="prompt-main">
            <div class="detail-card">
                <div class="detail-content">

                    <!-- Category -->
                    <span
                        v-if="prompt.category?.name"
                        class="category-badge"
                    >
                        {{ prompt.category.name }}
                    </span>

                    <!-- Title -->
                    <h1 class="page-heading">
                        {{ prompt.title }}
                    </h1>

                    <!-- Image -->
                    <div v-if="imageUrl" class="image-wrapper">
                        <img
                            :src="imageUrl"
                            :alt="prompt.title"
                            class="prompt-image"
                        >
                    </div>

                    <!-- Description -->
                    <p
                        v-if="prompt.label"
                        class="prompt-label"
                    >
                        {{ prompt.label }}
                    </p>

                    <!-- AI Tools -->
                    <section
                        v-if="tools.length"
                        class="ai-tools"
                    >
                        <strong class="ai-tools-heading">
                            AI Tools
                        </strong>

                        <div class="tool-list">
                            <span
                                v-for="tool in tools"
                                :key="tool"
                                class="tool-badge"
                            >
                                {{ tool }}
                            </span>
                        </div>
                    </section>

                    <!-- Prompt -->
                    <h2 class="prompt-title">
                        Prompt
                    </h2>

                    <div
                        ref="promptTextElement"
                        class="prompt-content"
                        :class="{ collapsed: !expanded }"
                    >{{ prompt.prompt_text }}</div>

                    <!-- Read More -->
                    <button
                        v-if="canExpand"
                        type="button"
                        class="read-more-btn"
                        :aria-expanded="expanded"
                        @click="expanded = !expanded"
                    >
                        {{ expanded ? 'Read Less' : 'Read More' }}

                        <i
                            class="bi"
                            :class="expanded
                                ? 'bi-chevron-up'
                                : 'bi-chevron-down'"
                        ></i>
                    </button>

                    <!-- Actions -->
                    <div class="action-buttons">
                        <button
                            type="button"
                            class="btn btn-copy"
                            @click="copyPrompt"
                        >
                            <i class="bi bi-clipboard me-1"></i>
                            Copy Prompt
                        </button>

                        <button
                            type="button"
                            class="btn btn-share"
                            @click="sharePrompt"
                        >
                            <i class="bi bi-share me-1"></i>
                            Share Prompt
                        </button>

                        <a
                            :href="homeUrl"
                            class="btn btn-home"
                        >
                            <i class="bi bi-house me-1"></i>
                            Home
                        </a>
                    </div>

                    <!-- Feedback -->
                    <div
                        v-if="message"
                        class="feedback-message"
                        :class="messageType === 'danger'
                            ? 'text-danger'
                            : 'text-success'"
                        role="status"
                        aria-live="polite"
                    >
                        {{ message }}
                    </div>

                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
/* ========================================
   PAGE
======================================== */
.prompt-details-page {
    width: 100%;
    min-height: 100vh;
    margin: 0;
    padding: 16px;
    background: #f3f5ff;
    color: #111827;
    font-family: Inter, -apple-system, BlinkMacSystemFont,
        "Segoe UI", sans-serif;
    box-sizing: border-box;
}

.prompt-details-page *,
.prompt-details-page *::before,
.prompt-details-page *::after {
    box-sizing: border-box;
}

/* ========================================
   MAIN LAYOUT
======================================== */
.prompt-main {
    width: 100%;
    max-width: 1120px;
    margin: 0 auto;
    padding: 0;
}

.detail-card {
    width: 100%;
    min-width: 0;
    background: #ffffff;
    border: 1px solid #e4e8ff;
    border-radius: 14px;
    box-shadow: 0 6px 24px rgba(30, 41, 59, 0.045);
    overflow: hidden;
}

.detail-content {
    width: 100%;
    min-width: 0;
    padding: 30px;
}

/* ========================================
   CATEGORY
======================================== */
.category-badge {
    display: inline-block;
    max-width: 100%;
    margin: 0 0 12px;
    padding: 3px 7px;
    color: #344054;
    background: #ffffff;
    border: 1px solid #d8deea;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 600;
    line-height: 1.5;
    overflow-wrap: anywhere;
}

/* ========================================
   TITLE
======================================== */
.page-heading {
    margin: 0 0 12px;
    color: #101828;
    font-size: clamp(23px, 2.5vw, 29px);
    font-weight: 850;
    line-height: 1.3;
    letter-spacing: -0.4px;
    overflow-wrap: anywhere;
}

/* ========================================
   IMAGE
======================================== */
.image-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 326px;
    margin: 0 0 16px;
    overflow: hidden;
    background: #f6f8fb;
    border-radius: 9px;
}

.prompt-image {
    display: block;
    width: auto;
    height: 100%;
    max-width: 100%;
    margin: 0 auto;
    object-fit: contain;
    object-position: center;
}

/* ========================================
   DESCRIPTION
======================================== */
.prompt-label {
    margin: 0 0 16px;
    color: #526078;
    font-size: 11px;
    line-height: 1.7;
    overflow-wrap: anywhere;
}

/* ========================================
   AI TOOLS
======================================== */
.ai-tools {
    position: static;
    display: block;
    width: 100%;
    margin: 0 0 20px;
    padding: 0;
}

.ai-tools-heading {
    position: static;
    display: block;
    margin: 0 0 8px;
    color: #111827;
    font-size: 12px;
    font-weight: 750;
    line-height: 1.5;
}

.tool-list {
    position: static;
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    align-items: center;
    justify-content: flex-start;
    gap: 5px;
    width: 100%;
    margin: 0;
    padding: 0;
}

.tool-badge {
    position: static;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: auto;
    max-width: 100%;
    margin: 0;
    padding: 3px 7px;
    color: #344054;
    background: #ffffff;
    border: 1px solid #dfe4ed;
    border-radius: 4px;
    font-size: 9px;
    font-weight: 650;
    line-height: 1.4;
    overflow-wrap: anywhere;
}

/* ========================================
   PROMPT HEADING
======================================== */
.prompt-title {
    margin: 0 0 10px;
    color: #101828;
    font-size: 14px;
    font-weight: 800;
    line-height: 1.5;
}

/* ========================================
   PROMPT TEXT
======================================== */
.prompt-content {
    position: relative;
    width: 100%;
    min-width: 0;
    margin: 0;
    padding: 13px;
    color: #344054;
    background: #f8fafc;
    border: 1px solid #e0e5ed;
    border-radius: 8px;
    font-size: 11px;
    line-height: 1.8;
    white-space: pre-wrap;
    overflow-wrap: anywhere;
    word-break: normal;
}

.prompt-content.collapsed {
    max-height: 112px;
    overflow: hidden;
}

.prompt-content.collapsed::after {
    content: "";
    position: absolute;
    right: 0;
    bottom: 0;
    left: 0;
    height: 30px;
    background: linear-gradient(
        to bottom,
        rgba(248, 250, 252, 0),
        #f8fafc
    );
    pointer-events: none;
}

/* ========================================
   READ MORE
======================================== */
.read-more-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin: 8px 0 18px;
    padding: 0;
    color: #6254f5;
    background: transparent;
    border: 0;
    font-size: 11px;
    line-height: 1.6;
    cursor: pointer;
}

.read-more-btn:hover {
    color: #4338ca;
    text-decoration: underline;
}

/* ========================================
   ACTION BUTTONS
======================================== */
.action-buttons {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
    width: 100%;
}

.action-buttons .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: auto;
    margin: 0;
    padding: 6px 9px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 500;
    line-height: 1.4;
    white-space: nowrap;
    text-decoration: none;
    box-shadow: none;
    transition: background-color 0.15s ease,
        border-color 0.15s ease;
}

.btn-copy {
    color: #ffffff;
    background: #6254ed;
    border: 1px solid #6254ed;
}

.btn-copy:hover,
.btn-copy:focus-visible {
    color: #ffffff;
    background: #5145d8;
    border-color: #5145d8;
}

.btn-share {
    color: #6254ed;
    background: #ffffff;
    border: 1px solid #a9a4ff;
}

.btn-share:hover,
.btn-share:focus-visible {
    color: #4338ca;
    background: #f5f4ff;
    border-color: #8278ff;
}

.btn-home {
    color: #475467;
    background: #ffffff;
    border: 1px solid #98a2b3;
}

.btn-home:hover,
.btn-home:focus-visible {
    color: #101828;
    background: #f9fafb;
    border-color: #667085;
}

/* ========================================
   FEEDBACK
======================================== */
.feedback-message {
    margin-top: 10px;
    font-size: 11px;
    line-height: 1.6;
    overflow-wrap: anywhere;
}

/* ========================================
   TABLET
======================================== */
@media (max-width: 767px) {
    .prompt-details-page {
        padding: 12px;
    }

    .detail-content {
        padding: 22px;
    }

    .page-heading {
        font-size: 24px;
    }

    .image-wrapper {
        height: 290px;
    }

    .prompt-content {
        font-size: 11px;
    }
}

/* ========================================
   MOBILE
======================================== */
@media (max-width: 480px) {
    .prompt-details-page {
        padding: 8px;
    }

    .detail-card {
        border-radius: 11px;
    }

    .detail-content {
        padding: 16px;
    }

    .page-heading {
        font-size: 21px;
        letter-spacing: -0.2px;
    }

    .image-wrapper {
        height: 240px;
        border-radius: 7px;
    }

    .prompt-label {
        font-size: 11px;
    }

    .ai-tools {
        margin-bottom: 18px;
    }

    .prompt-content {
        padding: 11px;
        font-size: 11px;
    }

    .action-buttons {
        gap: 5px;
    }

    .action-buttons .btn {
        padding: 6px 8px;
        font-size: 10px;
    }
}
</style>
