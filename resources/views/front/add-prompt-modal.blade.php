
<div class="modal fade"
    id="addPromptModal"
    tabindex="-1"
    aria-labelledby="addPromptModalLabel"
    aria-hidden="true"
    data-bs-backdrop="static"
    data-bs-keyboard="false">

    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable frontend-prompt-modal-dialog">
        <div class="modal-content frontend-prompt-modal-content">

            <div class="modal-header frontend-prompt-modal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="frontend-prompt-header-icon">
                        <i class="bi bi-stars"></i>
                    </div>
                    <div>
                        <h5 class="modal-title frontend-prompt-modal-title mb-1" id="addPromptModalLabel">
                            Submit New Prompt
                        </h5>
                        <p class="frontend-prompt-modal-subtitle mb-0">
                            Share your useful AI prompt with the community
                        </p>
                    </div>
                </div>

                <button type="button"
                    class="btn-close btn-close-white frontend-prompt-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            <form action="{{ route('prompts.store') }}"
                method="POST"
                enctype="multipart/form-data">

                @csrf

                <div class="modal-body frontend-prompt-modal-body">

                    {{-- Basic Information --}}
                    <div class="frontend-form-section">
                        <div class="frontend-form-section-heading">
                            <div class="frontend-section-icon">
                                <i class="bi bi-info-circle"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Basic Information</h6>
                                <p class="mb-0">Add the basic details of your prompt.</p>
                            </div>
                        </div>

                        <div class="frontend-form-group">
                            <label for="title" class="frontend-form-label">
                                Prompt Title <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                class="form-control frontend-form-control @error('title') is-invalid @enderror"
                                id="title"
                                name="title"
                                value="{{ old('title') }}"
                                placeholder="Enter prompt title..."
                                required>

                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="frontend-form-group mb-0">
                            <label for="category_id" class="frontend-form-label">
                                Category <span class="text-danger">*</span>
                            </label>

                            <select
                                class="form-select frontend-form-control @error('category_id') is-invalid @enderror"
                                id="category_id"
                                name="category_id"
                                required>
                                <option value="">Select Category</option>

                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                
{{-- AI Tools --}}
<div class="frontend-form-section">
    <div class="frontend-form-section-heading">
        <div class="frontend-section-icon">
            <i class="bi bi-robot"></i>
        </div>

        <div>
            <h6 class="mb-1">AI Tools / Platforms</h6>
            <p class="mb-0">
                Select the AI tools this prompt is useful for.
            </p>
        </div>
    </div>

    @php
        $oldTools = old('ai_tools', old('ai_tool', []));

        if (!is_array($oldTools)) {
            $oldTools = [$oldTools];
        }

        $availableAiTools = availableTools();
    @endphp

    <div class="frontend-form-group mb-0">
        <label class="frontend-form-label">
            AI Tools / Platforms
            <span class="text-danger">*</span>
        </label>

        <div class="frontend-ai-tools-dropdown" id="aiToolsDropdown">
            <button
                type="button"
                class="frontend-ai-tools-dropdown-btn"
                id="aiToolsDropdownBtn"
                aria-expanded="false"
                aria-controls="aiToolsDropdownMenu">

                <span id="selectedToolsText">Select AI Tools</span>
                <i class="bi bi-chevron-down"></i>
            </button>

            <div
                class="frontend-ai-tools-dropdown-menu"
                id="aiToolsDropdownMenu">

                @forelse($availableAiTools as $key => $url)
                    <label class="frontend-ai-tool-option">
                        <input
                            type="checkbox"
                            name="ai_tools[]"
                            value="{{ $key }}"
                            id="tool_{{ $key }}"
                            {{ in_array($key, $oldTools) ? 'checked' : '' }}>

                        <span class="frontend-ai-tool-check">
                            <i class="bi bi-check"></i>
                        </span>

                        <span class="frontend-ai-tool-name">
                            {{ ucfirst(str_replace('_', ' ', $key)) }}
                        </span>
                    </label>
                @empty
                    <div class="p-3 text-muted">
                        No AI tools available.
                    </div>
                @endforelse
            </div>
        </div>

        <small class="frontend-field-hint">
            <i class="bi bi-info-circle me-1"></i>
            You can select multiple AI tools.
        </small>

        @error('ai_tools')
            <div class="text-danger small mt-2">{{ $message }}</div>
        @enderror
    </div>
