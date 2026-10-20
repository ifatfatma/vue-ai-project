<!DOCTYPE html>
<html lang="en">
<head>
    @php $seoSettings = \App\Models\SeoSetting::first(); @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $seoSettings->meta_title ?? 'AI Prompt Hub - Discover & Copy Best Prompts' }}</title>
    <meta name="description" content="{{ $seoSettings->meta_description ?? 'Find the best AI prompts for ChatGPT, Midjourney, and more.' }}">
    <meta name="keywords" content="{{ $seoSettings->meta_keywords ?? 'ai prompts, chatgpt prompts, midjourney prompts' }}">
    <meta property="og:title" content="{{ $seoSettings->meta_title ?? 'AI Prompt Hub - Discover & Copy Best Prompts' }}">
    <meta property="og:description" content="{{ $seoSettings->meta_description ?? 'Find the best AI prompts for ChatGPT, Midjourney, and more.' }}">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:type" content="website">
    @if(!empty($seoSettings?->og_image))
        <meta property="og:image" content="{{ asset('storage/' . $seoSettings->og_image) }}">
    @endif
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        #toastContainer{position:fixed;top:20px;right:20px;z-index:3000;width:min(390px,calc(100vw - 30px))}
        #suggestionList{z-index:1060;max-height:360px;overflow-y:auto;border-radius:0 0 12px 12px}
        #suggestionList .list-group-item{cursor:pointer;text-align:left}
        #suggestionList .list-group-item:hover{background:#f8fafc}
        #aiToolsDropdown{position:relative}
        #aiToolsDropdownMenu{display:none;position:absolute;top:calc(100% + 4px);left:0;right:0;z-index:1080;max-height:260px;overflow-y:auto;background:#fff;border:1px solid #dee2e6;border-radius:10px;padding:8px;box-shadow:0 12px 30px rgba(15,23,42,.14)}
        #aiToolsDropdownMenu.show{display:block!important}
        #aiToolsDropdownBtn{width:100%;display:flex;align-items:center;justify-content:space-between;gap:12px;cursor:pointer}
        #aiToolsDropdownMenu .frontend-ai-tool-option{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:7px;cursor:pointer}
        #aiToolsDropdownMenu .frontend-ai-tool-option:hover{background:#f1f5f9}
        #imageUploadBox{cursor:pointer}
        #imageUploadBox:focus-visible{outline:3px solid rgba(99,102,241,.35);outline-offset:3px}
        #addPromptModal{z-index:2000!important}
        .modal-backdrop{z-index:1990!important}
        body.modal-open{overflow:hidden}
        #addPromptModal .modal-content,#addPromptModal .modal-header,#addPromptModal .modal-body,#addPromptModal .modal-footer{opacity:1;filter:none}
        .prompt-card-image-wrapper,.prompt-card-no-image{cursor:pointer;position:relative}
        .prompt-suggestion-title{font-weight:600;color:#0f172a}
        .prompt-suggestion-meta{font-size:.8rem;color:#64748b;margin-top:3px}
        .frontend-image-preview-box{margin-top:12px;border:1px solid #e2e8f0;border-radius:12px;padding:12px}
        .frontend-image-preview-box img{display:block;max-width:100%;max-height:220px;object-fit:contain;margin:12px auto;border-radius:8px}
        .frontend-image-preview-header,.frontend-image-preview-actions{display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap}
        .frontend-image-file-name{overflow-wrap:anywhere;font-weight:600}
        .frontend-field-error{color:#dc3545;font-size:.875rem;margin-top:6px}
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg modern-navbar">
    <div class="container-fluid px-3 px-lg-4">
        <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
            @if(auth('frontend')->check() && auth('frontend')->user()->profile_image)
                <img src="{{ asset(auth('frontend')->user()->profile_image) }}" alt="{{ auth('frontend')->user()->name }}" class="rounded-circle me-2" width="36" height="36" style="width:36px;height:36px;object-fit:cover;border:2px solid #8b5cf6;">
            @else
                <span class="brand-icon"><i class="bi bi-stars"></i></span>
            @endif
            <span>AI Prompt Hub</span>
        </a>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center flex-row gap-2">
                <li class="nav-item">
                    <button type="button" class="btn add-prompt-btn btn-sm fw-bold px-3 py-2" data-bs-toggle="modal" data-bs-target="#addPromptModal">
                        <i class="bi bi-plus-lg me-1"></i> Add Prompt
                    </button>
                </li>
                @auth('frontend')
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle navbar-account d-flex align-items-center" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            @if(auth('frontend')->user()->profile_image)
                                <img src="{{ asset(auth('frontend')->user()->profile_image) }}" alt="{{ auth('frontend')->user()->name }}" class="rounded-circle me-2" width="36" height="36" style="width:36px;height:36px;object-fit:cover;border:2px solid #8b5cf6;">
                            @else
                                <i class="bi bi-person-circle fs-5 me-1"></i>
                            @endif
                            <span>My Account</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 rounded-3" aria-labelledby="profileDropdown">
                            <li><a class="dropdown-item py-2" href="{{ route('user.profile.edit') }}"><i class="bi bi-person-circle me-2"></i>My Profile</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('user.prompts') }}"><i class="bi bi-collection me-2"></i>My Prompts</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('user.saved-prompts') }}"><i class="bi bi-bookmark-fill me-2 text-primary"></i>Saved Prompts</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><form action="{{ route('frontend.logout') }}" method="POST">@csrf<button type="submit" class="dropdown-item py-2 text-danger fw-semibold"><i class="bi bi-box-arrow-right me-2"></i>Logout</button></form></li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item"><a href="{{ route('frontend.login') }}" class="nav-link navbar-account"><i class="bi bi-box-arrow-in-right me-1"></i> Login</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<div id="toastContainer" aria-live="polite" aria-atomic="false"></div>

<section class="hero-section text-center">
    <div class="container hero-content">
        <div class="hero-badge"><i class="bi bi-stars"></i> AI Prompt Library</div>
        <h1 class="hero-title">Discover Powerful <span>AI Prompts</span></h1>
        <p class="hero-description">Explore curated prompts for ChatGPT, Claude, Gemini, Midjourney and other powerful AI tools.</p>
      <div class="hero-search-wrapper">
    <form action="{{ route('home') }}" method="GET" autocomplete="off">
        <div class="hero-search position-relative"> 
            <i class="bi bi-search d-flex align-items-center ps-3" style="color:#94a3b8;"></i>
            
            <div class="flex-grow-1">
                <input type="text" id="prompt-search" name="search" value="{{ request('search') }}" class="hero-search-input w-100" placeholder="Search prompts, SEO, marketing, coding..." autocomplete="off" aria-label="Search prompts" aria-controls="suggestionList" aria-expanded="false">
            </div>
            
            <button type="submit" class="btn btn-hero-search px-4">Search</button>

           
            <ul id="suggestionList" class="list-group position-absolute shadow-sm text-start" style="display:none;"></ul>
        </div>
    </form>
</div>
    </div>
</section>

{{-- Quick suggestion preview modal --}}
<div class="modal fade" id="quickSuggestionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header modern-modal-header border-0 pb-3">
                <span id="modalCategoryBadge" class="badge bg-light text-primary fs-6">General</span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <h4 id="modalPromptTitle" class="fw-bold text-dark mb-3"></h4>
                <div id="modalImageContainer" class="text-center mb-3" style="display:none">
                    <img id="modalPromptImage" src="" alt="Prompt image" class="img-fluid rounded-4 border" style="max-height:300px">
                </div>
                <label class="fw-bold mb-2 text-muted small">PROMPT TEXT</label>
                <div id="modalPromptText" class="p-3 modal-prompt-text" style="white-space:pre-wrap;font-family:monospace;color:#475569"></div>
                <textarea id="modalHiddenTextarea" class="d-none"></textarea>
            </div>
            <div class="modal-footer border-0 pt-0">
                @auth('frontend')
                    <button id="modalCopyBtn" type="button" class="btn copy-btn fw-bold px-4" onclick="copyPrompt('modalHiddenTextarea',this)"><i class="bi bi-clipboard me-1"></i>Copy Prompt</button>
                @else
                    <a href="{{ route('frontend.login') }}" class="btn copy-btn fw-bold">Login to Copy</a>
                @endauth
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<main class="container main-content flex-grow-1">
    <div class="category-filter-wrapper">
        <a href="{{ route('home') }}" class="category-pill {{ !request('category') ? 'active' : '' }}"><i class="bi bi-grid"></i> All Prompts</a>
        @foreach($categories as $category)
            <a href="{{ route('home', ['category' => $category->id]) }}" class="category-pill {{ request('category') == $category->id ? 'active' : '' }}">{{ $category->name }}</a>
        @endforeach
    </div>

    <div class="prompts-container">
        @forelse($prompts as $prompt)
            @php
                $displayPromptText = $prompt->prompt_text ?? '';
                $variableMeta = [];
                if (preg_match('/<!--AI_PROMPT_VARIABLES:([\s\S]*?)-->\s*$/i', $displayPromptText, $metaMatch)) {
                    $decodedMeta = json_decode(urldecode($metaMatch[1]), true);
                    if (is_array($decodedMeta)) $variableMeta = $decodedMeta;
                    $displayPromptText = preg_replace('/<!--AI_PROMPT_VARIABLES:[\s\S]*?-->\s*$/i', '', $displayPromptText);
                }
                preg_match_all('/\[([^\[\]]+)\]/', $displayPromptText, $matches);
                $promptVariables = collect($matches[1] ?? [])->map(fn($v) => trim($v))->filter(fn($v) => $v !== '')->unique()->values();
                $modalTools = is_array($prompt->ai_tool) ? $prompt->ai_tool : json_decode($prompt->ai_tool ?? '[]', true);
                if (!is_array($modalTools)) $modalTools = !empty($prompt->ai_tool) ? [$prompt->ai_tool] : [];
            @endphp
            <article class="prompt-card" id="prompt-{{ $prompt->id }}">
                @if($prompt->image)
                    <div class="prompt-card-image-wrapper" data-bs-toggle="modal" data-bs-target="#publicModal{{ $prompt->id }}" role="button" tabindex="0" aria-label="View {{ $prompt->title }}">
                        <img src="{{ asset('storage/' . $prompt->image) }}" class="prompt-card-image" alt="{{ $prompt->title }}" @if($loop->first) fetchpriority="high" @else loading="lazy" @endif>
                        <div class="position-absolute top-0 start-0 m-3"><span class="badge bg-dark bg-opacity-75 text-white px-3 py-2 rounded-pill shadow-sm"><i class="bi bi-stars text-warning me-1"></i>{{ $prompt->title ?? 'AI Prompt' }}</span></div>
                        <div class="image-view-overlay"><i class="bi bi-eye"></i><span>View</span></div>
                    </div>
                     @else

                        <div class="prompt-card-no-image" data-bs-toggle="modal" data-bs-target="#publicModal{{ $prompt->id }}"
                            role="button" tabindex="0" aria-label="View {{ $prompt->title }}">

                            <i class="bi bi-image"></i>
                            <span>View Prompt</span>

                            {{-- Floating Prompt Title --}}
                            <div class="position-absolute top-0 start-0 m-3">
                                <span class="badge bg-dark bg-opacity-75 text-white px-3 py-2 rounded-pill shadow-sm"
                                    style="backdrop-filter: blur(4px); font-size: 0.85rem; max-width: calc(100% - 20px); white-space: normal; text-align: left;">
                                    <i class="bi bi-stars text-warning me-1"></i>
                                    {{ $prompt->title ?? 'AI Prompt' }}
                                </span>
                            </div>

                        </div>

                    @endif

                <div class="prompt-card-actions">
                    @auth('frontend')
                        @php $isSaved = auth('frontend')->user()->savedPrompts()->where('prompt_id', $prompt->id)->exists(); @endphp
                        <button type="button" class="btn prompt-action-btn save-btn {{ $isSaved ? 'saved' : '' }}" data-prompt-id="{{ $prompt->id }}" data-saved="{{ $isSaved ? '1' : '0' }}" data-save-url="{{ route('prompts.save', $prompt) }}" onclick="toggleSavePrompt(this)" title="{{ $isSaved ? 'Remove from Saved' : 'Save Prompt' }}">
                            <i class="bi {{ $isSaved ? 'bi-bookmark-fill' : 'bi-bookmark' }}"></i><span>{{ $isSaved ? 'Saved' : 'Save' }}</span>
                        </button>
                        <button type="button" class="btn prompt-action-btn copy-btn" onclick="copyPrompt('prompt-text-{{ $prompt->id }}',this,{{ $prompt->id }})" title="Copy Prompt"><i class="bi bi-clipboard"></i><span>Copy</span></button>
                        <textarea id="prompt-text-{{ $prompt->id }}" class="d-none">{{ $displayPromptText }}</textarea>
                    @else
                        <a class="btn prompt-action-btn save-btn" href="{{ route('frontend.login') }}" title="Login to save prompt"><i class="bi bi-bookmark"></i><span>Save</span></a>
                        <a class="btn prompt-action-btn copy-btn" href="{{ route('frontend.login') }}" title="Login to copy prompt"><i class="bi bi-clipboard"></i><span>Copy</span></a>
                    @endauth
                    <button type="button" class="btn prompt-action-btn share-btn" onclick="sharePrompt({{ $prompt->id }}, @js($prompt->title))" title="Share Prompt"><i class="bi bi-share"></i><span>Share</span></button>
                </div>
            </article>

            <div class="modal fade" id="publicModal{{ $prompt->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow-lg rounded-4">
                        <div class="modal-header modern-modal-header border-0 pb-3">
                            <span class="badge bg-light text-primary fs-6">{{ $prompt->category->name ?? 'General' }}</span>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <h4 class="fw-bold text-dark mb-3">{{ $prompt->title }}</h4>
                            @if(!empty($modalTools))
                                <div class="mb-3 d-flex flex-wrap gap-2">
                                    @foreach($modalTools as $tool)
                                        @php $toolUrl = availableTools()[$tool] ?? '#'; $readableName = ucfirst(str_replace('_', ' ', $tool)); @endphp
                                        <a href="{{ $toolUrl }}" target="_blank" rel="noopener noreferrer" class="ai-tool-badge text-decoration-none"><i class="bi bi-robot"></i> {{ $readableName }}</a>
                                    @endforeach
                                </div>
                            @endif
                            @if($prompt->image)
                                <div class="text-center mb-3"><img src="{{ asset('storage/' . $prompt->image) }}" class="img-fluid rounded-4 border" style="max-height:300px" alt="{{ $prompt->title }}"></div>
                            @endif
                            @if($promptVariables->isNotEmpty())
                                <div class="prompt-variables-box" data-variable-box="{{ $prompt->id }}">
                                    <div class="prompt-variables-title"><i class="bi bi-sliders2-vertical"></i> Customize this prompt</div>
                                    <div class="prompt-variable-hint">Fill in the details below. The example shows you what to enter.</div>
                                    @foreach($promptVariables as $variable)
                                        @php
                                            $variableToken = trim((string)$variable);
                                            $variableSettings = $variableMeta[$variableToken] ?? [];
                                            $variableLabel = trim($variableSettings['label'] ?? '');
                                            $variableExample = trim($variableSettings['example'] ?? '');
                                            if ($variableLabel === '') $variableLabel = preg_match('/^\d+$/', $variableToken) ? 'Variable ' . $variableToken : 'Enter ' . $variableToken;
                                            if ($variableExample === '') $variableExample = $variableToken;
                                        @endphp
                                        <div class="prompt-variable-field">
                                            <label class="prompt-variable-label" for="modal-prompt-variable-{{ $prompt->id }}-{{ $loop->index }}">{{ $variableLabel }}</label>
                                            <input type="text" class="prompt-variable-input" id="modal-prompt-variable-{{ $prompt->id }}-{{ $loop->index }}" data-variable="{{ $variableToken }}" data-prompt-id="{{ $prompt->id }}" placeholder="{{ $variableExample ?: 'Enter your answer' }}" autocomplete="off">
                                        </div>
                                    @endforeach
                                    <button type="button" class="prompt-variable-reset" data-prompt-id="{{ $prompt->id }}" onclick="resetPromptVariables({{ $prompt->id }})"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset</button>
                                </div>
                            @endif
                            <label class="fw-bold mb-2 text-muted small">PROMPT TEXT</label>
                            @php $fullText = $displayPromptText; $isLong = \Illuminate\Support\Str::length($fullText) > 150; $shortText = \Illuminate\Support\Str::limit($fullText, 150, ''); @endphp
                            <div class="p-3 modal-prompt-text position-relative">
                                <pre id="modal-text-short-{{ $prompt->id }}" style="white-space:pre-wrap;font-family:monospace;margin:0;color:#475569">{{ $shortText }}@if($isLong)...@endif</pre>
                                @if($isLong)
                                    <pre id="modal-text-full-{{ $prompt->id }}" style="white-space:pre-wrap;font-family:monospace;margin:0;display:none;color:#475569">{{ $fullText }}</pre>
                                    <div class="text-end mt-2"><button type="button" class="btn btn-link btn-sm text-decoration-none fw-bold p-0" onclick="toggleModalText({{ $prompt->id }})">Read More <i class="bi bi-chevron-down"></i></button></div>
                                @endif
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            @auth('frontend')
                                <button type="button" class="btn copy-btn fw-bold" onclick="copyPrompt('prompt-text-{{ $prompt->id }}',this,{{ $prompt->id }})"><i class="bi bi-clipboard-check me-1"></i>Copy Filled Prompt</button>
                            @else
                                <a href="{{ route('frontend.login') }}" class="btn copy-btn fw-bold text-decoration-none"><i class="bi bi-box-arrow-in-right me-1"></i>Login to Copy</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5"><p class="text-muted">No prompts found.</p></div>
        @endforelse
    </div>
</main>

{{-- Add Prompt modal is outside main and separate from Vue mount --}}
<div class="modal fade" id="addPromptModal" tabindex="-1" aria-labelledby="addPromptModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable frontend-prompt-modal-dialog">
        <div class="modal-content frontend-prompt-modal-content">
            <div class="modal-header frontend-prompt-modal-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="frontend-prompt-header-icon"><i class="bi bi-stars"></i></div>
                    <div><h5 class="modal-title frontend-prompt-modal-title mb-1" id="addPromptModalLabel">Submit New Prompt</h5><p class="frontend-prompt-modal-subtitle mb-0">Share your useful AI prompt with the community</p></div>
                </div>
                <button type="button" class="btn-close btn-close-white frontend-prompt-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addPromptForm" action="{{ route('prompts.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body frontend-prompt-modal-body">
                    <div class="frontend-form-section">
                        <div class="frontend-form-section-heading"><div class="frontend-section-icon"><i class="bi bi-info-circle"></i></div><div><h6 class="mb-1">Basic Information</h6><p class="mb-0">Add the basic details of your prompt.</p></div></div>
                        <div class="frontend-form-group">
                            <label for="title" class="frontend-form-label">Prompt Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control frontend-form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" placeholder="Enter prompt title..." required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="frontend-form-group mb-0">
                            <label for="category_id" class="frontend-form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select frontend-form-control @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)<option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>@endforeach
                            </select>
                            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="frontend-form-section">
                        <div class="frontend-form-section-heading"><div class="frontend-section-icon"><i class="bi bi-robot"></i></div><div><h6 class="mb-1">AI Tools / Platforms</h6><p class="mb-0">Select the AI tools this prompt is useful for.</p></div></div>
                        @php $oldTools = old('ai_tools', old('ai_tool', [])); if (!is_array($oldTools)) $oldTools = [$oldTools]; @endphp
                        <div class="frontend-form-group mb-0">
                            <label class="frontend-form-label">AI Tools / Platforms <span class="text-danger">*</span></label>
                            <div class="frontend-ai-tools-dropdown" id="aiToolsDropdown">
                                <button type="button" class="frontend-ai-tools-dropdown-btn" id="aiToolsDropdownBtn" aria-expanded="false"><span id="selectedToolsText">Select AI Tools</span><i class="bi bi-chevron-down"></i></button>
                                <div class="frontend-ai-tools-dropdown-menu" id="aiToolsDropdownMenu">
                                    @foreach(availableTools() as $key => $url)
                                        <label class="frontend-ai-tool-option"><input type="checkbox" name="ai_tools[]" value="{{ $key }}" id="tool_{{ $key }}" {{ in_array($key, $oldTools) ? 'checked' : '' }}><span class="frontend-ai-tool-check"><i class="bi bi-check"></i></span><span class="frontend-ai-tool-name">{{ ucfirst(str_replace('_', ' ', $key)) }}</span></label>
                                    @endforeach
                                </div>
                            </div>
                            <small class="frontend-field-hint"><i class="bi bi-info-circle me-1"></i>You can select multiple AI tools.</small>
                            @error('ai_tools')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="frontend-form-section">
                        <div class="frontend-form-section-heading"><div class="frontend-section-icon"><i class="bi bi-pencil-square"></i></div><div><h6 class="mb-1">Prompt Details</h6><p class="mb-0">Write the prompt that users can copy and customize.</p></div></div>
                        <div class="frontend-form-group mb-0">
                            <label for="prompt_text" class="frontend-form-label">Prompt Text <span class="text-danger">*</span></label>
                            <textarea class="form-control frontend-prompt-textarea @error('prompt_text') is-invalid @enderror" id="prompt_text" name="prompt_text" rows="7" placeholder="Write your prompt here...&#10;&#10;Use [1], [2], [3] for details that users can customize." required>{{ old('prompt_text') }}</textarea>
                            <div class="frontend-prompt-hint"><i class="bi bi-lightbulb me-1"></i>Tip: Use <strong>[1]</strong>, <strong>[2]</strong>, <strong>[3]</strong> for customizable values.</div>
                            @error('prompt_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="frontend-form-section">
                        <div class="frontend-form-section-heading"><div class="frontend-section-icon"><i class="bi bi-image"></i></div><div><h6 class="mb-1">Prompt Image</h6><p class="mb-0">Add an image to make your prompt more attractive.</p></div></div>
                        <div class="frontend-form-group mb-0">
                            <label for="image" class="frontend-form-label">Image <span class="frontend-optional-label">Optional</span></label>
                            <div class="frontend-image-upload-wrapper" id="imageUploadBox" role="button" tabindex="0" aria-controls="image" aria-label="Choose prompt image">
                                <div class="frontend-image-upload-icon"><i class="bi bi-cloud-arrow-up"></i></div>
                                <div class="frontend-image-upload-content"><div class="frontend-image-upload-title">Choose Prompt Image</div><div class="frontend-image-upload-text">JPG, JPEG, PNG, GIF or WEBP</div></div>
                            </div>
                            <input type="file" class="visually-hidden @error('image') is-invalid @enderror" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                            <div id="imagePreviewBox" class="frontend-image-preview-box" style="display:none">
                                <div class="frontend-image-preview-header"><div><i class="bi bi-check-circle-fill text-success me-1"></i><strong>Image Selected</strong></div><span class="frontend-image-preview-badge">Ready to upload</span></div>
                                <img id="imagePreview" src="" alt="Selected prompt image preview">
                                <div class="frontend-image-preview-details"><div id="imageFileName" class="frontend-image-file-name"></div><div id="imageFileSize" class="frontend-image-file-size"></div></div>
                                <div class="frontend-image-preview-actions mt-2"><label for="image" class="btn btn-sm btn-outline-primary mb-0"><i class="bi bi-arrow-repeat me-1"></i>Change Image</label><button type="button" class="btn btn-sm btn-outline-danger" id="removePromptImage"><i class="bi bi-trash me-1"></i>Remove Image</button></div>
                            </div>
                            <div id="imageValidationError" class="frontend-field-error" role="alert" style="display:none"></div>
                            @error('image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <small class="frontend-field-hint"><i class="bi bi-info-circle me-1"></i>Adding an image is optional. Maximum file size: 2 MB.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer frontend-prompt-modal-footer">
                    <button type="button" class="btn frontend-cancel-btn" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i>Cancel</button>
                    <button type="submit" class="btn frontend-submit-btn" id="submitPromptBtn"><span class="submit-prompt-spinner spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span><i class="bi bi-send me-1 submit-prompt-icon"></i><span class="submit-prompt-label">Submit Prompt</span></button>
                </div>
            </form>
        </div>
    </div>
</div>

<footer class="modern-footer mt-auto">
    <div class="container"><div class="text-center py-4 small">© {{ date('Y') }} <a href="{{ route('home') }}" class="fw-bold">AI Prompt Hub</a>. All rights reserved.</div></div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@php
    $suggestionPromptItems = $prompts instanceof \Illuminate\Contracts\Pagination\Paginator
        ? $prompts->items()
        : $prompts;

    $searchSuggestionPrompts = collect($suggestionPromptItems)->map(function ($prompt) {
        $text = (string) ($prompt->prompt_text ?? '');
        $text = preg_replace('/<!--AI_PROMPT_VARIABLES:[\s\S]*?-->\s*$/i', '', $text);

        return [
            'id' => $prompt->id,
            'title' => $prompt->title ?? 'Untitled Prompt',
            'text' => $text,
            'category' => $prompt->category->name ?? 'General',
            'image' => !empty($prompt->image) ? asset('storage/' . $prompt->image) : null,
        ];
    })->values()->all();
@endphp

<script>
(function () {
    'use strict';

    window.showToast = function (id, message, type = 'success') {
        const container = document.getElementById('toastContainer');
        if (!container) return;
        if (typeof type === 'boolean') type = type ? 'success' : 'error';
        if (!['success','error','warning','info'].includes(type)) type = 'info';
        if (id) document.getElementById(id)?.remove();
        const icons = {success:'bi-check-circle-fill',error:'bi-x-circle-fill',warning:'bi-exclamation-triangle-fill',info:'bi-info-circle-fill'};
        const toast = document.createElement('div');
        toast.className = 'app-toast toast-' + type;
        toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
        if (id) toast.id = id;
        const icon = document.createElement('i'); icon.className = 'bi ' + icons[type] + ' app-toast-icon';
        const content = document.createElement('div'); content.className = 'app-toast-content'; content.textContent = String(message ?? '');
        const close = document.createElement('button'); close.type = 'button'; close.className = 'app-toast-close'; close.innerHTML = '&times;'; close.setAttribute('aria-label','Dismiss notification');
        toast.append(icon,content,close); container.appendChild(toast);
        requestAnimationFrame(() => toast.classList.add('show'));
        let timer;
        const remove = () => { clearTimeout(timer); toast.classList.remove('show'); setTimeout(() => toast.remove(),300); };
        close.addEventListener('click',remove); timer = setTimeout(remove,2500);
    };

    window.copyPrompt = async function (textareaId, button, promptId) {
        const field = document.getElementById(textareaId);
        if (!field) { window.showToast('copyPromptError','Prompt text could not be found.','error'); return; }
        let text = field.value ?? field.textContent ?? '';
        if (promptId !== undefined && promptId !== null) {
            document.querySelectorAll('.prompt-variable-input[data-prompt-id="' + promptId + '"]').forEach(input => {
                const token = String(input.dataset.variable || '').trim();
                if (!token) return;
                const value = input.value.trim();
                const escaped = token.replace(/[.*+?^${}()|[\]\\]/g,'\\$&');
                text = text.replace(new RegExp('\\[' + escaped + '\\]','g'),value || '[' + token + ']');
            });
        }
        try {
            if (navigator.clipboard && window.isSecureContext) await navigator.clipboard.writeText(text);
            else {
                const temp = document.createElement('textarea'); temp.value = text; temp.setAttribute('readonly',''); temp.style.position = 'fixed'; temp.style.opacity = '0'; document.body.appendChild(temp); temp.select();
                const copied = document.execCommand('copy'); temp.remove(); if (!copied) throw new Error('Copy failed');
            }
            if (button) {
                const old = button.innerHTML; button.innerHTML = '<i class="bi bi-check2 me-1"></i><span>Copied!</span>'; button.disabled = true;
                setTimeout(() => { if (button.isConnected) { button.innerHTML = old; button.disabled = false; } },1400);
            }
            window.showToast('copyPromptSuccess','Prompt copied to clipboard.','success');
        } catch (_) { window.showToast('copyPromptError','Unable to copy automatically. Please select and copy the prompt text.','error'); }
    };

    window.sharePrompt = async function (promptId, promptTitle) {
        const url = new URL(window.location.pathname,window.location.origin); url.hash = 'prompt-' + promptId;
        try {
            if (navigator.share) { await navigator.share({title:promptTitle || 'AI Prompt',text:promptTitle || 'Check out this AI prompt',url:url.toString()}); }
            else if (navigator.clipboard && window.isSecureContext) await navigator.clipboard.writeText(url.toString());
            else {
                const temp = document.createElement('textarea'); temp.value = url.toString(); temp.setAttribute('readonly',''); temp.style.position = 'fixed'; temp.style.opacity = '0'; document.body.appendChild(temp); temp.select();
                const copied = document.execCommand('copy'); temp.remove(); if (!copied) throw new Error('Copy failed');
            }
            window.showToast('sharePromptSuccess',navigator.share ? 'Share dialog opened.' : 'Prompt link copied to clipboard.','success');
        } catch (error) { if (error?.name !== 'AbortError') window.showToast('sharePromptError','Unable to share this prompt link.','error'); }
    };

    window.toggleSavePrompt = async function (button) {
        if (!button || button.dataset.busy === 'true') return;
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content; const url = button.dataset.saveUrl;
        if (!url || !csrf) { window.showToast('savePromptError','Unable to save this prompt. Please refresh and try again.','error'); return; }
        const wasSaved = button.dataset.saved === '1'; button.dataset.busy = 'true'; button.disabled = true;
        try {
            const response = await fetch(url,{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'X-Requested-With':'XMLHttpRequest','Accept':'application/json','Content-Type':'application/json'},body:JSON.stringify({})});
            let data = {}; try { data = await response.json(); } catch (_) {}
            if (!response.ok || data.success === false) throw new Error(data.message || 'Request failed');
            const saved = typeof data.saved === 'boolean' ? data.saved : !wasSaved;
            button.dataset.saved = saved ? '1' : '0'; button.classList.toggle('saved',saved); button.title = saved ? 'Remove from Saved' : 'Save Prompt';
            const icon = button.querySelector('i'); if (icon) icon.className = 'bi ' + (saved ? 'bi-bookmark-fill' : 'bi-bookmark');
            const label = button.querySelector('span'); if (label) label.textContent = saved ? 'Saved' : 'Save';
            window.showToast('savePromptSuccess',data.message || (saved ? 'Prompt saved.' : 'Prompt removed from saved.'),'success');
        } catch (error) { window.showToast('savePromptError',error.message || 'Unable to update saved prompt.','error'); }
        finally { button.dataset.busy = 'false'; button.disabled = false; }
    };

    window.toggleModalText = function (id) {
        const shortText = document.getElementById('modal-text-short-' + id); const fullText = document.getElementById('modal-text-full-' + id);
        if (!shortText || !fullText) return;
        const showingFull = fullText.style.display !== 'none'; fullText.style.display = showingFull ? 'none' : 'block'; shortText.style.display = showingFull ? 'block' : 'none';
        const button = shortText.parentElement?.querySelector('button[onclick*="toggleModalText(' + id + ')"]');
        if (button) button.innerHTML = showingFull ? 'Read More <i class="bi bi-chevron-down"></i>' : 'Read Less <i class="bi bi-chevron-up"></i>';
    };

    window.resetPromptVariables = function (id) {
        document.querySelectorAll('.prompt-variable-input[data-prompt-id="' + id + '"]').forEach(input => { input.value = ''; input.dispatchEvent(new Event('input',{bubbles:true})); input.dispatchEvent(new Event('change',{bubbles:true})); });
        window.showToast('resetPromptVariablesToast','Prompt variables reset.','success');
    };

    document.addEventListener('DOMContentLoaded', function () {
        // Live search suggestions are based on all prompts rendered by this Blade page.
        const searchInput = document.getElementById('prompt-search');
        const suggestionList = document.getElementById('suggestionList');
        const prompts = @json($searchSuggestionPrompts);

        if (searchInput && suggestionList) {
            const hide = () => { suggestionList.style.display = 'none'; suggestionList.innerHTML = ''; searchInput.setAttribute('aria-expanded','false'); };
            const render = (items) => {
                suggestionList.innerHTML = '';
                if (!items.length) {
                    const li = document.createElement('li'); li.className = 'list-group-item text-muted small'; li.textContent = 'No matching prompts found.'; suggestionList.appendChild(li);
                } else {
                    items.slice(0,8).forEach(prompt => {
                        const li = document.createElement('li'); li.className = 'list-group-item list-group-item-action';
                        const title = document.createElement('div'); title.className = 'prompt-suggestion-title'; title.textContent = prompt.title;
                        const meta = document.createElement('div'); meta.className = 'prompt-suggestion-meta'; meta.textContent = prompt.category;
                        li.append(title,meta);
                        li.addEventListener('click', () => {
                            searchInput.value = prompt.title; hide();
                            document.getElementById('modalPromptTitle').textContent = prompt.title;
                            document.getElementById('modalCategoryBadge').textContent = prompt.category;
                            document.getElementById('modalPromptText').textContent = prompt.text;
                            document.getElementById('modalHiddenTextarea').value = prompt.text;
                            const imageContainer = document.getElementById('modalImageContainer'); const image = document.getElementById('modalPromptImage');
                            if (prompt.image) { image.src = prompt.image; image.alt = prompt.title; imageContainer.style.display = 'block'; }
                            else { image.removeAttribute('src'); imageContainer.style.display = 'none'; }
                            bootstrap.Modal.getOrCreateInstance(document.getElementById('quickSuggestionModal')).show();
                        });
                        suggestionList.appendChild(li);
                    });
                }
                suggestionList.style.display = 'block'; searchInput.setAttribute('aria-expanded','true');
            };
            searchInput.addEventListener('input', function () {
                const q = this.value.trim().toLowerCase(); if (!q) { hide(); return; }
                render(prompts.filter(p => p.title.toLowerCase().includes(q) || p.category.toLowerCase().includes(q) || p.text.toLowerCase().includes(q)));
            });
            searchInput.addEventListener('keydown', e => { if (e.key === 'Escape') hide(); });
            document.addEventListener('click', e => { if (!searchInput.contains(e.target) && !suggestionList.contains(e.target)) hide(); });
            searchInput.addEventListener('focus', function () { if (this.value.trim()) this.dispatchEvent(new Event('input')); });
        }

        // AI tools multi-select.
        const dropdown = document.getElementById('aiToolsDropdown'); const btn = document.getElementById('aiToolsDropdownBtn'); const menu = document.getElementById('aiToolsDropdownMenu'); const selectedText = document.getElementById('selectedToolsText');
        if (dropdown && btn && menu && selectedText) {
            const update = () => {
                const checked = [...menu.querySelectorAll('input[name="ai_tools[]"]:checked')];
                const names = checked.map(input => input.closest('label')?.querySelector('.frontend-ai-tool-name')?.textContent.trim() || input.value);
                selectedText.textContent = names.length ? names.join(', ') : 'Select AI Tools';
                selectedText.style.color = names.length ? '#1e293b' : '#475569'; selectedText.style.fontWeight = names.length ? '600' : '400';
            };
            btn.addEventListener('click', e => { e.preventDefault(); e.stopPropagation(); const open = menu.classList.toggle('show'); btn.setAttribute('aria-expanded',String(open)); });
            menu.addEventListener('click', e => e.stopPropagation());
            menu.querySelectorAll('input[name="ai_tools[]"]').forEach(input => input.addEventListener('change',update));
            document.addEventListener('click',e => { if (!dropdown.contains(e.target)) { menu.classList.remove('show'); btn.setAttribute('aria-expanded','false'); } });
            update();
        }

        // Image picker + preview + client-side 2 MB validation.
        const imageInput = document.getElementById('image'); const previewBox = document.getElementById('imagePreviewBox'); const previewImage = document.getElementById('imagePreview'); const fileName = document.getElementById('imageFileName'); const fileSize = document.getElementById('imageFileSize'); const uploadBox = document.getElementById('imageUploadBox'); const removeButton = document.getElementById('removePromptImage'); const imageError = document.getElementById('imageValidationError');
        if (imageInput && previewBox && previewImage && fileName && fileSize && uploadBox) {
            let previewUrl = null; const maxBytes = 2 * 1024 * 1024; const types = ['image/jpeg','image/png','image/gif','image/webp'];
            const clearPreview = () => { if (previewUrl) URL.revokeObjectURL(previewUrl); previewUrl = null; previewImage.removeAttribute('src'); fileName.textContent = ''; fileSize.textContent = ''; previewBox.style.display = 'none'; uploadBox.style.display = 'flex'; };
            const showImageError = message => { if (imageError) { imageError.textContent = message; imageError.style.display = 'block'; } window.showToast('imageValidationToast',message,'error'); };
            const openPicker = e => { e?.preventDefault(); imageInput.click(); };
            uploadBox.addEventListener('click',openPicker);
            uploadBox.addEventListener('keydown',e => { if (e.key === 'Enter' || e.key === ' ') openPicker(e); });
            imageInput.addEventListener('change',() => {
                const file = imageInput.files?.[0]; if (!file) return;
                if (imageError) { imageError.textContent = ''; imageError.style.display = 'none'; }
                if (!types.includes(file.type)) { imageInput.value = ''; clearPreview(); showImageError('Please select a JPG, JPEG, PNG, GIF or WEBP image.'); return; }
                if (file.size > maxBytes) { imageInput.value = ''; clearPreview(); showImageError('Image size must not exceed 2 MB.'); return; }
                if (previewUrl) URL.revokeObjectURL(previewUrl);
                previewUrl = URL.createObjectURL(file); previewImage.src = previewUrl; fileName.textContent = file.name;
                fileSize.textContent = file.size < 1048576 ? (file.size / 1024).toFixed(1) + ' KB' : (file.size / 1048576).toFixed(2) + ' MB';
                uploadBox.style.display = 'none'; previewBox.style.display = 'block';
            });
            removeButton?.addEventListener('click',() => { imageInput.value = ''; clearPreview(); if (imageError) imageError.style.display = 'none'; });
            document.getElementById('addPromptModal')?.addEventListener('hidden.bs.modal',() => { imageInput.value = ''; clearPreview(); if (imageError) imageError.style.display = 'none'; });
        }

        // Prevent double-submit but don't disable the form fields.
        const form = document.getElementById('addPromptForm'); const submitButton = document.getElementById('submitPromptBtn');
        if (form && submitButton) {
            form.addEventListener('submit', event => {
                if (submitButton.dataset.submitting === 'true') { event.preventDefault(); return; }
                const selectedTools = form.querySelectorAll('input[name="ai_tools[]"]:checked');
                if (!selectedTools.length) {
                    event.preventDefault();
                    window.showToast('aiToolsRequiredToast','Please select at least one AI tool.','error');
                    document.getElementById('aiToolsDropdownMenu')?.classList.add('show');
                    return;
                }
                submitButton.dataset.submitting = 'true'; submitButton.disabled = true; submitButton.setAttribute('aria-busy','true');
                submitButton.querySelector('.submit-prompt-spinner')?.classList.remove('d-none');
                submitButton.querySelector('.submit-prompt-icon')?.classList.add('d-none');
                const label = submitButton.querySelector('.submit-prompt-label'); if (label) label.textContent = 'Submitting...';
            });
        }

        // Keyboard accessibility for prompt card view modals.
        document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target^="#publicModal"]').forEach(el => {
            el.addEventListener('keydown', e => {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); bootstrap.Modal.getOrCreateInstance(document.querySelector(el.dataset.bsTarget)).show(); }
            });
        });

        // Open prompt modal from a shared URL hash.
        const match = window.location.hash.match(/^#prompt-(\d+)$/);
        if (match) {
            const id = match[1]; const card = document.getElementById('prompt-' + id); const modal = document.getElementById('publicModal' + id);
            card?.scrollIntoView({behavior:'smooth',block:'center'});
            if (modal) bootstrap.Modal.getOrCreateInstance(modal).show();
        }

        @if(session('success'))
            window.showToast('sessionSuccessToast', @json(session('success')), 'success');
        @endif

        @if($errors->any())
            window.showToast('sessionValidationToast', @json($errors->first()), 'error');
        @endif

        @if($errors->any())
            const addModal = document.getElementById('addPromptModal');
            if (addModal) bootstrap.Modal.getOrCreateInstance(addModal).show();
        @endif
    });
})();
</script>
</body>
</html>
