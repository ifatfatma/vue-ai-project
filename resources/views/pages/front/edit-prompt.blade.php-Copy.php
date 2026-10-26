<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edit Prompt - AI Prompt Hub</title>

   
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root{--primary:#6366f1;--primary-dark:#4f46e5;--secondary:#8b5cf6;--dark:#0f172a;--muted:#64748b;--line:#e2e8f0}
        *{box-sizing:border-box}
        body{background:#f4f7ff;color:#0f172a;font-family:system-ui,-apple-system,"Segoe UI",sans-serif}
        .modern-navbar{background:linear-gradient(110deg,#0f172a,#1e1b4b);padding:13px 0}
        .navbar-brand{color:#fff!important;font-size:19px;font-weight:800;letter-spacing:-.3px}
        .brand-icon{display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;margin-right:10px;border-radius:11px;background:linear-gradient(135deg,#818cf8,#7c3aed);color:#fff;font-size:19px}
        .hero-section{background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 50%,#312e81 100%);color:white;padding:36px 0 42px}
        .hero-section p{color:#cbd5e1!important}
        .edit-container{max-width:850px;margin: -20px auto 48px;position:relative;z-index:1}
        .edit-card{background:#fff;border:1px solid rgba(226,232,240,.9);border-radius:22px;box-shadow:0 18px 50px rgba(15,23,42,.09);overflow:hidden}
        .form-section{padding:24px;border-bottom:1px solid #eef2f7}
        .form-section:last-of-type{border-bottom:0}
        .section-heading{display:flex;align-items:center;gap:12px;margin-bottom:22px}
        .section-icon{display:flex;align-items:center;justify-content:center;flex:0 0 42px;width:42px;height:42px;border-radius:13px;background:#eef2ff;color:#4f46e5;font-size:19px}
        .section-heading h6{font-weight:800;margin:0 0 4px;font-size:15px}
        .section-heading p{margin:0;color:#64748b;font-size:12px}
        .form-label{font-size:13px;font-weight:750;color:#334155;margin-bottom:8px}
        .form-control,.form-select{border:1px solid #dbe3ef;border-radius:11px;min-height:46px;padding:11px 13px;font-size:14px;box-shadow:none!important}
        .form-control:focus,.form-select:focus{border-color:#818cf8;box-shadow:0 0 0 3px rgba(99,102,241,.12)!important}
        textarea.form-control{min-height:190px;resize:vertical;line-height:1.65}
        .field-hint{display:block;color:#64748b;font-size:12px;margin-top:9px}
        .ai-tools-dropdown{position:relative}
        .ai-tools-dropdown-btn{width:100%;min-height:46px;border:1px solid #dbe3ef;border-radius:11px;background:#fff;padding:10px 13px;display:flex;align-items:center;justify-content:space-between;text-align:left;color:#334155;font-size:14px}
        .ai-tools-dropdown-btn:focus{outline:0;border-color:#818cf8;box-shadow:0 0 0 3px rgba(99,102,241,.12)}
        .ai-tools-dropdown-menu{display:none;position:absolute;top:calc(100% + 6px);left:0;right:0;z-index:20;max-height:240px;overflow-y:auto;background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:7px;box-shadow:0 12px 30px rgba(15,23,42,.13)}
        .ai-tools-dropdown.open .ai-tools-dropdown-menu{display:block}
        .ai-tool-option{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:8px;cursor:pointer;font-size:13px;color:#334155}
        .ai-tool-option:hover{background:#f1f5f9}
        .ai-tool-option input{accent-color:#4f46e5;width:16px;height:16px}
        .selected-tool-badges{display:flex;flex-wrap:wrap;gap:6px;margin-top:9px}
        .tool-badge{font-size:11px;font-weight:700;color:#4338ca;background:#eef2ff;border-radius:20px;padding:5px 9px}
        .prompt-hint{margin-top:10px;padding:10px 12px;background:#f5f3ff;border:1px solid #ede9fe;border-radius:10px;color:#5b21b6;font-size:12px}
        .image-upload-box{display:flex;align-items:center;gap:14px;padding:17px;border:1.5px dashed #c7d2fe;border-radius:14px;background:#fafaff;cursor:pointer;transition:.2s}
        .image-upload-box:hover{border-color:#6366f1;background:#f5f3ff}
        .image-upload-icon{width:46px;height:46px;display:flex;align-items:center;justify-content:center;border-radius:13px;background:#eef2ff;color:#4f46e5;font-size:23px;flex-shrink:0}
        .image-upload-title{font-weight:800;font-size:13px;color:#1e293b}
        .image-upload-text{font-size:12px;color:#64748b;margin-top:3px}
        .image-preview-box{margin-top:14px;border:1px solid #e2e8f0;border-radius:14px;padding:12px;background:#fff}
        .image-preview-layout{display:flex;align-items:center;gap:14px}
        .image-preview{width:100px;height:88px;object-fit:cover;border-radius:10px;background:#f1f5f9;border:1px solid #e2e8f0;flex-shrink:0}
        .image-preview-name{font-size:13px;font-weight:750;overflow-wrap:anywhere;color:#1e293b}
        .image-preview-size{font-size:12px;color:#64748b;margin-top:4px}
        .image-preview-actions{display:flex;flex-wrap:wrap;gap:7px;margin-top:10px}
        .image-preview-actions .btn{font-size:12px;border-radius:8px}
        .current-image-label{font-size:11px;font-weight:800;color:#047857;background:#d1fae5;border-radius:20px;padding:4px 8px;display:inline-block;margin-bottom:7px}
        .form-footer{padding:20px 24px;background:#fafbff;border-top:1px solid #eef2f7;display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap}
        .btn-cancel{border:1px solid #cbd5e1;color:#475569;background:#fff;border-radius:11px;padding:11px 18px;font-weight:750;font-size:13px;text-decoration:none}
        .btn-cancel:hover{background:#f1f5f9;color:#1e293b}
        .btn-update{border:0;color:#fff;background:linear-gradient(135deg,#6366f1,#4f46e5);border-radius:11px;padding:11px 20px;font-weight:800;font-size:13px;box-shadow:0 7px 16px rgba(79,70,229,.2)}
        .btn-update:hover{color:#fff;transform:translateY(-1px);box-shadow:0 10px 20px rgba(79,70,229,.25)}
        .btn-update:disabled{opacity:.75;transform:none;cursor:wait}
        .alert{border-radius:12px;font-size:13px}
        footer{background:#1c2331;color:#fff;margin-top:auto}
        .toast-container{z-index:1090}
        @media(max-width:576px){.hero-section{padding:30px 0 38px}.hero-section h1{font-size:27px}.edit-container{margin: -16px 12px 30px}.form-section{padding:18px}.form-footer{padding:17px}.image-preview{width:82px;height:78px}.form-footer>*{flex:1;text-align:center}.btn-update{justify-content:center}}
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    {{-- Navbar --}}
    <nav class="navbar modern-navbar shadow-sm">
        <div class="container-fluid px-3 px-lg-4">
            <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                <span class="brand-icon"><i class="bi bi-stars"></i></span>
                <span>AI Prompt Hub</span>
            </a>
            <a href="{{ route('user.prompts') }}" class="btn btn-outline-light btn-sm fw-bold">
                <i class="bi bi-arrow-left me-1"></i> Back to My Prompts
            </a>
        </div>
    </nav>

    {{-- Hero --}}
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="fw-bold display-6 mb-2"><i class="bi bi-pencil-square me-2"></i>Edit Your Prompt</h1>
            <p class="mb-0" style="font-size:14px">Update your details. Editing may send your prompt for re-approval.</p>
        </div>
    </section>

    <main class="container edit-container flex-grow-1">
        @if(session('success'))
            <div class="alert alert-success shadow-sm mb-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger shadow-sm mb-3" role="alert">
                <div class="fw-bold mb-1"><i class="bi bi-exclamation-circle-fill me-2"></i>Please correct the following:</div>
                <ul class="mb-0 ps-4">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        @php
            $availableToolOptions = function_exists('availableTools') ? availableTools() : [];
            $storedTools = old('ai_tools', $prompt->ai_tools ?? $prompt->ai_tool ?? $prompt->tools ?? []);
            if (is_string($storedTools)) {
                $decodedTools = json_decode($storedTools, true);
                $storedTools = is_array($decodedTools) ? $decodedTools : (strlen($storedTools) ? explode(',', $storedTools) : []);
            }
            if (!is_array($storedTools)) $storedTools = [];
            $storedTools = array_map('strval', $storedTools);
        @endphp

        <div class="edit-card">
            <form id="editPromptForm" action="{{ route('user.prompts.update', $prompt->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Basic information --}}
                <section class="form-section">
                    <div class="section-heading">
                        <div class="section-icon"><i class="bi bi-info-circle"></i></div>
                        <div><h6>Basic Information</h6><p>Update the title and category of your prompt.</p></div>
                    </div>
                    <div class="mb-3">
                        <label for="title" class="form-label">Prompt Title <span class="text-danger">*</span></label>
                        <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $prompt->title) }}" placeholder="Enter prompt title..." required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                        <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ (string)old('category_id', $prompt->category_id) === (string)$cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </section>

                    {{-- AI tools --}}
                <section class="form-section">
                    <div class="section-heading">
                        <div class="section-icon"><i class="bi bi-robot"></i></div>
                        <div><h6>AI Tools / Platforms</h6><p>Select all AI platforms this prompt is useful for.</p></div>
                    </div>
                    <label class="form-label">AI Tools / Platforms <span class="text-danger">*</span></label>
                    <div class="ai-tools-dropdown" id="aiToolsDropdown">
                        <button type="button" class="ai-tools-dropdown-btn" id="aiToolsDropdownBtn" aria-expanded="false">
                            <span id="selectedToolsText">Select AI Tools</span><i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="ai-tools-dropdown-menu" id="aiToolsDropdownMenu">
                            @forelse($availableToolOptions as $key => $url)
                                <label class="ai-tool-option">
                                    <input type="checkbox" name="ai_tools[]" value="{{ $key }}" {{ in_array((string)$key, $storedTools, true) ? 'checked' : '' }}>
                                    <span>{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                                </label>
                            @empty
                                <div class="text-muted small p-2">AI tools are not configured.</div>
                            @endforelse
                        </div>
                    </div>
                    <div id="selectedToolBadges" class="selected-tool-badges"></div>
                    <small class="field-hint"><i class="bi bi-info-circle me-1"></i>You can select multiple AI tools.</small>
                    @error('ai_tools')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                </section>

                {{-- Prompt text --}}
                <section class="form-section">
                    <div class="section-heading">
                        <div class="section-icon"><i class="bi bi-pencil-square"></i></div>
                        <div><h6>Prompt Details</h6><p>Edit the prompt users can copy and customize.</p></div>
                    </div>
                    <label for="prompt_text" class="form-label">Prompt Text <span class="text-danger">*</span></label>
                    <textarea id="prompt_text" name="prompt_text" class="form-control @error('prompt_text') is-invalid @enderror" rows="7" placeholder="Write your prompt here...&#10;&#10;Use [1], [2], [3] for details users can customize." required>{{ old('prompt_text', $prompt->prompt_text) }}</textarea>
                    <div class="prompt-hint"><i class="bi bi-lightbulb me-1"></i>Tip: Use <strong>[1]</strong>, <strong>[2]</strong>, <strong>[3]</strong> for customizable values.</div>
                    @error('prompt_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </section>

                        {{-- Image upload --}}
                <section class="form-section">
                    <div class="section-heading">
                        <div class="section-icon"><i class="bi bi-image"></i></div>
                        <div><h6>Prompt Image</h6><p>Keep your current image or choose a replacement.</p></div>
                    </div>
                    <label for="image" class="form-label">Image / Output Example <span class="text-muted fw-normal">(Optional)</span></label>
                    <label for="image" class="image-upload-box" id="imageUploadBox">
                        <span class="image-upload-icon"><i class="bi bi-cloud-arrow-up"></i></span>
                        <span><span class="image-upload-title d-block">Choose Prompt Image</span><span class="image-upload-text d-block">JPG, JPEG, PNG, GIF or WEBP · Maximum 2 MB</span></span>
                    </label>
                    <input type="file" id="image" name="image" class="visually-hidden @error('image') is-invalid @enderror" accept="image/jpeg,image/png,image/gif,image/webp">
                    @error('image')<div class="text-danger small mt-2">{{ $message }}</div>@enderror

                    <div id="imagePreviewBox" class="image-preview-box" style="display:none">
                        <div class="image-preview-layout">
                            <img id="imagePreview" class="image-preview" src="" alt="Selected prompt image preview">
                            <div class="flex-grow-1 min-w-0">
                                <span id="currentImageLabel" class="current-image-label" style="display:none">Current image</span>
                                <div id="imageFileName" class="image-preview-name"></div>
                                <div id="imageFileSize" class="image-preview-size"></div>
                                <div class="image-preview-actions">
                                    <button type="button" id="changeImageBtn" class="btn btn-sm btn-outline-primary"><i class="bi bi-arrow-repeat me-1"></i>Change Image</button>
                                    <button type="button" id="removeImageBtn" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Remove New Image</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($prompt->image)
                        <div id="existingImageBox" class="image-preview-box">
                            <div class="image-preview-layout">
                                <img class="image-preview" src="{{ asset('storage/' . $prompt->image) }}" alt="Current prompt image">
                                <div class="flex-grow-1 min-w-0">
                                    <span class="current-image-label">Current saved image</span>
                                    <div class="image-preview-name">Existing prompt image</div>
                                    <div class="image-preview-size">Choose a new image above to replace it.</div>
                                </div>
                            </div>
                        </div>
                    @endif
                    <small class="field-hint">Leave this field empty to keep the existing image. Removing the image from this preview only clears a newly selected file; it does not delete the saved image.</small>
                </section>

                <div class="form-footer">
                    <a href="{{ route('user.prompts') }}" class="btn-cancel"><i class="bi bi-x-lg me-1"></i>Cancel</a>
                    <button type="submit" id="updatePromptBtn" class="btn-update">
                        <span id="updatePromptSpinner" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true" style="display:none"></span>
                        <i id="updatePromptIcon" class="bi bi-send me-1"></i>
                        <span id="updatePromptText">Update Prompt</span>
                    </button>
                </div>
            </form>
        </div>
    </main>

    <footer class="text-center mt-auto">
        <div class="p-3 small" style="background:rgba(0,0,0,.2)">© {{ date('Y') }} Copyright: <a class="text-white fw-bold text-decoration-none" href="{{ route('home') }}">AI Prompt Hub</a>. All rights reserved.</div>
    </footer>

    <div class="toast-container position-fixed top-0 end-0 p-3" id="editToastContainer" aria-live="polite" aria-atomic="true"></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
          document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('editPromptForm');
        const submitBtn = document.getElementById('updatePromptBtn');
        const spinner = document.getElementById('updatePromptSpinner');
        const submitIcon = document.getElementById('updatePromptIcon');
        const submitText = document.getElementById('updatePromptText');
        let submitting = false;

        form.addEventListener('submit', function (event) {
            if (submitting) { event.preventDefault(); return; }
            if (!form.checkValidity()) { form.reportValidity(); return; }
            submitting = true;
            submitBtn.disabled = true;
            spinner.style.display = 'inline-block';
            submitIcon.style.display = 'none';
            submitText.textContent = 'Updating Prompt...';
        });

        const dropdown = document.getElementById('aiToolsDropdown');
        const dropdownBtn = document.getElementById('aiToolsDropdownBtn');
        const selectedText = document.getElementById('selectedToolsText');
        const badges = document.getElementById('selectedToolBadges');
        const checkboxes = Array.from(dropdown.querySelectorAll('input[name="ai_tools[]"]'));
        function updateToolsDisplay() {
            const selected = checkboxes.filter(cb => cb.checked).map(cb => ({value: cb.value, label: cb.closest('label').querySelector('span').textContent.trim()}));
            selectedText.textContent = selected.length ? selected.length + ' tool' + (selected.length === 1 ? '' : 's') + ' selected' : 'Select AI Tools';
            badges.innerHTML = '';
            selected.forEach(tool => {
                const badge = document.createElement('span');
                badge.className = 'tool-badge';
                badge.textContent = tool.label;
                badges.appendChild(badge);
            });
        }
        dropdownBtn.addEventListener('click', function () {
            const isOpen = dropdown.classList.toggle('open');
            dropdownBtn.setAttribute('aria-expanded', String(isOpen));
        });
        checkboxes.forEach(cb => cb.addEventListener('change', updateToolsDisplay));
        document.addEventListener('click', function (event) {
            if (!dropdown.contains(event.target)) {
                dropdown.classList.remove('open');
                dropdownBtn.setAttribute('aria-expanded', 'false');
            }
        });
         updateToolsDisplay();

        const fileInput = document.getElementById('image');
        const previewBox = document.getElementById('imagePreviewBox');
        const preview = document.getElementById('imagePreview');
        const fileName = document.getElementById('imageFileName');
        const fileSize = document.getElementById('imageFileSize');
        const removeBtn = document.getElementById('removeImageBtn');
        const changeBtn = document.getElementById('changeImageBtn');
        const existingImageBox = document.getElementById('existingImageBox');
        let previewUrl = null;

        function formatBytes(bytes) {
            return bytes < 1024 * 1024 ? (bytes / 1024).toFixed(1) + ' KB' : (bytes / (1024 * 1024)).toFixed(2) + ' MB';
        }
        function clearNewImage() {
            fileInput.value = '';
            if (previewUrl) URL.revokeObjectURL(previewUrl);
            previewUrl = null;
            previewBox.style.display = 'none';
            preview.removeAttribute('src');
            if (existingImageBox) existingImageBox.style.display = '';
        }
        fileInput.addEventListener('change', function () {
            const file = fileInput.files && fileInput.files[0];
            if (!file) return;
            const allowedTypes = ['image/jpeg','image/png','image/gif','image/webp'];
            if (!allowedTypes.includes(file.type)) {
                fileInput.value = '';
                showEditToast('Please select a JPG, PNG, GIF or WEBP image.', 'danger');
                return;
            }
            if (file.size > 2 * 1024 * 1024) {
                fileInput.value = '';
                showEditToast('Image size must be 2 MB or less.', 'danger');
                return;
            }
            if (previewUrl) URL.revokeObjectURL(previewUrl);
            previewUrl = URL.createObjectURL(file);
            preview.src = previewUrl;
            fileName.textContent = file.name;
            fileSize.textContent = formatBytes(file.size);
            previewBox.style.display = 'block';
            if (existingImageBox) existingImageBox.style.display = 'none';
        });
        changeBtn.addEventListener('click', () => fileInput.click());
        removeBtn.addEventListener('click', clearNewImage);

         function showEditToast(message, type) {
            const container = document.getElementById('editToastContainer');
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-bg-' + (type || 'success') + ' border-0';
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            toast.innerHTML = '<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
            toast.querySelector('.toast-body').textContent = message;
            container.appendChild(toast);
            const toastInstance = new bootstrap.Toast(toast, {delay: 3500});
            toastInstance.show();
            toast.addEventListener('hidden.bs.toast', () => toast.remove());
        }
    });
    </script>
</body>
</html>
