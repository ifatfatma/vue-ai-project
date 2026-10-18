```blade
@extends('layouts.backlayout')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | EDIT PROMPT DATA
    |--------------------------------------------------------------------------
    */

    $editPromptRawText = old('prompt_text', $prompt->prompt_text);

    $editVariableMeta = [];

    /*
    |--------------------------------------------------------------------------
    | Extract variable metadata from saved prompt text
    |--------------------------------------------------------------------------
    */

    if (preg_match('/<!--AI_PROMPT_VARIABLES:([\s\S]*?)-->\s*$/i', $editPromptRawText, $editMetaMatch)) {

        $decodedEditMeta = json_decode(
            urldecode($editMetaMatch[1]),
            true
        );

        if (is_array($decodedEditMeta)) {
            $editVariableMeta = $decodedEditMeta;
        }

        $editPromptRawText = preg_replace(
            '/<!--AI_PROMPT_VARIABLES:[\s\S]*?-->\s*$/i',
            '',
            $editPromptRawText
        );
    }

    /*
    |--------------------------------------------------------------------------
    | AI TOOLS
    |--------------------------------------------------------------------------
    */

    $availableTools = availableTools();

    $existingTools = $prompt->ai_tool;

    if (is_string($existingTools)) {

        $decodedTools = json_decode($existingTools, true);

        if (is_array($decodedTools)) {
            $existingTools = $decodedTools;
        } else {
            $existingTools = [$existingTools];
        }
    }

    if (!is_array($existingTools)) {
        $existingTools = [];
    }

    $selectedTools = old('ai_tool', $existingTools);

    if (!is_array($selectedTools)) {
        $selectedTools = [$selectedTools];
    }
@endphp


<div class="row">

    <div class="col-12">

        <div class="prompt-form-card">

            {{-- ========================================================= --}}
            {{-- PAGE HEADER --}}
            {{-- ========================================================= --}}

            <div class="prompt-page-header">

                <div class="prompt-page-header-left">

                    <div class="prompt-page-icon">
                        <i class="bi bi-pencil-square"></i>
                    </div>

                    <div>
                        <h4 class="prompt-page-title">
                            Edit Prompt
                        </h4>

                        <p class="prompt-page-subtitle mb-0">
                            Update your prompt details, AI tools and example output.
                        </p>
                    </div>

                </div>

                <a
                    href="{{ route('admin.prompts.index') }}"
                    class="back-to-prompts-btn"
                >
                    <i class="bi bi-arrow-left me-1"></i>
                    Back to Prompts
                </a>

            </div>


            {{-- ========================================================= --}}
            {{-- VALIDATION ERRORS --}}
            {{-- ========================================================= --}}

            @if ($errors->any())

                <div class="alert alert-danger modern-alert">

                    <div class="d-flex align-items-start">

                        <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>

                        <div>

                            <strong>Please fix the following errors:</strong>

                            <ul class="mb-0 mt-2">

                                @foreach ($errors->all() as $error)

                                    <li>{{ $error }}</li>

                                @endforeach

                            </ul>

                        </div>

                    </div>

                </div>

            @endif


            {{-- ========================================================= --}}
            {{-- FORM --}}
            {{-- ========================================================= --}}

            <form
                action="{{ route('admin.prompts.update', $prompt->id) }}"
                method="POST"
                enctype="multipart/form-data"
                id="edit-prompt-form"
            >

                @csrf
                @method('PUT')


                {{-- ===================================================== --}}
                {{-- BASIC INFORMATION --}}
                {{-- ===================================================== --}}

                <div class="prompt-basic-box">

                    <div class="form-section-heading">

                        <div class="section-heading-icon">
                            <i class="bi bi-info-circle"></i>
                        </div>

                        <div>
                            <h5 class="form-section-title">
                                Basic Information
                            </h5>

                            <p class="form-section-subtitle">
                                Update the category and title of your prompt collection.
                            </p>
                        </div>

                    </div>


                    <div class="row g-3">

                        {{-- CATEGORY --}}

                        <div class="col-md-6">

                            <label class="form-label-custom">
                                Category
                                <span class="required-star">*</span>
                            </label>

                            <select
                                name="category_id"
                                class="form-control modern-form-control"
                                required
                            >

                                @foreach($categories as $category)

                                    <option
                                        value="{{ $category->id }}"
                                        {{ old('category_id', $prompt->category_id) == $category->id ? 'selected' : '' }}
                                    >
                                        {{ $category->name }}
                                    </option>

                                @endforeach

                            </select>

                        </div>


                        {{-- TITLE --}}

                        <div class="col-md-6">

                            <label class="form-label-custom">
                                Topic / Collection Title
                                <span class="required-star">*</span>
                            </label>

                            <input
                                type="text"
                                name="title"
                                class="form-control modern-form-control"
                                value="{{ old('title', $prompt->title) }}"
                                placeholder="Enter prompt collection title"
                                required
                            >

                        </div>

                    </div>

                </div>


                {{-- ===================================================== --}}
                {{-- PROMPT DETAILS --}}
                {{-- ===================================================== --}}

                <div class="prompts-section-header">

                    <div class="prompts-section-heading">

                        <div class="prompts-section-icon">
                            <i class="bi bi-chat-square-text"></i>
                        </div>

                        <div>

                            <h5 class="form-section-title mb-0">
                                Prompt Details
                            </h5>

                            <p class="form-section-subtitle mb-0">
                                Edit the prompt text and user input variables.
                            </p>

                        </div>

                    </div>

                </div>


                <div class="prompt-card">

                    <div class="prompt-card-header">

                        <div class="d-flex align-items-center gap-2">

                            <span class="prompt-number">
                                Prompt
                            </span>

                            <span class="prompt-card-label">
                                Edit Prompt Content
                            </span>

                        </div>

                    </div>


                    <div class="prompt-card-body">

                        {{-- ================================================= --}}
                        {{-- STEP LABEL --}}
                        {{-- ================================================= --}}

                        <div class="form-group mb-4">

                            <label class="form-label-custom">

                                Step Label

                                <span class="optional-label">
                                    (Optional)
                                </span>

                            </label>

                            <input
                                type="text"
                                name="label"
                                class="form-control modern-form-control"
                                value="{{ old('label', $prompt->label) }}"
                                placeholder="Example: Step 1"
                            >

                            <div class="form-help">
                                <i class="bi bi-info-circle me-1"></i>
                                Add a label if this prompt represents a specific step.
                            </div>

                        </div>


                        {{-- ================================================= --}}
                        {{-- PROMPT TEXT --}}
                        {{-- ================================================= --}}

                        <div class="form-group mb-4">

                            <label class="form-label-custom">

                                Prompt Text

                                <span class="required-star">*</span>

                            </label>

                            <textarea
                                name="prompt_text"
                                id="edit_prompt_text"
                                class="form-control modern-textarea"
                                rows="8"
                                placeholder="Write your prompt here..."
                                required
                            >{{ $editPromptRawText }}</textarea>

                            <div class="form-help">

                                <i class="bi bi-lightbulb me-1"></i>

                                Use placeholders like
                                <code>[1]</code>,
                                <code>[2]</code>,
                                <code>[3]</code>.

                                They will automatically become user input fields.

                            </div>


                            {{-- ============================================= --}}
                            {{-- VARIABLE SETTINGS --}}
                            {{-- ============================================= --}}

                            <div
                                class="variable-settings-box"
                                id="edit-variable-settings"
                            >

                                <div class="variable-settings-title">

                                    <i class="bi bi-sliders2-vertical me-1"></i>

                                    User Input Settings

                                </div>

                                <div class="variable-settings-help">

                                    Enter only an example / dummy value for each placeholder.

                                </div>

                                <div class="variable-settings-list"></div>

                            </div>

                        </div>


                        {{-- ================================================= --}}
                        {{-- AI TOOLS --}}
                        {{-- ================================================= --}}

                        <div class="form-group mb-4">

                            <label class="form-label-custom">

                                AI Tools / Platforms

                                <span class="required-star">*</span>

                            </label>


                            <div class="ai-tool-dropdown">

                                {{-- Dropdown Button --}}

                                <button
                                    type="button"
                                    id="aiToolsDropdownBtn"
                                    class="ai-tool-toggle"
                                >

                                    <span id="aiToolsSelectedText">
                                        Select AI Tools
                                    </span>

                                    <i class="bi bi-chevron-down"></i>

                                </button>


                                {{-- Dropdown Menu --}}

                                <div
                                    id="aiToolsDropdownMenu"
                                    class="ai-tool-menu"
                                >

                                    @foreach($availableTools as $key => $toolName)

                                        @php

                                            $displayName =
                                                is_string($toolName)
                                                && filter_var($toolName, FILTER_VALIDATE_URL)
                                                    ? ucfirst($key)
                                                    : $toolName;

                                        @endphp

                                        <label class="ai-tool-option">

                                            <input
                                                type="checkbox"
                                                name="ai_tool[]"
                                                value="{{ $key }}"
                                                class="ai-tool-checkbox"
                                                {{ in_array($key, $selectedTools) ? 'checked' : '' }}
                                            >

                                            <span class="ai-tool-checkmark"></span>

                                            <span class="ai-tool-name">
                                                {{ $displayName }}
                                            </span>

                                        </label>

                                    @endforeach

                                </div>

                            </div>


                            <div class="form-help">

                                <i class="bi bi-info-circle me-1"></i>

                                Select one or multiple AI tools.

                            </div>

                        </div>


                        {{-- ================================================= --}}
                        {{-- IMAGE SECTION --}}
                        {{-- ================================================= --}}

                        <div class="form-group">

                            <label class="form-label-custom">

                                Example Output Image

                                <span class="optional-label">
                                    (Optional)
                                </span>

                            </label>


                            {{-- EXISTING IMAGE --}}

                            @if($prompt->image)

                                <div
                                    class="existing-image-box"
                                    id="existing-img-box"
                                >

                                    <div class="existing-image-left">

                                        <div class="existing-image-preview">

                                            <img
                                                src="{{ asset('storage/' . $prompt->image) }}"
                                                alt="Current Image"
                                            >

                                        </div>


                                        <div>

                                            <div class="existing-image-title">

                                                <i class="bi bi-image me-1"></i>

                                                Current Saved Image

                                            </div>

                                            <div class="existing-image-text">

                                                Upload a new image to replace it.

                                            </div>

                                        </div>

                                    </div>


                                    <div class="remove-image-area">

                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="remove_image"
                                            value="1"
                                            id="remove_image_check"
                                        >

                                        <label
                                            class="remove-image-label"
                                            for="remove_image_check"
                                        >

                                            <i class="bi bi-trash3 me-1"></i>

                                            Remove Current Image

                                        </label>

                                    </div>

                                </div>

                            @endif


                            {{-- NEW IMAGE UPLOAD --}}

                            <div class="image-upload-wrapper">

                                <div class="image-upload-input-area">

                                    <i class="bi bi-cloud-arrow-up image-upload-icon"></i>

                                    <div class="image-upload-content">

                                        <div class="image-upload-title">
                                            Upload New Image
                                        </div>

                                        <div class="image-upload-text">
                                            Choose a new image to replace the existing one.
                                        </div>

                                    </div>

                                    <input
                                        type="file"
                                        name="image"
                                        id="edit_image_input"
                                        class="form-control image-file-input"
                                        accept="image/*"
                                    >

                                </div>


                                <button
                                    type="button"
                                    class="clear-image-btn"
                                    onclick="clearEditInput()"
                                >
                                    <i class="bi bi-x-circle me-1"></i>
                                    Clear
                                </button>

                            </div>


                            <div class="form-help">

                                <i class="bi bi-info-circle me-1"></i>

                                Upload a new image only if you want to replace the current image.

                            </div>

                        </div>

                    </div>

                </div>


                {{-- ===================================================== --}}
                {{-- FORM ACTIONS --}}
                {{-- ===================================================== --}}

                <div class="form-actions">

                    <a
                        href="{{ route('admin.prompts.index') }}"
                        class="cancel-btn"
                    >
                        <i class="bi bi-x-circle me-1"></i>
                        Cancel
                    </a>


                    <button
                        type="submit"
                        class="update-prompt-btn"
                    >

                        <i class="bi bi-check-circle me-1"></i>

                        Update Prompt

                    </button>

                </div>


            </form>

        </div>

    </div>

</div>


{{-- ========================================================= --}}
{{-- CSS --}}
{{-- ========================================================= --}}

<style>

/*
|--------------------------------------------------------------------------
| MAIN CARD
|--------------------------------------------------------------------------
*/

.prompt-form-card {
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    background: #ffffff;
    padding: 24px;
    height: calc(100vh - 140px);
    overflow-y: auto;
    overflow-x: hidden;
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


/*
|--------------------------------------------------------------------------
| PAGE HEADER
|--------------------------------------------------------------------------
*/

.prompt-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e5e7eb;
}

.prompt-page-header-left {
    display: flex;
    align-items: center;
    gap: 14px;
}

.prompt-page-icon {
    width: 48px;
    height: 48px;
    min-width: 48px;
    border-radius: 12px;
    background: #eef4ff;
    color: #0d6efd;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 21px;
}

.prompt-page-title {
    margin: 0;
    font-size: 22px;
    font-weight: 700;
    color: #1f2937;
}

.prompt-page-subtitle {
    margin-top: 4px;
    font-size: 13px;
    color: #6b7280;
}

.back-to-prompts-btn {
    display: inline-flex;
    align-items: center;
    text-decoration: none;
    border: 1px solid #dbe1e8;
    background: #ffffff;
    color: #495057;
    border-radius: 8px;
    padding: 9px 14px;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.2s ease;
}

.back-to-prompts-btn:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #0d6efd;
}


/*
|--------------------------------------------------------------------------
| ALERT
|--------------------------------------------------------------------------
*/

.modern-alert {
    border-radius: 10px;
    border: 1px solid #fecaca;
    margin-bottom: 22px;
    font-size: 13px;
}


/*
|--------------------------------------------------------------------------
| BASIC INFORMATION
|--------------------------------------------------------------------------
*/

.prompt-basic-box {
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
}

.form-section-heading {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 18px;
}

.section-heading-icon {
    width: 36px;
    height: 36px;
    min-width: 36px;
    border-radius: 9px;
    background: #eaf2ff;
    color: #0d6efd;
    display: flex;
    align-items: center;
    justify-content: center;
}

.form-section-title {
    font-size: 16px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 3px;
}

.form-section-subtitle {
    font-size: 12px;
    color: #6b7280;
}

.form-label-custom {
    display: block;
    font-size: 13px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 8px;
}

.required-star {
    color: #dc3545;
    margin-left: 2px;
}

.optional-label {
    color: #9ca3af;
    font-size: 11px;
    font-weight: 500;
    margin-left: 3px;
}

.modern-form-control {
    min-height: 46px;
    border: 1px solid #d7dce2;
    border-radius: 8px;
    font-size: 13px;
    color: #374151;
    background: #ffffff;
    transition: all 0.2s ease;
}

.modern-form-control:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.10);
}


/*
|--------------------------------------------------------------------------
| PROMPTS SECTION
|--------------------------------------------------------------------------
*/

.prompts-section-header {
    margin-bottom: 12px;
}

.prompts-section-heading {
    display: flex;
    align-items: center;
    gap: 12px;
}

.prompts-section-icon {
    width: 38px;
    height: 38px;
    min-width: 38px;
    border-radius: 9px;
    background: #eef4ff;
    color: #0d6efd;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 17px;
}


/*
|--------------------------------------------------------------------------
| PROMPT CARD
|--------------------------------------------------------------------------
*/

.prompt-card {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #ffffff;
    margin-bottom: 22px;
    overflow: visible;
}

.prompt-card-header {
    min-height: 54px;
    padding: 12px 16px;
    border-bottom: 1px solid #e5e7eb;
    background: #f8fafc;
    border-radius: 12px 12px 0 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.prompt-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 28px;
    padding: 4px 10px;
    border-radius: 7px;
    background: #eaf2ff;
    color: #0d6efd;
    font-size: 12px;
    font-weight: 700;
}

.prompt-card-label {
    font-size: 13px;
    font-weight: 600;
    color: #475569;
}

.prompt-card-body {
    padding: 20px;
}


/*
|--------------------------------------------------------------------------
| TEXTAREA
|--------------------------------------------------------------------------
*/

.modern-textarea {
    width: 100%;
    min-height: 180px;
    resize: vertical;
    border: 1px solid #d7dce2;
    border-radius: 9px;
    padding: 13px 14px;
    font-size: 13px;
    line-height: 1.65;
    color: #374151;
    transition: all 0.2s ease;
}

.modern-textarea:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.10);
}

.form-help {
    font-size: 11px;
    color: #6b7280;
    margin-top: 7px;
}

.form-help i {
    color: #0d6efd;
}


/*
|--------------------------------------------------------------------------
| VARIABLE SETTINGS
|--------------------------------------------------------------------------
*/

.variable-settings-box {
    margin-top: 14px;
    padding: 15px;
    border: 1px solid #dbe3ec;
    border-radius: 9px;
    background: #f8fafc;
    display: none;
}

.variable-settings-box.has-variables {
    display: block;
}

.variable-settings-title {
    font-size: 13px;
    font-weight: 700;
    color: #334155;
    margin-bottom: 4px;
}

.variable-settings-help {
    font-size: 11px;
    color: #64748b;
    margin-bottom: 12px;
}

.variable-row {
    padding: 12px;
    margin-bottom: 10px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #ffffff;
}

.variable-row:last-child {
    margin-bottom: 0;
}

.variable-number {
    font-size: 11px;
    font-weight: 700;
    color: #0d6efd;
    margin-bottom: 7px;
}

.variable-example-input {
    min-height: 40px;
    border: 1px solid #d7dce2;
    border-radius: 7px;
    font-size: 12px;
}


/*
|--------------------------------------------------------------------------
| AI TOOLS DROPDOWN
|--------------------------------------------------------------------------
*/

.ai-tool-dropdown {
    position: relative;
    width: 100%;
}

.ai-tool-toggle {
    width: 100%;
    min-height: 46px;
    padding: 10px 13px;
    background: #ffffff;
    border: 1px solid #d7dce2;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    text-align: left;
    color: #495057;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.ai-tool-toggle:hover {
    border-color: #86b7fe;
}

.ai-tool-toggle.active {
    border-color: #86b7fe;
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.10);
}

.ai-tool-toggle i {
    transition: transform 0.2s ease;
}

.ai-tool-toggle.active i {
    transform: rotate(180deg);
}

.ai-tool-menu {
    position: absolute;
    top: calc(100% + 5px);
    left: 0;
    width: 100%;
    background: #ffffff;
    border: 1px solid #dbe2ea;
    border-radius: 9px;
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.14);
    padding: 6px;
    display: none;
    z-index: 99999;
    max-height: 260px;
    overflow-y: auto;
}

.ai-tool-menu.show {
    display: block;
}

.ai-tool-option {
    position: relative;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 11px;
    margin: 0;
    border-radius: 7px;
    cursor: pointer;
    font-size: 13px;
    color: #374151;
    transition: background 0.15s ease;
}

.ai-tool-option:hover {
    background: #f1f5f9;
}

.ai-tool-checkbox {
    position: absolute;
    opacity: 0;
    width: 1px;
    height: 1px;
}

.ai-tool-checkmark {
    width: 18px;
    height: 18px;
    min-width: 18px;
    border: 2px solid #adb5bd;
    border-radius: 4px;
    background: #ffffff;
    position: relative;
    transition: all 0.15s ease;
}

.ai-tool-checkbox:checked + .ai-tool-checkmark {
    background: #0d6efd;
    border-color: #0d6efd;
}

.ai-tool-checkbox:checked + .ai-tool-checkmark::after {
    content: "";
    position: absolute;
    left: 4px;
    top: 0px;
    width: 6px;
    height: 10px;
    border: solid #ffffff;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.ai-tool-checkbox:checked ~ .ai-tool-name {
    font-weight: 600;
    color: #0d6efd;
}

.ai-tool-menu::-webkit-scrollbar {
    width: 7px;
}

.ai-tool-menu::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.ai-tool-menu::-webkit-scrollbar-thumb {
    background: #adb5bd;
    border-radius: 10px;
}

.ai-tool-menu::-webkit-scrollbar-thumb:hover {
    background: #6c757d;
}


/*
|--------------------------------------------------------------------------
| EXISTING IMAGE
|--------------------------------------------------------------------------
*/

.existing-image-box {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    padding: 14px;
    margin-bottom: 13px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #f8fafc;
}

.existing-image-left {
    display: flex;
    align-items: center;
    gap: 13px;
}

.existing-image-preview {
    width: 78px;
    height: 78px;
    min-width: 78px;
    border-radius: 9px;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    background: #ffffff;
}

.existing-image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.existing-image-title {
    font-size: 13px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 4px;
}

.existing-image-text {
    font-size: 11px;
    color: #6b7280;
}

.remove-image-area {
    display: flex;
    align-items: center;
    gap: 7px;
    white-space: nowrap;
}

.remove-image-area .form-check-input {
    margin: 0;
    cursor: pointer;
}

.remove-image-label {
    margin: 0;
    font-size: 12px;
    font-weight: 600;
    color: #dc3545;
    cursor: pointer;
}


/*
|--------------------------------------------------------------------------
| IMAGE UPLOAD
|--------------------------------------------------------------------------
*/

.image-upload-wrapper {
    display: flex;
    align-items: stretch;
    gap: 10px;
}

.image-upload-input-area {
    flex: 1;
    min-height: 68px;
    border: 1px dashed #cbd5e1;
    border-radius: 9px;
    background: #f8fafc;
    padding: 11px 13px;
    display: flex;
    align-items: center;
    gap: 11px;
}

.image-upload-icon {
    font-size: 24px;
    color: #0d6efd;
}

.image-upload-content {
    min-width: 150px;
}

.image-upload-title {
    font-size: 12px;
    font-weight: 700;
    color: #374151;
}

.image-upload-text {
    font-size: 10px;
    color: #6b7280;
    margin-top: 2px;
}

.image-file-input {
    flex: 1;
    min-width: 180px;
    font-size: 12px;
    border: 1px solid #d7dce2;
    border-radius: 7px;
    background: #ffffff;
}

.clear-image-btn {
    min-width: 105px;
    border: 1px solid #d7dce2;
    border-radius: 8px;
    background: #ffffff;
    color: #6b7280;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.clear-image-btn:hover {
    border-color: #adb5bd;
    color: #dc3545;
    background: #fff8f8;
}


/*
|--------------------------------------------------------------------------
| FORM ACTIONS
|--------------------------------------------------------------------------
*/

.form-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    padding-top: 20px;
    margin-top: 4px;
    border-top: 1px solid #e5e7eb;
}

.cancel-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 42px;
    padding: 9px 18px;
    border: 1px solid #d7dce2;
    border-radius: 8px;
    background: #ffffff;
    color: #495057;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.2s ease;
}

.cancel-btn:hover {
    background: #f8fafc;
    color: #374151;
    border-color: #cbd5e1;
}

.update-prompt-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 42px;
    padding: 9px 20px;
    border: 0;
    border-radius: 8px;
    background: #198754;
    color: #ffffff;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
}

.update-prompt-btn:hover {
    background: #157347;
    color: #ffffff;
}


/*
|--------------------------------------------------------------------------
| RESPONSIVE
|--------------------------------------------------------------------------
*/

@media (max-width: 768px) {

    .prompt-form-card {
        padding: 16px;
        height: calc(100vh - 110px);
    }

    .prompt-page-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .back-to-prompts-btn {
        width: 100%;
        justify-content: center;
    }

    .prompt-basic-box,
    .prompt-card-body {
        padding: 15px;
    }

    .existing-image-box {
        align-items: flex-start;
        flex-direction: column;
    }

    .remove-image-area {
        width: 100%;
    }

    .image-upload-wrapper {
        flex-direction: column;
    }

    .image-upload-input-area {
        flex-wrap: wrap;
    }

    .image-file-input {
        width: 100%;
        min-width: 100%;
    }

    .clear-image-btn {
        min-height: 40px;
        width: 100%;
    }

    .form-actions {
        flex-direction: column-reverse;
    }

    .cancel-btn,
    .update-prompt-btn {
        width: 100%;
    }
}

</style>


{{-- ========================================================= --}}
{{-- JAVASCRIPT --}}
{{-- ========================================================= --}}

<script>

const VARIABLE_META_MARKER = '<!--AI_PROMPT_VARIABLES:';

const existingVariableMeta = @json($editVariableMeta);


/*
|--------------------------------------------------------------------------
| Strip saved variable metadata
|--------------------------------------------------------------------------
*/

function stripVariableMeta(text) {

    return String(text || '')
        .replace(
            /<!--AI_PROMPT_VARIABLES:[\s\S]*?-->\s*$/i,
            ''
        )
        .trimEnd();
}


/*
|--------------------------------------------------------------------------
| Detect [1], [2], [3] variables
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| Escape HTML attribute
|--------------------------------------------------------------------------
*/

function escAttr(value) {

    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

}


/*
|--------------------------------------------------------------------------
| Render variable settings
|--------------------------------------------------------------------------
*/

function renderVariableSettings(text) {

    const box =
        document.getElementById('edit-variable-settings');

    const list =
        box
            ? box.querySelector('.variable-settings-list')
            : null;

    if (!box || !list) {
        return;
    }


    const vars = detectPromptVariables(text);

    list.innerHTML = '';


    if (!vars.length) {

        box.classList.remove('has-variables');

        return;
    }


    box.classList.add('has-variables');


    vars.forEach(function (variable) {

        const savedMeta =
            existingVariableMeta[variable] || {};

        const example =
            savedMeta.example || '';


        list.insertAdjacentHTML(
            'beforeend',

            `
            <div
                class="variable-row"
                data-variable-row="${variable}"
            >

                <div class="variable-number">
                    Variable [${variable}]
                </div>

                <label class="form-label fw-semibold mb-1">
                    Example / Dummy Value
                </label>

                <input
                    type="text"
                    class="form-control variable-example-input"
                    data-variable="${variable}"
                    value="${escAttr(example)}"
                    placeholder="e.g. young Indian woman"
                >

            </div>
            `
        );

    });

}


/*
|--------------------------------------------------------------------------
| Collect variable metadata
|--------------------------------------------------------------------------
*/

function collectVariableMeta() {

    const meta = {};

    document
        .querySelectorAll(
            '#edit-variable-settings .variable-row'
        )
        .forEach(function (row) {

            const variable =
                row.dataset.variableRow;

            const input =
                row.querySelector(
                    '.variable-example-input'
                );

            meta[variable] = {

                example:
                    input
                        ? input.value.trim()
                        : ''

            };

        });


    return meta;
}


/*
|--------------------------------------------------------------------------
| Validate variable settings
|--------------------------------------------------------------------------
*/

function validateVariableSettings() {

    const rows =
        document.querySelectorAll(
            '#edit-variable-settings .variable-row'
        );


    for (const row of rows) {

        const variable =
            row.dataset.variableRow;

        const input =
            row.querySelector(
                '.variable-example-input'
            );


        if (!input || !input.value.trim()) {

            if (input) {
                input.focus();
            }

            alert(
                `Please enter an example / dummy value for Variable [${variable}].`
            );

            return false;
        }

    }


    return true;
}


/*
|--------------------------------------------------------------------------
| Append variable metadata
|--------------------------------------------------------------------------
*/

function appendVariableMeta(text, meta) {

    const cleanText =
        stripVariableMeta(text);


    if (!detectPromptVariables(cleanText).length) {

        return cleanText;
    }


    return (
        cleanText +
        VARIABLE_META_MARKER +
        encodeURIComponent(
            JSON.stringify(meta)
        ) +
        '-->'
    );

}


/*
|--------------------------------------------------------------------------
| DOM READY
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'DOMContentLoaded',
    function () {


        /*
        |--------------------------------------------------------------------------
        | VARIABLE SETTINGS
        |--------------------------------------------------------------------------
        */

        const editPromptText =
            document.getElementById(
                'edit_prompt_text'
            );


        if (editPromptText) {

            renderVariableSettings(
                editPromptText.value
            );


            editPromptText.addEventListener(
                'input',
                function () {

                    renderVariableSettings(
                        stripVariableMeta(
                            this.value
                        )
                    );

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | FORM SUBMIT
        |--------------------------------------------------------------------------
        */

        const form =
            document.getElementById(
                'edit-prompt-form'
            );


        if (form && editPromptText) {

            form.addEventListener(
                'submit',
                function (event) {


                    if (!validateVariableSettings()) {

                        event.preventDefault();

                        return;
                    }


                    editPromptText.value =
                        appendVariableMeta(
                            editPromptText.value,
                            collectVariableMeta()
                        );

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | AI TOOLS DROPDOWN
        |--------------------------------------------------------------------------
        */

        const dropdownBtn =
            document.getElementById(
                'aiToolsDropdownBtn'
            );

        const dropdownMenu =
            document.getElementById(
                'aiToolsDropdownMenu'
            );

        const selectedText =
            document.getElementById(
                'aiToolsSelectedText'
            );


        if (
            !dropdownBtn ||
            !dropdownMenu ||
            !selectedText
        ) {

            return;

        }


        const checkboxes =
            dropdownMenu.querySelectorAll(
                '.ai-tool-checkbox'
            );


        /*
        |--------------------------------------------------------------------------
        | Update selected AI tools text
        |--------------------------------------------------------------------------
        */

        function updateSelectedText() {

            const selected =
                dropdownMenu.querySelectorAll(
                    '.ai-tool-checkbox:checked'
                );


            if (selected.length === 0) {

                selectedText.textContent =
                    'Select AI Tools';

                return;
            }


            const names = [];


            selected.forEach(
                function (checkbox) {

                    const option =
                        checkbox.closest(
                            '.ai-tool-option'
                        );

                    if (!option) {
                        return;
                    }


                    const name =
                        option.querySelector(
                            '.ai-tool-name'
                        );


                    if (name) {

                        names.push(
                            name.textContent.trim()
                        );

                    }

                }
            );


            if (names.length <= 3) {

                selectedText.textContent =
                    names.join(', ');

            } else {

                selectedText.textContent =
                    names.length +
                    ' AI Tools Selected';

            }

        }


        /*
        |--------------------------------------------------------------------------
        | Open / close dropdown
        |--------------------------------------------------------------------------
        */

        dropdownBtn.addEventListener(
            'click',
            function (event) {

                event.preventDefault();

                event.stopPropagation();

                dropdownMenu.classList.toggle('show');

                dropdownBtn.classList.toggle('active');

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Checkbox change
        |--------------------------------------------------------------------------
        */

        checkboxes.forEach(
            function (checkbox) {

                checkbox.addEventListener(
                    'change',
                    updateSelectedText
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Close when clicking outside
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'click',
            function (event) {

                if (
                    !dropdownBtn.contains(event.target) &&
                    !dropdownMenu.contains(event.target)
                ) {

                    dropdownMenu.classList.remove('show');

                    dropdownBtn.classList.remove('active');

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Initial selected tools
        |--------------------------------------------------------------------------
        */

        updateSelectedText();

    }
);


/*
|--------------------------------------------------------------------------
| Clear selected image
|--------------------------------------------------------------------------
*/

function clearEditInput() {

    const input =
        document.getElementById(
            'edit_image_input'
        );


    if (input) {

        input.value = '';

    }

}

</script>

@endsection
```
