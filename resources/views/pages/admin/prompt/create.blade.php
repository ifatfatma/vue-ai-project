```blade
@extends('layouts.backlayout')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | AI TOOLS
    |--------------------------------------------------------------------------
    */
    $availableTools = [
        'chatgpt'    => 'ChatGPT',
        'claude'     => 'Claude',
        'gemini'     => 'Gemini',
        'midjourney' => 'Midjourney',
        'dalle'      => 'DALL-E',
        'perplexity' => 'Perplexity',
        'copilot'    => 'Microsoft Copilot',
        'grok'       => 'Grok',
        'deepseek'   => 'DeepSeek',
        'mistral'    => 'Mistral',
        'meta_ai'    => 'Meta AI',
        'other'      => 'Other',
    ];

    /*
    |--------------------------------------------------------------------------
    | OLD AI TOOLS
    |--------------------------------------------------------------------------
    */
    $oldFirstAiTools = old('prompts.0.ai_tool', []);

    if (!is_array($oldFirstAiTools)) {
        $oldFirstAiTools = [$oldFirstAiTools];
    }
@endphp


<style>

/* ==========================================================================
   ADMIN ADD PROMPT - MODERN DESIGN
   ========================================================================== */

/* MAIN FORM CARD */
.prompt-form-card {
    border: 1px solid #e8edf3;
    border-radius: 18px;
    background: #ffffff;
    height: calc(100vh - 140px);
    overflow-y: auto;
    overflow-x: hidden;
    box-shadow: 0 8px 30px rgba(15, 23, 42, 0.06);
}

.prompt-form-card::-webkit-scrollbar {
    width: 7px;
}

.prompt-form-card::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
}

.prompt-form-card::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

.prompt-form-card::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.prompt-form-card {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f1f5f9;
}


/* PAGE HEADER */
.prompt-page-header {
    padding: 4px 2px 20px;
    border-bottom: 1px solid #eef2f7;
    margin-bottom: 24px;
}

.prompt-page-icon {
    width: 48px;
    height: 48px;
    border-radius: 13px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    color: #ffffff;
    font-size: 23px;
    box-shadow: 0 8px 18px rgba(79, 70, 229, 0.22);
}

.prompt-page-header h4 {
    color: #172033;
    font-weight: 700;
    letter-spacing: -0.2px;
}

.prompt-page-header p {
    color: #64748b;
    font-size: 13px;
}


/* SECTION TITLE */
.form-section-title {
    color: #172033;
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 4px;
}

.form-section-subtitle {
    color: #64748b;
    font-size: 12px;
}


/* TOP INFORMATION BOX */
.prompt-basic-box {
    background: #f8fafc;
    border: 1px solid #e8edf3;
    border-radius: 14px;
    padding: 18px;
    margin-bottom: 24px;
}


/* LABELS */
.form-label-custom {
    display: block;
    font-weight: 600;
    color: #334155;
    margin-bottom: 8px;
    font-size: 13px;
}

.required-star {
    color: #ef4444;
    font-weight: 700;
}


/* INPUTS */
.prompt-form-card .form-control,
.prompt-form-card .form-select {
    min-height: 44px;
    border-radius: 10px;
    border: 1px solid #dbe3ec;
    color: #334155;
    background-color: #ffffff;
    font-size: 13px;
    transition: all 0.2s ease;
}

.prompt-form-card .form-control::placeholder {
    color: #94a3b8;
}

.prompt-form-card .form-control:hover,
.prompt-form-card .form-select:hover {
    border-color: #c4cfdb;
}

.prompt-form-card .form-control:focus,
.prompt-form-card .form-select:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.10);
}

textarea.form-control {
    min-height: 145px;
    resize: vertical;
    line-height: 1.6;
}


/* DIVIDER */
.prompt-divider {
    height: 1px;
    background: #edf1f5;
    border: 0;
    margin: 26px 0;
}


/* PROMPTS SECTION HEADER */
.prompts-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}

.prompts-section-heading {
    display: flex;
    align-items: center;
    gap: 10px;
}

.prompts-section-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #eef2ff;
    color: #4f46e5;
    font-size: 17px;
}

.prompts-section-heading h5 {
    margin: 0;
    color: #172033;
    font-size: 15px;
    font-weight: 700;
}

.prompts-section-heading small {
    color: #64748b;
    font-size: 11px;
}


/* PROMPT CARD */
.prompt-card {
    position: relative;
    border: 1px solid #e5eaf0;
    border-radius: 16px;
    background: #ffffff;
    padding: 20px !important;
    margin-bottom: 16px;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.045);
    transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
}

.prompt-card:hover {
    border-color: #c7d2fe !important;
    box-shadow: 0 8px 24px rgba(79, 70, 229, 0.08);
    transform: translateY(-1px);
}


/* PROMPT HEADER */
.prompt-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 14px;
    margin-bottom: 18px;
    border-bottom: 1px solid #eef2f7;
}

.prompt-number {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 700;
    padding: 7px 11px;
    border-radius: 8px;
    background: #eef2ff !important;
    color: #4f46e5 !important;
}


/* REMOVE BUTTON */
.remove-prompt-btn {
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    padding: 6px 10px;
}


/* AI TOOL DROPDOWN */
.ai-tool-dropdown {
    position: relative;
    width: 100%;
}

.ai-tool-toggle {
    width: 100%;
    min-height: 44px;
    background: #ffffff;
    border: 1px solid #dbe3ec;
    border-radius: 10px;
    padding: 9px 13px;
    cursor: pointer;
    color: #64748b;
    transition: all 0.2s ease;
}

.ai-tool-toggle:hover {
    border-color: #c4cfdb;
    background: #f8fafc;
}

.ai-tool-toggle:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.10);
}

.selected-tools-text {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    text-align: left;
    flex: 1;
    font-size: 13px;
}

.ai-tool-dropdown.has-selection .selected-tools-text {
    color: #1e293b;
    font-weight: 600;
}


/* AI TOOL MENU */
.ai-tool-menu {
    display: none;
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    width: 100%;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.14);
    padding: 7px;
    max-height: 280px;
    overflow-y: auto;
    z-index: 9999;
}

.ai-tool-dropdown.open .ai-tool-menu {
    display: block;
}

.ai-tool-menu::-webkit-scrollbar {
    width: 5px;
}

.ai-tool-menu::-webkit-scrollbar-track {
    background: #f8fafc;
}

.ai-tool-menu::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}


/* AI TOOL OPTION */
.ai-tool-option {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 10px;
    margin: 0;
    border-radius: 8px;
    cursor: pointer;
    font-size: 13px;
    color: #334155;
    transition: background 0.15s ease;
}

.ai-tool-option:hover {
    background: #f1f5f9;
}

.ai-tool-option input[type="checkbox"] {
    width: 16px;
    height: 16px;
    cursor: pointer;
    accent-color: #4f46e5;
}


/* SELECTED AI BADGES */
.selected-ai-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 9px;
}

.selected-ai-badges .badge {
    font-size: 10px;
    font-weight: 600;
    padding: 6px 9px;
    border-radius: 7px;
    background: #eef2ff !important;
    color: #4f46e5 !important;
}


/* HELP TEXT */
.form-help {
    display: block;
    margin-top: 7px;
    font-size: 11px;
    line-height: 1.5;
    color: #64748b;
}

.form-help code {
    background: #f1f5f9;
    color: #4f46e5;
    padding: 2px 5px;
    border-radius: 4px;
    font-size: 11px;
}


/* VARIABLE SETTINGS */
.variable-settings-box {
    margin-top: 12px;
    padding: 14px;
    border: 1px solid #dbe4ef;
    border-radius: 12px;
    background: #f8fafc;
    display: none;
}

.variable-settings-box.has-variables {
    display: block;
}

.variable-settings-title {
    font-weight: 700;
    color: #334155;
    font-size: 13px;
    margin-bottom: 4px;
}

.variable-settings-title i {
    color: #4f46e5;
}

.variable-settings-help {
    font-size: 11px;
    color: #64748b;
    margin-bottom: 12px;
}

.variable-row {
    padding: 11px;
    margin-bottom: 9px;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
    background: #ffffff;
}

.variable-row:last-child {
    margin-bottom: 0;
}

.variable-number {
    font-size: 11px;
    font-weight: 700;
    color: #4f46e5;
    margin-bottom: 7px;
}


/* IMAGE UPLOAD */
.image-upload-wrapper {
    border: 1px dashed #cbd5e1;
    border-radius: 11px;
    padding: 8px;
    background: #f8fafc;
}

.image-upload-wrapper .form-control {
    border: 0;
    background: transparent;
    box-shadow: none;
}

.image-upload-wrapper .form-control:focus {
    box-shadow: none;
}

.image-clear-btn {
    border-radius: 8px;
    font-size: 12px;
}


/* ADD MORE BUTTON */
.add-more-area {
    margin-top: 18px;
    padding: 16px;
    border: 1px dashed #cbd5e1;
    border-radius: 13px;
    background: #f8fafc;
}

.add-more-btn {
    border-radius: 9px;
    font-weight: 700;
    padding: 8px 14px;
    border-width: 1px;
    transition: all 0.2s ease;
}

.add-more-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 14px rgba(79, 70, 229, 0.12);
}


/* FORM ACTIONS */
.form-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 9px;
    padding-top: 4px;
}

.form-actions .btn {
    border-radius: 9px;
    font-weight: 600;
    min-height: 40px;
    padding-left: 16px;
    padding-right: 16px;
}

.save-all-btn {
    background: linear-gradient(135deg, #10b981, #059669);
    border: 0;
    box-shadow: 0 6px 14px rgba(5, 150, 105, 0.15);
}

.save-all-btn:hover {
    box-shadow: 0 8px 18px rgba(5, 150, 105, 0.22);
}


/* ALERT */
.prompt-form-alert {
    border-radius: 12px;
    border: 0;
}


/* MOBILE */
@media (max-width: 767.98px) {

    .prompt-form-card {
        height: calc(100vh - 110px);
        border-radius: 14px;
    }

    .prompt-form-card .card-body {
        padding: 18px !important;
    }

    .prompt-page-header {
        margin-bottom: 18px;
    }

    .prompt-page-icon {
        width: 42px;
        height: 42px;
        font-size: 19px;
    }

    .prompt-card {
        padding: 15px !important;
    }

    .prompts-section-header {
        align-items: flex-start;
    }

    .form-actions {
        flex-direction: column-reverse;
        align-items: stretch;
    }

    .form-actions .btn {
        width: 100%;
    }
}

</style>


{{-- ========================================================================
     VALIDATION ERRORS
     ======================================================================== --}}

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show prompt-form-alert" role="alert">
        <div class="d-flex align-items-start">
            <i class="mdi mdi-alert-circle-outline fs-4 me-2"></i>

            <div>
                <strong>Please fix the following errors:</strong>

                <ul class="mb-0 mt-2 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
            aria-label="Close">
        </button>
    </div>
@endif


{{-- ========================================================================
     SUCCESS MESSAGE
     ======================================================================== --}}

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show prompt-form-alert" role="alert">

        <i class="mdi mdi-check-circle-outline me-1"></i>
        {{ session('success') }}

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
            aria-label="Close">
        </button>

    </div>
@endif


{{-- ========================================================================
     MAIN FORM CARD
     ======================================================================== --}}

<div class="row">
    <div class="col-12">

        <div class="card prompt-form-card">

            <div class="card-body p-4">

                {{-- PAGE HEADER --}}
                <div class="prompt-page-header">

                    <div class="d-flex align-items-center gap-3">

                        <div class="prompt-page-icon">
                            <i class="mdi mdi-creation"></i>
                        </div>

                        <div>
                            <h4 class="mb-1">
                                Add New Prompts
                            </h4>

                            <p class="mb-0">
                                Create a prompt collection and add one or more AI prompts.
                            </p>
                        </div>

                    </div>

                </div>


                {{-- BASIC INFORMATION --}}
                <div class="prompt-basic-box">

                    <div class="d-flex align-items-center gap-2 mb-3">

                        <div class="prompts-section-icon">
                            <i class="mdi mdi-information-outline"></i>
                        </div>

                        <div>
                            <div class="form-section-title">
                                Basic Information
                            </div>

                            <div class="form-section-subtitle">
                                Select the category and give your prompt collection a title.
                            </div>
                        </div>

                    </div>


                    <div class="row">

                        {{-- CATEGORY --}}
                        <div class="col-md-6 mb-3 mb-md-0">

                            <label
                                for="category_id"
                                class="form-label-custom">

                                Select Category

                                <span class="required-star">*</span>

                            </label>

                            <select
                                name="category_id"
                                id="category_id"
                                class="form-select"
                                form="prompt-form"
                                required>

                                <option value="">
                                    -- Select Category --
                                </option>

                                @foreach($categories as $category)

                                    <option
                                        value="{{ $category->id }}"
                                        {{ old('category_id') == $category->id ? 'selected' : '' }}>

                                        {{ $category->name }}

                                    </option>

                                @endforeach

                            </select>

                        </div>


                        {{-- TITLE --}}
                        <div class="col-md-6">

                            <label
                                for="title"
                                class="form-label-custom">

                                Prompt Collection / Topic Title

                                <span class="required-star">*</span>

                            </label>

                            <input
                                type="text"
                                name="title"
                                id="title"
                                class="form-control"
                                form="prompt-form"
                                value="{{ old('title') }}"
                                placeholder="e.g. SEO Article Blueprint"
                                required>

                        </div>

                    </div>

                </div>


                {{-- FORM --}}
                <form
                    action="{{ route('admin.prompts.store') }}"
                    method="POST"
                    enctype="multipart/form-data"
                    id="prompt-form">

                    @csrf


                    {{-- PROMPTS HEADING --}}
                    <div class="prompts-section-header">

                        <div class="prompts-section-heading">

                            <div class="prompts-section-icon">
                                <i class="mdi mdi-text-box-multiple-outline"></i>
                            </div>

                            <div>

                                <h5>
                                    Prompts List
                                </h5>

                                <small>
                                    Add multiple prompts under this collection.
                                </small>

                            </div>

                        </div>

                    </div>


                    {{-- PROMPTS CONTAINER --}}
                    <div id="prompts-container">

                        {{-- FIRST PROMPT --}}
                        <div
                            class="prompt-card"
                            id="prompt-block-0">

                            {{-- PROMPT HEADER --}}
                            <div class="prompt-card-header">

                                <span class="badge prompt-number">
                                    <i class="mdi mdi-numeric-1-circle-outline"></i>
                                    Prompt #1
                                </span>

                            </div>


                            {{-- AI TOOL --}}
                            <div class="mb-3">

                                <label class="form-label-custom">
                                    AI Tool / Platform
                                </label>

                                <div class="ai-tool-dropdown">

                                    <button
                                        type="button"
                                        class="ai-tool-toggle d-flex align-items-center justify-content-between">

                                        <span class="selected-tools-text">
                                            Select AI Tool(s)
                                        </span>

                                        <i class="mdi mdi-chevron-down"></i>

                                    </button>


                                    <div class="ai-tool-menu">

                                        @foreach($availableTools as $key => $toolName)

                                            <label class="ai-tool-option">

                                                <input
                                                    type="checkbox"
                                                    name="prompts[0][ai_tool][]"
                                                    value="{{ $key }}"
                                                    {{ in_array($key, $oldFirstAiTools, true) ? 'checked' : '' }}>

                                                <span>
                                                    {{ $toolName }}
                                                </span>

                                            </label>

                                        @endforeach

                                    </div>

                                </div>


                                <div class="selected-ai-badges"></div>


                                <small class="form-help">
                                    <i class="mdi mdi-information-outline me-1"></i>
                                    Select one or multiple AI platforms for this prompt.
                                </small>

                            </div>


                            {{-- PROMPT LABEL --}}
                            <div class="mb-3">

                                <label
                                    for="prompt_label_0"
                                    class="form-label-custom">

                                    Prompt Step / Title

                                    <span class="text-muted fw-normal">
                                        (Optional)
                                    </span>

                                </label>

                                <input
                                    type="text"
                                    name="prompts[0][label]"
                                    id="prompt_label_0"
                                    class="form-control"
                                    value="{{ old('prompts.0.label') }}"
                                    placeholder="e.g. Step 1: Catchy Headline Generator">

                            </div>


                            {{-- PROMPT TEXT --}}
                            <div class="mb-3">

                                <label
                                    for="prompt_text_0"
                                    class="form-label-custom">

                                    Prompt Text

                                    <span class="required-star">*</span>

                                </label>

                                <textarea
                                    name="prompts[0][text]"
                                    id="prompt_text_0"
                                    class="form-control"
                                    rows="5"
                                    placeholder="Write your complete prompt here..."
                                    required>{{ old('prompts.0.text') }}</textarea>


                                <small class="form-help">

                                    <i class="mdi mdi-information-outline me-1"></i>

                                    Enter the complete prompt that the AI should process.
                                    For dynamic fields, use placeholders like
                                    <code>[1]</code>,
                                    <code>[2]</code>,
                                    <code>[3]</code>.
                                    These will automatically become input fields for users.

                                </small>


                                {{-- VARIABLE SETTINGS --}}
                                <div
                                    class="variable-settings-box"
                                    data-variable-settings="0">

                                    <div class="variable-settings-title">

                                        <i class="mdi mdi-tune-vertical me-1"></i>
                                        User Input Settings

                                    </div>

                                    <div class="variable-settings-help">
                                        Enter only an example / dummy value for each placeholder.
                                    </div>

                                    <div class="variable-settings-list"></div>

                                </div>

                            </div>


                            {{-- IMAGE --}}
                            <div>

                                <label
                                    for="img_0"
                                    class="form-label-custom">

                                    Example Output Image

                                    <span class="text-muted fw-normal">
                                        (Optional)
                                    </span>

                                </label>


                                <div class="input-group image-upload-wrapper">

                                    <input
                                        type="file"
                                        name="prompts[0][image]"
                                        id="img_0"
                                        class="form-control"
                                        accept="image/*">


                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary image-clear-btn"
                                        onclick="clearInput('img_0')">

                                        <i class="mdi mdi-close me-1"></i>
                                        Clear

                                    </button>

                                </div>


                                <small class="form-help">
                                    <i class="mdi mdi-image-outline me-1"></i>
                                    Upload an example output image if available.
                                </small>

                            </div>

                        </div>

                    </div>


                    {{-- ADD MORE --}}
                    <div class="add-more-area">

                        <button
                            type="button"
                            id="add-more-btn"
                            class="btn btn-outline-primary add-more-btn">

                            <i class="mdi mdi-plus me-1"></i>
                            Add More Prompt

                        </button>

                        <small class="form-help d-inline-block ms-2">
                            Add another prompt to this collection.
                        </small>

                    </div>


                    <hr class="prompt-divider">


                    {{-- FORM ACTIONS --}}
                    <div class="form-actions">

                        <button
                            type="reset"
                            class="btn btn-light border">

                            <i class="mdi mdi-refresh me-1"></i>
                            Reset

                        </button>


                        <button
                            type="submit"
                            class="btn btn-success text-white save-all-btn"
                            id="submit-btn">

                            <i class="mdi mdi-content-save-outline me-1"></i>
                            Save All Prompts

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>
</div>


<script>

/* ==========================================================================
   VARIABLE SETTINGS
   ========================================================================== */

const VARIABLE_META_MARKER = '<!--AI_PROMPT_VARIABLES:';

function stripVariableMeta(text) {
    return String(text || '')
        .replace(/<!--AI_PROMPT_VARIABLES:[\s\S]*?-->\s*$/i, '')
        .trimEnd();
}


function detectPromptVariables(text) {

    const matches =
        String(text || '').match(/\[(\d+)\]/g) || [];

    return [
        ...new Set(
            matches.map(function (item) {
                return item.replace(/\[|\]/g, '');
            })
        )
    ].sort(function (a, b) {
        return Number(a) - Number(b);
    });
}


function getVariableMeta(text) {

    const match =
        String(text || '').match(
            /<!--AI_PROMPT_VARIABLES:([\s\S]*?)-->\s*$/i
        );

    if (!match) {
        return {};
    }

    try {

        const data =
            JSON.parse(
                decodeURIComponent(match[1])
            );

        return data && typeof data === 'object'
            ? data
            : {};

    } catch (error) {

        return {};

    }
}


function escAttr(value) {

    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

}


function renderVariableSettings(box, text) {

    if (!box) {
        return;
    }

    const list =
        box.querySelector('.variable-settings-list');

    if (!list) {
        return;
    }

    const variables =
        detectPromptVariables(text);

    list.innerHTML = '';

    if (!variables.length) {

        box.classList.remove('has-variables');

        return;
    }

    box.classList.add('has-variables');

    const old =
        getVariableMeta(text);

    variables.forEach(function (variable) {

        const data =
            old[variable] || {};

        list.insertAdjacentHTML(
            'beforeend',
            `
                <div
                    class="variable-row"
                    data-variable-row="${variable}">

                    <div class="variable-number">
                        Variable [${variable}]
                    </div>

                    <label class="form-label-custom mb-1">
                        Example / Dummy Value
                    </label>

                    <input
                        type="text"
                        class="form-control variable-example-input"
                        data-variable="${variable}"
                        value="${escAttr(data.example || '')}"
                        placeholder="e.g. young Indian woman">

                </div>
            `
        );

    });

}


function attachVariableSettings(textarea, box) {

    if (!textarea || !box) {
        return;
    }

    renderVariableSettings(
        box,
        textarea.value
    );

    textarea.addEventListener(
        'input',
        function () {

            renderVariableSettings(
                box,
                stripVariableMeta(this.value)
            );

        }
    );

}


function collectVariableMeta(box) {

    const meta = {};

    if (!box) {
        return meta;
    }

    box
        .querySelectorAll('.variable-row')
        .forEach(function (row) {

            const variable =
                row.dataset.variableRow;

            const input =
                row.querySelector(
                    '.variable-example-input'
                );

            meta[variable] = {
                example: input?.value.trim() || ''
            };

        });

    return meta;

}


function validateVariableSettings(box) {

    if (
        !box ||
        !box.classList.contains('has-variables')
    ) {
        return true;
    }

    for (
        const row of box.querySelectorAll('.variable-row')
    ) {

        const variable =
            row.dataset.variableRow;

        const input =
            row.querySelector(
                '.variable-example-input'
            );

        if (!input?.value.trim()) {

            input?.focus();

            alert(
                `Please enter an example / dummy value for Variable [${variable}].`
            );

            return false;
        }

    }

    return true;

}


function appendVariableMeta(text, meta) {

    const clean =
        stripVariableMeta(text);

    if (
        !detectPromptVariables(clean).length
    ) {
        return clean;
    }

    return (
        clean +
        VARIABLE_META_MARKER +
        encodeURIComponent(
            JSON.stringify(meta)
        ) +
        '-->'
    );

}


/* ==========================================================================
   DOM READY
   ========================================================================== */

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /* AI TOOLS */
        const aiTools =
            @json($availableTools);


        /* PROMPT COUNTER */
        let count = 1;


        /* FIRST VARIABLE SETTINGS */
        attachVariableSettings(
            document.getElementById('prompt_text_0'),
            document.querySelector(
                '[data-variable-settings="0"]'
            )
        );


        /* ==================================================================
           GET AI TOOL OPTIONS
           ================================================================== */

        function getAiToolOptions(index) {

            let html = '';

            Object.entries(aiTools)
                .forEach(function ([key, name]) {

                    html += `
                        <label class="ai-tool-option">

                            <input
                                type="checkbox"
                                name="prompts[${index}][ai_tool][]"
                                value="${key}">

                            <span>
                                ${name}
                            </span>

                        </label>
                    `;

                });

            return html;

        }


        /* ==================================================================
           UPDATE SELECTED AI TOOLS
           ================================================================== */

        function updateSelectedTools(dropdown) {

            const checkedInputs =
                dropdown.querySelectorAll(
                    'input[type="checkbox"]:checked'
                );

            const textElement =
                dropdown.querySelector(
                    '.selected-tools-text'
                );

            const badgeContainer =
                dropdown.parentElement.querySelector(
                    '.selected-ai-badges'
                );

            if (!textElement) {
                return;
            }


            /* NO SELECTION */
            if (checkedInputs.length === 0) {

                textElement.textContent =
                    'Select AI Tool(s)';

                dropdown.classList.remove(
                    'has-selection'
                );

                if (badgeContainer) {
                    badgeContainer.innerHTML = '';
                }

                return;
            }


            /* SELECTED NAMES */
            const selectedNames = [];

            checkedInputs.forEach(
                function (input) {

                    const option =
                        input.closest(
                            '.ai-tool-option'
                        );

                    if (!option) {
                        return;
                    }

                    const nameElement =
                        option.querySelector('span');

                    if (!nameElement) {
                        return;
                    }

                    const name =
                        nameElement.textContent.trim();

                    selectedNames.push(name);

                }
            );


            /* DROPDOWN TEXT */
            textElement.textContent =
                selectedNames.join(', ');

            dropdown.classList.add(
                'has-selection'
            );


            /* BADGES */
            if (badgeContainer) {

                badgeContainer.innerHTML = '';

                selectedNames.forEach(
                    function (name) {

                        const badge =
                            document.createElement('span');

                        badge.className =
                            'badge';

                        badge.innerHTML =
                            '<i class="mdi mdi-robot me-1"></i>' +
                            name;

                        badgeContainer.appendChild(
                            badge
                        );

                    }
                );

            }

        }


        /* ==================================================================
           INITIALIZE AI DROPDOWNS
           ================================================================== */

        function initializeAiDropdowns() {

            document
                .querySelectorAll('.ai-tool-dropdown')
                .forEach(function (dropdown) {

                    updateSelectedTools(
                        dropdown
                    );

                });

        }

        initializeAiDropdowns();


        /* ==================================================================
           AI DROPDOWN TOGGLE
           ================================================================== */

        document.addEventListener(
            'click',
            function (event) {

                const toggle =
                    event.target.closest(
                        '.ai-tool-toggle'
                    );


                /* OPEN / CLOSE DROPDOWN */
                if (toggle) {

                    const dropdown =
                        toggle.closest(
                            '.ai-tool-dropdown'
                        );


                    /* CLOSE OTHER DROPDOWNS */
                    document
                        .querySelectorAll(
                            '.ai-tool-dropdown.open'
                        )
                        .forEach(
                            function (openDropdown) {

                                if (
                                    openDropdown !== dropdown
                                ) {

                                    openDropdown.classList.remove(
                                        'open'
                                    );

                                }

                            }
                        );


                    dropdown.classList.toggle('open');

                    return;
                }


                /* CHECKBOX SELECTED */
                const checkbox =
                    event.target.closest(
                        '.ai-tool-option input[type="checkbox"]'
                    );

                if (checkbox) {

                    const dropdown =
                        checkbox.closest(
                            '.ai-tool-dropdown'
                        );

                    if (dropdown) {

                        updateSelectedTools(
                            dropdown
                        );

                    }

                    return;
                }


                /* CLICK OUTSIDE */
                if (
                    !event.target.closest(
                        '.ai-tool-dropdown'
                    )
                ) {

                    document
                        .querySelectorAll(
                            '.ai-tool-dropdown.open'
                        )
                        .forEach(
                            function (dropdown) {

                                dropdown.classList.remove(
                                    'open'
                                );

                            }
                        );

                }

            }
        );

/* ==================================================================
           ADD MORE PROMPT
           ================================================================== */

        document
            .getElementById('add-more-btn')
            .addEventListener(
                'click',
                function () {

                    const index = count;

                    count++;


                    const newPromptBox = `
                        <div
                            class="prompt-card"
                            id="prompt-block-${index}">

                            <!-- PROMPT HEADER -->
                            <div class="prompt-card-header">

                                <span class="badge prompt-number">
                                    <i class="mdi mdi-numeric-${index + 1}-circle-outline"></i>
                                    Prompt #${index + 1}
                                </span>

                                <button
                                    type="button"
                                    class="btn btn-outline-danger btn-sm remove-prompt-btn"
                                    onclick="deleteBox('prompt-block-${index}')">

                                    <i class="mdi mdi-delete-outline me-1"></i>
                                    Remove

                                </button>

                            </div>

                            <!-- PROMPT TITLE / TOPIC TITLE FOR THIS SPECIFIC PROMPT -->
                            <div class="mb-3">
                                <label for="prompt_item_title_${index}" class="form-label-custom">
                                    Prompt Collection / Topic Title <span class="required-star">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="prompts[${index}][collection_title]"
                                    id="prompt_item_title_${index}"
                                    class="form-control"
                                    placeholder="e.g. SEO Article Blueprint"
                                    required>
                            </div>


                            <!-- AI TOOL -->
                            <div class="mb-3">

                                <label class="form-label-custom">
                                    AI Tool / Platform
                                </label>

                                <div class="ai-tool-dropdown">

                                    <button
                                        type="button"
                                        class="ai-tool-toggle d-flex align-items-center justify-content-between">

                                        <span class="selected-tools-text">
                                            Select AI Tool(s)
                                        </span>

                                        <i class="mdi mdi-chevron-down"></i>

                                    </button>


                                    <div class="ai-tool-menu">

                                        ${getAiToolOptions(index)}

                                    </div>

                                </div>


                                <div class="selected-ai-badges"></div>


                                <small class="form-help">
                                    <i class="mdi mdi-information-outline me-1"></i>
                                    Select one or multiple AI platforms for this prompt.
                                </small>

                            </div>


                            <!-- PROMPT LABEL -->
                            <div class="mb-3">

                                <label
                                    for="prompt_label_${index}"
                                    class="form-label-custom">

                                    Prompt Step / Title

                                    <span class="text-muted fw-normal">
                                        (Optional)
                                    </span>

                                </label>

                                <input
                                    type="text"
                                    name="prompts[${index}][label]"
                                    id="prompt_label_${index}"
                                    class="form-control"
                                    placeholder="e.g. Step ${index + 1}: Content Generator">

                            </div>


                            <!-- PROMPT TEXT -->
                            <div class="mb-3">

                                <label
                                    for="prompt_text_${index}"
                                    class="form-label-custom">

                                    Prompt Text

                                    <span class="required-star">*</span>

                                </label>

                                <textarea
                                    name="prompts[${index}][text]"
                                    id="prompt_text_${index}"
                                    class="form-control"
                                    rows="5"
                                    placeholder="Write your complete prompt here..."
                                    required></textarea>


                                <small class="form-help">

                                    <i class="mdi mdi-information-outline me-1"></i>

                                    Enter the complete prompt that the AI should process.
                                    For dynamic fields, use placeholders like
                                    <code>[1]</code>,
                                    <code>[2]</code>,
                                    <code>[3]</code>.

                                </small>


                                <div
                                    class="variable-settings-box"
                                    data-variable-settings="${index}">

                                    <div class="variable-settings-title">

                                        <i class="mdi mdi-tune-vertical me-1"></i>
                                        User Input Settings

                                    </div>

                                    <div class="variable-settings-help">
                                        Enter only an example / dummy value for each placeholder.
                                    </div>

                                    <div class="variable-settings-list"></div>

                                </div>

                            </div>


                            <!-- IMAGE -->
                            <div>

                                <label
                                    for="img_${index}"
                                    class="form-label-custom">

                                    Example Output Image

                                    <span class="text-muted fw-normal">
                                        (Optional)
                                    </span>

                                </label>


                                <div class="input-group image-upload-wrapper">

                                    <input
                                        type="file"
                                        name="prompts[${index}][image]"
                                        id="img_${index}"
                                        class="form-control"
                                        accept="image/*">


                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary image-clear-btn"
                                        onclick="clearInput('img_${index}')">

                                        <i class="mdi mdi-close me-1"></i>
                                        Clear

                                    </button>

                                </div>


                                <small class="form-help">
                                    <i class="mdi mdi-image-outline me-1"></i>
                                    Upload an example output image if available.
                                </small>

                            </div>

                        </div>
                    `;


                    document
                        .getElementById('prompts-container')
                        .insertAdjacentHTML(
                            'beforeend',
                            newPromptBox
                        );


                    /* VARIABLE SETTINGS */
                    attachVariableSettings(
                        document.getElementById(
                            `prompt_text_${index}`
                        ),
                        document.querySelector(
                            `[data-variable-settings="${index}"]`
                        )
                    );


                    /* SCROLL TO NEW PROMPT */
                    const formCard =
                        document.querySelector(
                            '.prompt-form-card'
                        );

                    if (formCard) {

                        setTimeout(
                            function () {

                                formCard.scrollTo({
                                    top: formCard.scrollHeight,
                                    behavior: 'smooth'
                                });

                            },
                            100
                        );

                    }

                }
            );


        /* ==================================================================
           FORM SUBMIT PROTECTION + VARIABLE META
           ================================================================== */

        document
            .getElementById('prompt-form')
            .addEventListener(
                'submit',
                function (event) {

                    const textareas =
                        this.querySelectorAll(
                            'textarea[name^="prompts["][name$="[text]"]'
                        );


                    for (
                        const textarea of textareas
                    ) {

                        const match =
                            textarea.name.match(
                                /^prompts\[(\d+)\][text]$/
                            );

                        if (!match) {
                            continue;
                        }


                        const box =
                            this.querySelector(
                                `[data-variable-settings="${match[1]}"]`
                            );


                        if (
                            !validateVariableSettings(box)
                        ) {

                            event.preventDefault();

                            return;
                        }


                        textarea.value =
                            appendVariableMeta(
                                textarea.value,
                                collectVariableMeta(box)
                            );

                    }


                    const submitButton =
                        document.getElementById(
                            'submit-btn'
                        );


                    submitButton.disabled = true;

                    submitButton.innerHTML = `
                        <span
                            class="spinner-border spinner-border-sm me-1"
                            role="status">
                        </span>
                        Saving...
                    `;

                }
            );

    }
);


/* ==========================================================================
   DELETE PROMPT BOX
   ========================================================================== */

function deleteBox(boxId) {

    const box =
        document.getElementById(boxId);

    if (!box) {
        return;
    }


    /* KEEP AT LEAST ONE PROMPT */
    const promptBoxes =
        document.querySelectorAll(
            '.prompt-card'
        );


    if (promptBoxes.length <= 1) {

        alert(
            'At least one prompt is required.'
        );

        return;
    }


    box.remove();

}


/* ==========================================================================
   CLEAR IMAGE
   ========================================================================== */

function clearInput(inputId) {

    const input =
        document.getElementById(inputId);

    if (input) {
        input.value = '';
    }

}

</script>

@endsection
```