</div>


                    {{-- Prompt Details --}}
                    <div class="frontend-form-section">
                        <div class="frontend-form-section-heading">
                            <div class="frontend-section-icon">
                                <i class="bi bi-pencil-square"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Prompt Details</h6>
                                <p class="mb-0">
                                    Write the prompt that users can copy and customize.
                                </p>
                            </div>
                        </div>

                        <div class="frontend-form-group mb-0">
                            <label for="prompt_text" class="frontend-form-label">
                                Prompt Text <span class="text-danger">*</span>
                            </label>

                            <textarea
                                class="form-control frontend-prompt-textarea @error('prompt_text') is-invalid @enderror"
                                id="prompt_text"
                                name="prompt_text"
                                rows="7"
                                placeholder="Write your prompt here... Use [1], [2], [3] for details users can customize."
                                required>{{ old('prompt_text') }}</textarea>

                            <div class="frontend-prompt-hint">
                                <i class="bi bi-lightbulb me-1"></i>
                                Tip: Use <strong>[1]</strong>, <strong>[2]</strong>,
                                <strong>[3]</strong> for customizable values.
                            </div>

                            @error('prompt_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Image Upload --}}
                    <div class="frontend-form-section">
                        <div class="frontend-form-section-heading">
                            <div class="frontend-section-icon">
                                <i class="bi bi-image"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Prompt Image</h6>
                                <p class="mb-0">Add an image to make your prompt more attractive.</p>
                            </div>
                        </div>

                        <div class="frontend-form-group mb-0">
                            <label for="image" class="frontend-form-label">
                                Image <span class="frontend-optional-label">Optional</span>
                            </label>

                            <label for="image"
                                class="frontend-image-upload-wrapper"
                                id="imageUploadBox">
                                <div class="frontend-image-upload-icon">
                                    <i class="bi bi-cloud-arrow-up"></i>
                                </div>
                                <div class="frontend-image-upload-content">
                                    <div class="frontend-image-upload-title">Choose Prompt Image</div>
                                    <div class="frontend-image-upload-text">JPG, JPEG, PNG, GIF or WEBP</div>
                                </div>
                            </label>

                            <input type="file"
                                class="visually-hidden @error('image') is-invalid @enderror"
                                id="image"
                                name="image"
                                accept="image/jpeg,image/png,image/gif,image/webp">

                            <div id="imagePreviewBox"
                                class="frontend-image-preview-box"
                                style="display: none;">

                                <div class="frontend-image-preview-header">
                                    <div>
                                        <i class="bi bi-check-circle-fill text-success me-1"></i>
                                        <strong>Image Selected</strong>
                                    </div>
                                    <span class="frontend-image-preview-badge">Ready to upload</span>
                                </div>

                                <img id="imagePreview" src="" alt="Selected prompt image preview">

                                <div class="frontend-image-preview-details">
                                    <div id="imageFileName" class="frontend-image-file-name"></div>
                                    <div id="imageFileSize" class="frontend-image-file-size"></div>
                                </div>

                                <div class="frontend-image-preview-actions">
                                    <label for="image" class="btn btn-sm btn-outline-primary mb-0">
                                        <i class="bi bi-arrow-repeat me-1"></i>
                                        Change Image
                                    </label>

                                    <button type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        id="removePromptImage">
                                        <i class="bi bi-trash me-1"></i>
                                        Remove Image
                                    </button>
                                </div>
                            </div>

                            @error('image')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <small class="frontend-field-hint">
                                <i class="bi bi-info-circle me-1"></i>
                                Adding an image is optional. Maximum file size: 2 MB.
                            </small>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="modal-footer frontend-prompt-modal-footer">
                    <button type="button"
                        class="btn frontend-cancel-btn"
                        data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>
                        Cancel
                    </button>

                    <button type="submit"
                        class="btn frontend-submit-btn"
                        id="submitPromptBtn">
                        <span class="submit-prompt-spinner spinner-border spinner-border-sm me-2 d-none"
                            role="status" aria-hidden="true"></span>
                        <i class="bi bi-send me-1 submit-prompt-icon"></i>
                        <span class="submit-prompt-label">Submit Prompt</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
