@extends('layouts.frontlayout')

@php
    $hideNavbar = true;
@endphp

@section('title', 'Saved Prompts')

@push('styles')
<style>
/* =========================================================
   SAVED PAGE
========================================================= */

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

/* =========================================================
   SAVED CARD
   Same visual idea as Home prompt cards:
   image first, details open in modal.
========================================================= */

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

/* =========================================================
   HOME-STYLE VIEW MODAL
========================================================= */

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

.saved-view-modal .prompt-variable-input::placeholder {
    color: #94a3b8;
    opacity: 1;
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

/* =========================================================
   EMPTY / TOAST
========================================================= */

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

/* =========================================================
   RESPONSIVE
========================================================= */

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
</style>
@endpush

@section('content')

<div class="container saved-page">

    {{-- =====================================================
         HEADER
    ====================================================== --}}

    <div class="saved-header">

        <div class="saved-header-content">

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">

                <div class="d-flex align-items-center gap-3">

                    <div class="saved-icon">
                        <i class="bi bi-bookmark-fill"></i>
                    </div>

                    <div>

                        <h2 class="fw-bold mb-1">
                            Saved Prompts
                        </h2>

                        <p class="mb-0">
                            Your personal collection of saved AI prompts.
                        </p>

                        <span class="saved-count" id="savedPromptCount">

                            <i class="bi bi-bookmark-check-fill"></i>

                            {{ $prompts->total() }}

                            Saved

                        </span>

                    </div>

                </div>

                <a
                    href="{{ route('home') }}"
                    class="btn explore-btn"
                >
                    <i class="bi bi-grid-3x3-gap-fill"></i>
                    Explore Prompts
                </a>

            </div>

        </div>

    </div>


    {{-- =====================================================
         SUCCESS MESSAGE
    ====================================================== --}}

    @if(session('success'))

        <div
            class="alert alert-success alert-dismissible fade show rounded-4 mb-4"
            role="alert"
        >

            <i class="bi bi-check-circle-fill me-2"></i>

            {{ session('success') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    @endif


    {{-- =====================================================
         SAVED PROMPTS
    ====================================================== --}}

    @if($prompts->count())

        <div
            class="row g-4"
            id="savedPromptsGrid"
        >

            @foreach($prompts as $prompt)

                @php

                    /*
                    |----------------------------------------------------------
                    | PROMPT TEXT + VARIABLE METADATA
                    |----------------------------------------------------------
                    | Admin Create/Edit stores the variable label and example
                    | value at the end of prompt_text using this hidden marker:
                    |
                    | <!--AI_PROMPT_VARIABLES:ENCODED_JSON-->
                    |
                    | The marker is removed before anything is displayed/copied.
                    */

                    $promptText =
                        $prompt->prompt_text
                        ?? $prompt->prompt
                        ?? $prompt->description
                        ?? '';

                    $variableMeta = [];

                    if (
                        preg_match(
                            '/<!--AI_PROMPT_VARIABLES:([\s\S]*?)-->\s*$/i',
                            $promptText,
                            $metaMatch
                        )
                    ) {

                        $decodedMeta = json_decode(
                            urldecode($metaMatch[1]),
                            true
                        );

                        if (is_array($decodedMeta)) {
                            $variableMeta = $decodedMeta;
                        }

                        $promptText = preg_replace(
                            '/<!--AI_PROMPT_VARIABLES:[\s\S]*?-->\s*$/i',
                            '',
                            $promptText
                        );
                    }

                    /*
                    |----------------------------------------------------------
                    | DETECT BOTH VARIABLE FORMATS
                    |----------------------------------------------------------
                    | New prompts normally use [1], [2], [3]...
                    | Older prompts may contain descriptive placeholders such as:
                    | [young Indian woman]
                    | [elegant black formal suit]
                    | [soft golden-hour lighting]
                    |
                    | Both formats are supported here so existing saved prompts
                    | also get the customize inputs.
                    */
                    preg_match_all(
                        '/\[([^\[\]]+)\]/',
                        $promptText,
                        $matches
                    );

                    $promptVariables = collect($matches[1] ?? [])
                        ->map(fn ($value) => trim($value))
                        ->filter()
                        ->unique()
                        ->values();

                    /*
                    | Variable definitions are used only by this Blade view.
                    | For numeric placeholders, admin-saved metadata is used.
                    | For old descriptive placeholders, the text inside [ ] is
                    | used as the example so the user knows what to enter.
                    */
                    $variableDefinitions = $promptVariables
                        ->values()
                        ->map(function ($variable, $index) use ($variableMeta) {
                            $variableKey = (string) $variable;
                            $settings = [];

                            if (preg_match('/^\d+$/', $variableKey)) {
                                $settings = $variableMeta[$variableKey] ?? [];
                            }

                            $label = trim($settings['label'] ?? '');
                            $example = trim($settings['example'] ?? '');

                            if ($label === '') {
                                $label = 'Variable ' . ($index + 1);
                            }

                            if ($example === '' && !preg_match('/^\d+$/', $variableKey)) {
                                $example = $variableKey;
                            }

                            return [
                                'key' => $variableKey,
                                'label' => $label,
                                'example' => $example,
                                'index' => $index + 1,
                            ];
                        });

                    $isLongPrompt =
                        Str::length($promptText) > 150;

                    $shortPromptText =
                        Str::limit(
                            $promptText,
                            150,
                            ''
                        );

                    $modalTools = [];

                    if (!empty($prompt->ai_tool)) {

                        $decodedTools =
                            is_array($prompt->ai_tool)
                                ? $prompt->ai_tool
                                : json_decode(
                                    $prompt->ai_tool,
                                    true
                                );

                        $modalTools =
                            is_array($decodedTools)
                                ? $decodedTools
                                : [$prompt->ai_tool];
                    }

                @endphp


                <div
                    class="col-md-6 col-lg-4 saved-prompt-item"
                    data-prompt-id="{{ $prompt->id }}"
                >

                    <div class="saved-card">

                        {{-- =================================================
                             IMAGE / OPEN VIEW MODAL
                        ================================================== --}}

                        <button
                            type="button"
                            class="saved-image-button"
                            data-bs-toggle="modal"
                            data-bs-target="#savedPromptModal{{ $prompt->id }}"
                            title="View prompt"
                        >

                            @if($prompt->image)

                                <img
                                    src="{{ asset('storage/' . $prompt->image) }}"
                                    alt="{{ $prompt->title }}"
                                    class="saved-card-image"
                                    onerror="
                                        this.style.display='none';
                                        this.nextElementSibling.style.display='flex';
                                    "
                                >

                                <div
                                    class="saved-card-placeholder"
                                    style="display:none;"
                                >
                                    <i class="bi bi-stars"></i>
                                </div>

                            @else

                                <div class="saved-card-placeholder">
                                    <i class="bi bi-stars"></i>
                                </div>

                            @endif


                            <div class="saved-image-badge">

                                <i class="bi bi-image"></i>

                                Prompt

                            </div>

                        </button>


                        {{-- =================================================
                             ACTIONS
                        ================================================== --}}

                        <div class="saved-card-actions">

                            @if($promptVariables->isNotEmpty())

                                <button
                                    type="button"
                                    class="btn saved-copy-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#savedPromptModal{{ $prompt->id }}"
                                >

                                    <i class="bi bi-sliders2"></i>

                                    Customize &amp; Copy

                                </button>

                            @else

                                <button
                                    type="button"
                                    class="btn saved-copy-btn"
                                    onclick="copySavedPrompt({{ $prompt->id }}, this)"
                                >

                                    <i class="bi bi-clipboard"></i>

                                    Copy

                                </button>

                            @endif


                            <button
                                type="button"
                                class="btn saved-remove-btn"
                                onclick="removeSavedPrompt({{ $prompt->id }}, this)"
                                title="Remove from saved"
                            >

                                <i class="bi bi-bookmark-x"></i>

                                Remove

                            </button>

                        </div>

                    </div>

                </div>


                {{-- =================================================
                     HOME-STYLE VIEW MODAL
                ================================================== --}}

                <div
                    class="modal fade saved-view-modal"
                    id="savedPromptModal{{ $prompt->id }}"
                    tabindex="-1"
                    aria-hidden="true"
                >

                    <div
                        class="modal-dialog modal-dialog-centered modal-lg"
                    >

                        <div
                            class="modal-content border-0 shadow-lg rounded-4"
                        >

                            {{-- HEADER --}}

                            <div
                                class="modal-header modern-modal-header border-0 pb-3"
                            >

                                <span class="badge bg-light text-primary fs-6">

                                    {{ $prompt->category->name ?? 'General' }}

                                </span>


                                <button
                                    type="button"
                                    class="btn-close btn-close-white"
                                    data-bs-dismiss="modal"
                                    aria-label="Close"
                                ></button>

                            </div>


                            {{-- BODY --}}

                            <div class="modal-body p-4">

                                <h4 class="fw-bold text-dark mb-3">

                                    {{ $prompt->title }}

                                </h4>


                                {{-- AI TOOLS --}}

                                @if(!empty($modalTools))

                                    <div class="mb-3 d-flex flex-wrap gap-2">

                                        @foreach($modalTools as $tool)

                                            @php

                                                $toolUrl =
                                                    function_exists('availableTools')
                                                        ? (availableTools()[$tool] ?? '#')
                                                        : '#';

                                                $readableName =
                                                    ucfirst(
                                                        str_replace(
                                                            '_',
                                                            ' ',
                                                            $tool
                                                        )
                                                    );

                                            @endphp

                                            <a
                                                href="{{ $toolUrl }}"
                                                target="_blank"
                                                class="ai-tool-badge text-decoration-none"
                                            >

                                                <i class="bi bi-robot"></i>

                                                {{ $readableName }}

                                            </a>

                                        @endforeach

                                    </div>

                                @endif


                                {{-- IMAGE --}}

                                @if($prompt->image)

                                    <div class="saved-modal-image-wrap">

                                        <img
                                            src="{{ asset('storage/' . $prompt->image) }}"
                                            alt="{{ $prompt->title }}"
                                            class="saved-modal-image"
                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                        >

                                        <div
                                            class="saved-modal-no-image"
                                            style="display:none;"
                                        >
                                            <i class="bi bi-stars"></i>
                                        </div>

                                    </div>

                                @else

                                    <div class="saved-modal-no-image">

                                        <i class="bi bi-stars"></i>

                                    </div>

                                @endif


                                {{-- CUSTOMIZE --}}

                                @if($promptVariables->isNotEmpty())

                                    <div class="prompt-variables-box">

                                        <div class="prompt-variables-title">

                                            <i class="bi bi-sliders2-vertical"></i>

                                            Customize this prompt

                                        </div>


                                        <div class="prompt-variable-hint">

                                            Fill in all the details below before copying.

                                        </div>


                                        @foreach($variableDefinitions as $variableDefinition)

                                            <div class="prompt-variable-field">

                                                <label
                                                    class="prompt-variable-label"
                                                    for="saved-prompt-variable-{{ $prompt->id }}-{{ $variableDefinition['index'] }}"
                                                >

                                                    {{ $variableDefinition['label'] }}

                                                    <span
                                                        class="text-danger"
                                                        title="Required"
                                                    >
                                                        *
                                                    </span>

                                                </label>

                                                <input
                                                    type="text"
                                                    class="prompt-variable-input"
                                                    id="saved-prompt-variable-{{ $prompt->id }}-{{ $variableDefinition['index'] }}"
                                                    data-variable="{{ $variableDefinition['index'] }}"
                                                    data-placeholder="{{ $variableDefinition['key'] }}"
                                                    data-prompt-id="{{ $prompt->id }}"
                                                    placeholder="{{ $variableDefinition['example'] ?: 'Enter your answer' }}"
                                                    autocomplete="off"
                                                >

                                            </div>

                                        @endforeach


                                        <button
                                            type="button"
                                            class="prompt-variable-reset"
                                            onclick="resetSavedPromptVariables({{ $prompt->id }})"
                                        >

                                            <i class="bi bi-arrow-counterclockwise me-1"></i>

                                            Reset

                                        </button>

                                    </div>

                                @endif


                                {{-- PROMPT TEXT --}}

                                <label class="fw-bold mb-2 text-muted small">

                                    PROMPT TEXT

                                </label>


                                <div class="p-3 modal-prompt-text position-relative">

                                    <pre
                                        id="saved-modal-text-short-{{ $prompt->id }}"
                                        style="
                                            white-space: pre-wrap;
                                            font-family: monospace;
                                            margin: 0;
                                            color:#475569;
                                        "
                                    >{{ $shortPromptText }}@if($isLongPrompt)...@endif</pre>


                                    @if($isLongPrompt)

                                        <pre
                                            id="saved-modal-text-full-{{ $prompt->id }}"
                                            style="
                                                white-space: pre-wrap;
                                                font-family: monospace;
                                                margin: 0;
                                                display: none;
                                                color:#475569;
                                            "
                                        >{{ $promptText }}</pre>


                                        <div class="text-end mt-2">

                                            <button
                                                type="button"
                                                class="btn btn-link btn-sm text-decoration-none fw-bold p-0"
                                                onclick="toggleSavedModalText({{ $prompt->id }})"
                                            >

                                                Read More

                                                <i class="bi bi-chevron-down"></i>

                                            </button>

                                        </div>

                                    @endif

                                </div>


                                {{-- HIDDEN ORIGINAL PROMPT --}}

                                <textarea
                                    id="saved-original-prompt-{{ $prompt->id }}"
                                    class="saved-modal-copy-textarea"
                                    aria-hidden="true"
                                >{{ $promptText }}</textarea>

                            </div>


                            {{-- FOOTER --}}

                            <div class="modal-footer border-0 pt-0">

                                <button
                                    type="button"
                                    class="btn copy-btn fw-bold"
                                    onclick="copySavedModalPrompt({{ $prompt->id }}, this)"
                                >

                                    <i class="bi bi-clipboard-check me-1"></i>

                                    {{ $promptVariables->isNotEmpty() ? 'Copy Filled Prompt' : 'Copy Prompt' }}

                                </button>


                                <button
                                    type="button"
                                    class="btn btn-light border"
                                    data-bs-dismiss="modal"
                                >

                                    Close

                                </button>

                            </div>

                        </div>

                    </div>

                </div>

            @endforeach

        </div>


        {{-- PAGINATION --}}

        <div class="saved-pagination d-flex justify-content-center mt-5">

            {{ $prompts->links() }}

        </div>


    @else

        {{-- =====================================================
             EMPTY STATE
        ====================================================== --}}

        <div
            class="empty-saved"
            id="emptySavedState"
        >

            <div class="empty-saved-icon">

                <i class="bi bi-bookmark-heart"></i>

            </div>


            <h4 class="fw-bold mb-2">

                No Saved Prompts Yet

            </h4>


            <p class="text-muted mb-4">

                Save your favourite prompts from the Prompt Hub
                and they will appear here.

            </p>


            <a
                href="{{ route('home') }}"
                class="btn explore-btn"
            >

                <i class="bi bi-search"></i>

                Explore Prompts

            </a>

        </div>

    @endif

</div>


<script>

/* =========================================================
   CSRF
========================================================= */

function getSavedCsrfToken()
{
    const meta =
        document.querySelector(
            'meta[name="csrf-token"]'
        );

    return meta
        ? meta.getAttribute('content')
        : '';
}


/* =========================================================
   COPY FROM SAVED CARD
========================================================= */

function copySavedPrompt(
    promptId,
    button
)
{
    const textarea =
        document.getElementById(
            `saved-original-prompt-${promptId}`
        );

    if (!textarea) {
        showSavedToast('Prompt text not found.');
        return;
    }

    const text =
        textarea.value.trim();

    if (!text) {
        showSavedToast('Nothing to copy.');
        return;
    }

    navigator.clipboard
        .writeText(text)
        .then(function () {

            const oldHtml =
                button.innerHTML;

            button.innerHTML = `
                <i class="bi bi-check2"></i>
                Copied!
            `;

            showSavedToast(
                'Prompt copied successfully!'
            );

            setTimeout(function () {

                button.innerHTML =
                    oldHtml;

            }, 1800);

        })
        .catch(function () {

            textarea.classList.remove('saved-modal-copy-textarea');

            textarea.select();

            document.execCommand('copy');

            textarea.classList.add('saved-modal-copy-textarea');

            showSavedToast(
                'Prompt copied successfully!'
            );

        });
}


/* =========================================================
   COPY FILLED PROMPT FROM MODAL
========================================================= */

function copySavedModalPrompt(
    promptId,
    button
)
{
    const originalTextarea =
        document.getElementById(
            `saved-original-prompt-${promptId}`
        );

    if (!originalTextarea) {

        showSavedToast(
            'Prompt text not found.'
        );

        return;
    }

    const originalText =
        originalTextarea.value;

    const fields =
        document.querySelectorAll(
            `.prompt-variable-input[data-prompt-id="${promptId}"]`
        );

    /*
    |----------------------------------------------------------
    | NO VARIABLES
    |----------------------------------------------------------
    */

    if (fields.length === 0) {

        copySavedTextToClipboard(
            originalText,
            button
        );

        return;
    }


    const values = {};
    let firstEmptyField = null;


    /*
    |----------------------------------------------------------
    | COLLECT ALL VALUES
    |----------------------------------------------------------
    */

    fields.forEach(function (field) {

        const variableIndex =
            field.dataset.variable;

        const placeholder =
            field.dataset.placeholder || variableIndex;

        const value =
            field.value.trim();

        values[variableIndex] = {
            placeholder: placeholder,
            value: value
        };

        if (
            value === ''
            &&
            !firstEmptyField
        ) {

            firstEmptyField =
                field;
        }

    });


    /*
    |----------------------------------------------------------
    | ALL VARIABLES ARE REQUIRED
    |----------------------------------------------------------
    */

    if (firstEmptyField) {

        firstEmptyField.focus();

        const label =
            firstEmptyField
                .closest('.prompt-variable-field')
                ?.querySelector(
                    '.prompt-variable-label'
                );

        const variableName =
            label
                ? label.textContent
                    .replace('*', '')
                    .trim()
                : `Variable ${firstEmptyField.dataset.variable}`;

        showSavedToast(
            `Please fill ${variableName}.`
        );

        return;
    }


    /*
    |----------------------------------------------------------
    | REPLACE NUMERIC OR DESCRIPTIVE [PLACEHOLDERS]
    |----------------------------------------------------------
    | Use the original text only once so values containing
    | placeholder-like text are not recursively replaced.
    */

    const textToCopy =
        originalText.replace(
            /\[(\d+)\]/g,
            function (
                match,
                variableNumber
            ) {

                const matchingField =
                    Object.values(values).find(function (item) {
                        return item.placeholder === variableNumber;
                    });

                return matchingField
                    ? matchingField.value
                    : match;

            }
        );


    copySavedTextToClipboard(
        textToCopy,
        button
    );
}


/* =========================================================
   CLIPBOARD HELPER
========================================================= */

function copySavedTextToClipboard(
    textToCopy,
    button
)
{
    navigator.clipboard
        .writeText(textToCopy)
        .then(function () {

            const originalContent =
                button.innerHTML;

            button.innerHTML =
                '<i class="bi bi-check2 me-1"></i> Copied!';

            button.classList.remove(
                'copy-btn'
            );

            button.classList.add(
                'btn-dark'
            );

            showSavedToast(
                'Prompt copied successfully!'
            );

            setTimeout(function () {

                button.innerHTML =
                    originalContent;

                button.classList.remove(
                    'btn-dark'
                );

                button.classList.add(
                    'copy-btn'
                );

            }, 2000);

        })
        .catch(function (err) {

            console.error(
                'Failed to copy:',
                err
            );

            showSavedToast(
                'Unable to copy the prompt.'
            );

        });
}


/* =========================================================
   SYNC VARIABLE INPUTS
========================================================= */

document.addEventListener(
    'input',
    function (event) {

        const field =
            event.target.closest(
                '.prompt-variable-input'
            );

        if (!field) {
            return;
        }

        const promptId =
            field.dataset.promptId;

        const variable =
            field.dataset.variable;

        const value =
            field.value;

        document
            .querySelectorAll(
                `.prompt-variable-input[data-prompt-id="${promptId}"][data-variable="${variable}"]`
            )
            .forEach(function (otherField) {

                if (otherField !== field) {
                    otherField.value = value;
                }

            });
    }
);


/* =========================================================
   RESET VARIABLES
========================================================= */

function resetSavedPromptVariables(
    promptId
)
{
    document
        .querySelectorAll(
            `.prompt-variable-input[data-prompt-id="${promptId}"]`
        )
        .forEach(function (field) {

            field.value = '';

        });
}


/* =========================================================
   READ MORE / READ LESS
========================================================= */

function toggleSavedModalText(
    promptId
)
{
    const shortTextEl =
        document.getElementById(
            `saved-modal-text-short-${promptId}`
        );

    const fullTextEl =
        document.getElementById(
            `saved-modal-text-full-${promptId}`
        );

    if (
        !shortTextEl ||
        !fullTextEl
    ) {
        return;
    }

    const wrapper =
        shortTextEl.closest(
            '.modal-prompt-text'
        );

    const btnEl =
        wrapper
            ? wrapper.querySelector('button')
            : null;

    if (
        fullTextEl.style.display === 'none'
    ) {

        fullTextEl.style.display =
            'block';

        shortTextEl.style.display =
            'none';

        if (btnEl) {

            btnEl.innerHTML =
                'Read Less <i class="bi bi-chevron-up"></i>';

        }

    } else {

        fullTextEl.style.display =
            'none';

        shortTextEl.style.display =
            'block';

        if (btnEl) {

            btnEl.innerHTML =
                'Read More <i class="bi bi-chevron-down"></i>';

        }

    }
}


/* =========================================================
   REMOVE SAVED PROMPT
========================================================= */

function removeSavedPrompt(
    promptId,
    button
)
{
    if (
        !confirm(
            'Remove this prompt from your saved list?'
        )
    ) {
        return;
    }

    const card =
        button.closest(
            '.saved-prompt-item'
        );

    const modalElement =
        document.getElementById(
            `savedPromptModal${promptId}`
        );

    button.disabled =
        true;

    button.innerHTML = `
        <span class="spinner-border spinner-border-sm"></span>
        Removing...
    `;

    fetch(
        `{{ url('/prompts') }}/${promptId}/save`,
        {
            method: 'POST',

            headers: {
                'Content-Type':
                    'application/json',

                'X-CSRF-TOKEN':
                    getSavedCsrfToken(),

                'Accept':
                    'application/json',

                'X-Requested-With':
                    'XMLHttpRequest'
            },

            body:
                JSON.stringify({})
        }
    )
    .then(async function (response) {

        const data =
            await response
                .json()
                .catch(function () {
                    return {};
                });

        if (!response.ok) {

            throw new Error(
                data.message
                ||
                'Unable to remove prompt.'
            );

        }

        return data;

    })
    .then(function (data) {

        const isRemoved =
            data.saved === false
            ||
            data.saved === 0
            ||
            data.saved === '0';

        if (!isRemoved) {

            button.disabled =
                false;

            button.innerHTML = `
                <i class="bi bi-bookmark-x"></i>
                Remove
            `;

            return;
        }

        if (modalElement) {

            const modalInstance =
                bootstrap.Modal.getInstance(
                    modalElement
                );

            if (modalInstance) {
                modalInstance.hide();
            }

        }

        if (card) {

            card.style.transition =
                'all .25s ease';

            card.style.opacity =
                '0';

            card.style.transform =
                'scale(.95)';

            setTimeout(function () {

                card.remove();

                updateSavedCount();

                checkSavedEmptyState();

            }, 250);

        }

        showSavedToast(
            'Prompt removed from saved list.'
        );

    })
    .catch(function (error) {

        console.error(
            'Remove Saved Prompt Error:',
            error
        );

        button.disabled =
            false;

        button.innerHTML = `
            <i class="bi bi-bookmark-x"></i>
            Remove
        `;

        showSavedToast(
            error.message
            ||
            'Something went wrong.'
        );

    });
}


/* =========================================================
   COUNT
========================================================= */

function updateSavedCount()
{
    const countElement =
        document.getElementById(
            'savedPromptCount'
        );

    if (!countElement) {
        return;
    }

    const cards =
        document.querySelectorAll(
            '.saved-prompt-item'
        );

    const count =
        cards.length;

    countElement.innerHTML = `
        <i class="bi bi-bookmark-check-fill"></i>
        ${count}
        Saved
    `;
}


/* =========================================================
   EMPTY STATE
========================================================= */

function checkSavedEmptyState()
{
    const grid =
        document.getElementById(
            'savedPromptsGrid'
        );

    if (!grid) {
        return;
    }

    const cards =
        grid.querySelectorAll(
            '.saved-prompt-item'
        );

    if (cards.length > 0) {
        return;
    }

    grid.innerHTML = `
        <div class="col-12">

            <div class="empty-saved">

                <div class="empty-saved-icon">
                    <i class="bi bi-bookmark-heart"></i>
                </div>

                <h4 class="fw-bold mb-2">
                    No Saved Prompts Yet
                </h4>

                <p class="text-muted mb-4">
                    Save your favourite prompts from the Prompt Hub
                    and they will appear here.
                </p>

                <a
                    href="{{ route('home') }}"
                    class="btn explore-btn"
                >
                    <i class="bi bi-search"></i>
                    Explore Prompts
                </a>

            </div>

        </div>
    `;
}


/* =========================================================
   TOAST
========================================================= */

function showSavedToast(
    message
)
{
    const oldToast =
        document.getElementById(
            'savedPromptToast'
        );

    if (oldToast) {
        oldToast.remove();
    }

    const toast =
        document.createElement(
            'div'
        );

    toast.id =
        'savedPromptToast';

    toast.innerHTML = `
        <i class="bi bi-check-circle-fill"></i>
        ${message}
    `;

    document.body.appendChild(
        toast
    );

    setTimeout(function () {

        if (toast) {
            toast.remove();
        }

    }, 2500);
}


/* =========================================================
   CLEAR MODAL VARIABLES WHEN CLOSED
========================================================= */

document.addEventListener(
    'hidden.bs.modal',
    function (event) {

        const modal =
            event.target;

        if (
            !modal.classList.contains(
                'saved-view-modal'
            )
        ) {
            return;
        }

        const promptId =
            modal.id.replace(
                'savedPromptModal',
                ''
            );

        resetSavedPromptVariables(
            promptId
        );

        const shortTextEl =
            document.getElementById(
                `saved-modal-text-short-${promptId}`
            );

        const fullTextEl =
            document.getElementById(
                `saved-modal-text-full-${promptId}`
            );

        if (
            shortTextEl &&
            fullTextEl
        ) {

            shortTextEl.style.display =
                'block';

            fullTextEl.style.display =
                'none';

            const btn =
                modal.querySelector(
                    '.modal-prompt-text button'
                );

            if (btn) {

                btn.innerHTML =
                    'Read More <i class="bi bi-chevron-down"></i>';

            }

        }

    }
);

</script>

@endsection
