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

    if (preg_match('/<!--AI_PROMPT_VARIABLES:([\s\S]*?)-->\s*$/i', $editPromptRawText, $editMetaMatch)) {
        $decodedEditMeta = json_decode(urldecode($editMetaMatch[1]), true);
        if (is_array($decodedEditMeta)) {
            $editVariableMeta = $decodedEditMeta;
        }
        $editPromptRawText = preg_replace('/<!--AI_PROMPT_VARIABLES:[\s\S]*?-->\s*$/i', '', $editPromptRawText);
    }

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

            {{-- PAGE HEADER --}}
            <div class="prompt-page-header">
                <div class="prompt-page-header-left">
                    <div class="prompt-page-icon">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <div>
                        <h4 class="prompt-page-title">Edit Prompt</h4>
                        <p class="prompt-page-subtitle mb-0">Update your prompt details, AI tools and example output.</p>
                    </div>
                </div>

                <a href="{{ route('admin.prompts.index') }}" class="back-to-prompts-btn">
                    <i class="bi bi-arrow-left me-1"></i> Back to Prompts
                </a>
            </div>

            {{-- VALIDATION ERRORS --}}
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

            {{-- STYLISH CUSTOM ALERT BANNER (HIDDEN BY DEFAULT) --}}
            <div id="custom-stylish-alert" class="alert alert-danger modern-alert d-none mb-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-circle-fill me-2 fs-5"></i>
                    <div id="custom-alert-message"></div>
                </div>
            </div>

            {{-- FORM --}}
            <form
                action="{{ route('admin.prompts.update', $prompt->id) }}"
                method="POST"
                enctype="multipart/form-data"
                id="edit-prompt-form"
            >
                @csrf
                @method('PUT')

                {{-- BASIC INFORMATION --}}
                <div class="prompt-basic-box">
                    <div class="form-section-heading">
                        <div class="section-heading-icon">
                            <i class="bi bi-info-circle"></i>
                        </div>
                        <div>
                            <h5 class="form-section-title">Basic Information</h5>
                            <p class="form-section-subtitle">Select the category and give your prompt collection a title.</p>
                        </div>
                    </div>

                    <div class="row g-3">
                        {{-- CATEGORY --}}
                        <div class="col-md-6">
                            <label class="form-label-custom">Category <span class="required-star">*</span></label>
                            <select name="category_id" class="form-control modern-form-control" required>
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
                            <label class="form-label-custom">Prompt Collection / Topic Title <span class="required-star">*</span></label>
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

                {{-- PROMPT DETAILS --}}
                <div class="prompts-section-header">
                    <div class="prompts-section-heading">
                        <div class="prompts-section-icon">
                            <i class="bi bi-chat-square-text"></i>
                        </div>
                        <div>
                            <h5 class="form-section-title mb-0">Prompt Details</h5>
                            <p class="form-section-subtitle mb-0">Edit the prompt text and user input variables.</p>
                        </div>
                    </div>
                </div>

                <div class="prompt-card">
                    <div class="prompt-card-header">
                        <div class="d-flex align-items-center gap-2">
                            <span class="prompt-number">Prompt #1</span>
                            <span class="prompt-card-label">Edit Prompt Content</span>
                        </div>
                    </div>

                    <div class="prompt-card-body">

                        {{-- AI TOOL DROPDOWN --}}
                        <div class="form-group mb-4">
                            <label class="form-label-custom">AI Tool / Platform <span class="required-star">*</span></label>
                            
                            <div class="ai-tool-dropdown">
                                <button type="button" id="aiToolsDropdownBtn" class="ai-tool-toggle">
                                    <span id="aiToolsSelectedText">Select AI Tools</span>
                                    <i class="bi bi-chevron-down"></i>
                                </button>

                                <div id="aiToolsDropdownMenu" class="ai-tool-menu">
                                    @foreach($availableTools as $key => $toolName)
                                        <label class="ai-tool-option">
                                            <input
                                                type="checkbox"
                                                name="ai_tool[]"
                                                value="{{ $key }}"
                                                class="ai-tool-checkbox"
                                                {{ in_array($key, $selectedTools) ? 'checked' : '' }}
                                            >
                                            <span class="ai-tool-checkmark"></span>
                                            <span class="ai-tool-name">{{ $toolName }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="form-help"><i class="bi bi-info-circle me-1"></i> Select one or multiple AI platforms for this prompt.</div>
                        </div>

                        {{-- STEP LABEL --}}
                        <div class="form-group mb-4">
                            <label class="form-label-custom">Prompt Step / Title <span class="optional-label">(Optional)</span></label>
                            <input
                                type="text"
                                name="label"
                                class="form-control modern-form-control"
                                value="{{ old('label', $prompt->label) }}"
                                placeholder="e.g. Step 1: Catchy Headline Generator"
                            >
                            <div class="form-help"><i class="bi bi-info-circle me-1"></i> Add a label if this prompt represents a specific step.</div>
                        </div>

                        {{-- PROMPT TEXT --}}
                        <div class="form-group mb-4">
                            <label class="form-label-custom">Prompt Text <span class="required-star">*</span></label>
                            <textarea
                                name="prompt_text"
                                id="edit_prompt_text"
                                class="form-control modern-textarea"
                                rows="6"
                                placeholder="Enter the complete prompt that the AI should process..."
                                required
                            >{{ $editPromptRawText }}</textarea>

                            <div class="form-help">
                                <i class="bi bi-lightbulb me-1"></i> Use placeholders like <code>[1]</code>, <code>[2]</code>, <code>[3]</code>. These will automatically become input fields for users.
                            </div>

                            {{-- VARIABLE SETTINGS --}}
                            <div class="variable-settings-box" id="edit-variable-settings">
                                <div class="variable-settings-title">
                                    <i class="bi bi-sliders2-vertical me-1"></i> User Input Settings
                                </div>
                                <div class="variable-settings-help">Enter only an example / dummy value for each placeholder.</div>
                                <div class="variable-settings-list"></div>
                            </div>
                        </div>

                        {{-- SINGLE NORMAL IMAGE UPLOAD SECTION --}}
                        <div class="form-group">
                            <label class="form-label-custom">Example Output Image <span class="optional-label">(Optional)</span></label>

                            <div class="image-upload-wrapper">
                                <div class="image-upload-input-area">
                                    <input
                                        type="file"
                                        name="image"
                                        id="edit_image_input"
                                        class="form-control image-file-input"
                                        accept="image/*"
                                    >
                                </div>

                                <button type="button" class="clear-image-btn" onclick="clearEditInput()">
                                    <i class="bi bi-x-circle me-1"></i> Clear
                                </button>
                            </div>

                            @if($prompt->image)
                                <div class="mt-2 d-flex align-items-center gap-3">
                                    <small class="text-muted">
                                        Current file: <strong>{{ basename($prompt->image) }}</strong>
                                    </small>
                                    <div class="form-check m-0">
                                        <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_image_check">
                                        <label class="form-check-label text-danger fw-semibold" for="remove_image_check" style="font-size: 12px; cursor: pointer;">
                                            Remove current image
                                        </label>
                                    </div>
                                </div>
                            @endif

                            <div class="form-help"><i class="bi bi-image-outline me-1"></i> Upload an example output image if available.</div>
                        </div>

                    </div>
                </div>

                {{-- FORM ACTIONS --}}
                <div class="form-actions">
                    <a href="{{ route('admin.prompts.index') }}" class="cancel-btn">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </a>
                    <button type="submit" class="update-prompt-btn">
                        <i class="bi bi-check-circle me-1"></i> Update Prompt
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- CSS STYLES --}}
<style>
.prompt-form-card { border: 1px solid #e5e7eb; border-radius: 14px; background: #ffffff; padding: 24px; height: calc(100vh - 140px); overflow-y: auto; overflow-x: hidden; }
.prompt-page-header { display: flex; align-items: center; justify-content: space-between; gap: 20px; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid #e5e7eb; }
.prompt-page-header-left { display: flex; align-items: center; gap: 14px; }
.prompt-page-icon { width: 48px; height: 48px; min-width: 48px; border-radius: 12px; background: #eef4ff; color: #0d6efd; display: flex; align-items: center; justify-content: center; font-size: 21px; }
.prompt-page-title { margin: 0; font-size: 22px; font-weight: 700; color: #1f2937; }
.prompt-page-subtitle { margin-top: 4px; font-size: 13px; color: #6b7280; }
.back-to-prompts-btn { display: inline-flex; align-items: center; text-decoration: none; border: 1px solid #dbe1e8; background: #ffffff; color: #495057; border-radius: 8px; padding: 9px 14px; font-size: 13px; font-weight: 600; }
.modern-alert { border-radius: 10px; border: 1px solid #fecaca; margin-bottom: 22px; font-size: 13px; }
.prompt-basic-box { background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; margin-bottom: 24px; }
.form-section-heading { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 18px; }
.section-heading-icon { width: 36px; height: 36px; min-width: 36px; border-radius: 9px; background: #eaf2ff; color: #0d6efd; display: flex; align-items: center; justify-content: center; }
.form-section-title { font-size: 16px; font-weight: 700; color: #1f2937; margin-bottom: 3px; }
.form-section-subtitle { font-size: 12px; color: #6b7280; }
.form-label-custom { display: block; font-size: 13px; font-weight: 700; color: #374151; margin-bottom: 8px; }
.required-star { color: #dc3545; margin-left: 2px; }
.optional-label { color: #9ca3af; font-size: 11px; font-weight: 500; margin-left: 3px; }
.modern-form-control { min-height: 46px; border: 1px solid #d7dce2; border-radius: 8px; font-size: 13px; color: #374151; background: #ffffff; }
.prompts-section-header { margin-bottom: 12px; }
.prompts-section-heading { display: flex; align-items: center; gap: 12px; }
.prompts-section-icon { width: 38px; height: 38px; min-width: 38px; border-radius: 9px; background: #eef4ff; color: #0d6efd; display: flex; align-items: center; justify-content: center; font-size: 17px; }
.prompt-card { border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff; margin-bottom: 22px; }
.prompt-card-header { min-height: 54px; padding: 12px 16px; border-bottom: 1px solid #e5e7eb; background: #f8fafc; border-radius: 12px 12px 0 0; display: flex; align-items: center; justify-content: space-between; }
.prompt-number { display: inline-flex; align-items: center; justify-content: center; min-height: 28px; padding: 4px 10px; border-radius: 7px; background: #eaf2ff; color: #0d6efd; font-size: 12px; font-weight: 700; }
.prompt-card-label { font-size: 13px; font-weight: 600; color: #475569; }
.prompt-card-body { padding: 20px; }
.modern-textarea { width: 100%; min-height: 160px; resize: vertical; border: 1px solid #d7dce2; border-radius: 9px; padding: 13px 14px; font-size: 13px; line-height: 1.65; color: #374151; }
.form-help { font-size: 11px; color: #6b7280; margin-top: 7px; }
.form-help i { color: #0d6efd; }
.variable-settings-box { margin-top: 14px; padding: 15px; border: 1px solid #dbe3ec; border-radius: 9px; background: #f8fafc; display: none; }
.variable-settings-box.has-variables { display: block; }
.variable-settings-title { font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 4px; }
.variable-settings-help { font-size: 11px; color: #64748b; margin-bottom: 12px; }
.variable-row { padding: 12px; margin-bottom: 10px; border: 1px solid #e2e8f0; border-radius: 8px; background: #ffffff; }
.variable-number { font-size: 11px; font-weight: 700; color: #0d6efd; margin-bottom: 7px; }
.variable-example-input { min-height: 40px; border: 1px solid #d7dce2; border-radius: 7px; font-size: 12px; }
.ai-tool-dropdown { position: relative; width: 100%; }
.ai-tool-toggle { width: 100%; min-height: 46px; padding: 10px 13px; background: #ffffff; border: 1px solid #d7dce2; border-radius: 8px; display: flex; align-items: center; justify-content: space-between; font-size: 13px; color: #495057; cursor: pointer; }
.ai-tool-menu { position: absolute; top: calc(100% + 5px); left: 0; width: 100%; background: #ffffff; border: 1px solid #dbe2ea; border-radius: 9px; box-shadow: 0 10px 28px rgba(15, 23, 42, 0.14); padding: 6px; display: none; z-index: 99999; max-height: 260px; overflow-y: auto; }
.ai-tool-menu.show { display: block; }
.ai-tool-option { position: relative; display: flex; align-items: center; gap: 10px; padding: 10px 11px; border-radius: 7px; cursor: pointer; font-size: 13px; color: #374151; }
.ai-tool-option:hover { background: #f1f5f9; }
.ai-tool-checkbox { position: absolute; opacity: 0; width: 1px; height: 1px; }
.ai-tool-checkmark { width: 18px; height: 18px; min-width: 18px; border: 2px solid #adb5bd; border-radius: 4px; background: #ffffff; position: relative; }
.ai-tool-checkbox:checked + .ai-tool-checkmark { background: #0d6efd; border-color: #0d6efd; }
.ai-tool-checkbox:checked + .ai-tool-checkmark::after { content: ""; position: absolute; left: 4px; top: 0px; width: 6px; height: 10px; border: solid #ffffff; border-width: 0 2px 2px 0; transform: rotate(45deg); }
.image-upload-wrapper { display: flex; align-items: center; gap: 10px; border: 1px dashed #cbd5e1; border-radius: 9px; background: #f8fafc; padding: 8px; }
.image-upload-input-area { flex: 1; }
.image-file-input { font-size: 12px; border: 1px solid #d7dce2; border-radius: 7px; background: #ffffff; }
.clear-image-btn { min-width: 90px; border: 1px solid #d7dce2; border-radius: 8px; background: #ffffff; color: #6b7280; font-size: 12px; font-weight: 600; padding: 7px 12px; cursor: pointer; }
.clear-image-btn:hover { color: #dc3545; background: #fff8f8; }
.form-actions { display: flex; align-items: center; justify-content: flex-end; gap: 10px; padding-top: 20px; border-top: 1px solid #e5e7eb; }
.cancel-btn, .update-prompt-btn { min-height: 42px; padding: 9px 20px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; }
.cancel-btn { background: #ffffff; border: 1px solid #d7dce2; color: #495057; }
.update-prompt-btn { background: #198754; border: 0; color: #ffffff; font-weight: 700; cursor: pointer; }
</style>

{{-- JAVASCRIPT --}}
<script>
const VARIABLE_META_MARKER = '<!--AI_PROMPT_VARIABLES:';
const existingVariableMeta = @json($editVariableMeta);

function stripVariableMeta(text) {
    return String(text || '').replace(/<!--AI_PROMPT_VARIABLES:[\s\S]*?-->\s*$/i, '').trimEnd();
}

function detectPromptVariables(text) {
    const matches = String(text || '').match(/\[(\d+)\]/g) || [];
    return [...new Set(matches.map(item => item.replace(/\[|\]/g, '')))].sort((a, b) => Number(a) - Number(b));
}

function escAttr(value) {
    return String(value || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function renderVariableSettings(text) {
    const box = document.getElementById('edit-variable-settings');
    const list = box ? box.querySelector('.variable-settings-list') : null;
    if (!box || !list) return;

    const vars = detectPromptVariables(text);
    list.innerHTML = '';

    if (!vars.length) {
        box.classList.remove('has-variables');
        return;
    }

    box.classList.add('has-variables');

    vars.forEach(variable => {
        const savedMeta = existingVariableMeta[variable] || {};
        const example = savedMeta.example || '';

        list.insertAdjacentHTML('beforeend', `
            <div class="variable-row" data-variable-row="${variable}">
                <div class="variable-number">Variable [${variable}]</div>
                <label class="form-label-custom mb-1">Example / Dummy Value</label>
                <input
                    type="text"
                    class="form-control variable-example-input"
                    data-variable="${variable}"
                    value="${escAttr(example)}"
                    placeholder="e.g. young Indian woman"
                >
            </div>
        `);
    });
}

function collectVariableMeta() {
    const meta = {};
    document.querySelectorAll('#edit-variable-settings .variable-row').forEach(row => {
        const variable = row.dataset.variableRow;
        const input = row.querySelector('.variable-example-input');
        meta[variable] = { example: input ? input.value.trim() : '' };
    });
    return meta;
}

// Stylish In-Page Alert Notification Function (Replaces standard browser popup)
function showCustomAlert(message) {
    const alertBox = document.getElementById('custom-stylish-alert');
    const messageBox = document.getElementById('custom-alert-message');
    
    if (alertBox && messageBox) {
        messageBox.textContent = message;
        alertBox.classList.remove('d-none');
        
        // Scroll smoothly to the top to see the alert
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

function hideCustomAlert() {
    const alertBox = document.getElementById('custom-stylish-alert');
    if (alertBox) {
        alertBox.classList.add('d-none');
    }
}

function validateVariableSettings() {
    const rows = document.querySelectorAll('#edit-variable-settings .variable-row');
    for (const row of rows) {
        const variable = row.dataset.variableRow;
        const input = row.querySelector('.variable-example-input');
        if (!input || !input.value.trim()) {
            if (input) {
                input.focus();
                input.style.borderColor = '#dc3545';
                input.style.boxShadow = '0 0 0 3px rgba(220, 53, 69, 0.1)';
            }
            showCustomAlert(`Please enter an example / dummy value for Variable [${variable}].`);
            return false;
        } else {
            if (input) {
                input.style.borderColor = '';
                input.style.boxShadow = '';
            }
        }
    }
    hideCustomAlert();
    return true;
}

function appendVariableMeta(text, meta) {
    const cleanText = stripVariableMeta(text);
    if (!detectPromptVariables(cleanText).length) return cleanText;
    return cleanText + VARIABLE_META_MARKER + encodeURIComponent(JSON.stringify(meta)) + '-->';
}

document.addEventListener('DOMContentLoaded', function () {
    const editPromptText = document.getElementById('edit_prompt_text');
    if (editPromptText) {
        renderVariableSettings(editPromptText.value);
        editPromptText.addEventListener('input', function () {
            renderVariableSettings(stripVariableMeta(this.value));
        });
    }

    const form = document.getElementById('edit-prompt-form');
    if (form && editPromptText) {
        form.addEventListener('submit', function (event) {
            if (!validateVariableSettings()) {
                event.preventDefault();
                return;
            }
            editPromptText.value = appendVariableMeta(editPromptText.value, collectVariableMeta());
        });
    }

    // AI Tools Dropdown Toggle
    const dropdownBtn = document.getElementById('aiToolsDropdownBtn');
    const dropdownMenu = document.getElementById('aiToolsDropdownMenu');
    const selectedText = document.getElementById('aiToolsSelectedText');

    if (dropdownBtn && dropdownMenu && selectedText) {
        const checkboxes = dropdownMenu.querySelectorAll('.ai-tool-checkbox');

        function updateSelectedText() {
            const selected = dropdownMenu.querySelectorAll('.ai-tool-checkbox:checked');
            if (selected.length === 0) {
                selectedText.textContent = 'Select AI Tools';
                return;
            }

            const names = [];
            selected.forEach(checkbox => {
                const option = checkbox.closest('.ai-tool-option');
                if (option) {
                    const name = option.querySelector('.ai-tool-name');
                    if (name) names.push(name.textContent.trim());
                }
            });

            selectedText.textContent = names.length <= 3 ? names.join(', ') : names.length + ' AI Tools Selected';
        }

        dropdownBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
        });

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectedText);
        });

        document.addEventListener('click', function (e) {
            if (!dropdownBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.remove('show');
            }
        });

        updateSelectedText();
    }
});

function clearEditInput() {
    const input = document.getElementById('edit_image_input');
    if (input) input.value = '';
}
</script>

@endsection