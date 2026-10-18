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
    @if(!empty($seoSettings?->og_image))<meta property="og:image" content="{{ asset('storage/' . $seoSettings->og_image) }}">@endif
    <meta property="og:title" content="{{ $seoSettings->meta_title ?? 'AI Prompt Hub - Discover & Copy Best Prompts' }}">
    <meta property="og:description" content="{{ $seoSettings->meta_description ?? 'Find the best AI prompts for ChatGPT, Midjourney, and more.' }}">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:type" content="website">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="d-flex flex-column min-vh-100">

    <nav class="navbar navbar-expand-lg modern-navbar">
        <div class="container-fluid px-3 px-lg-4">
            <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                @if(auth('frontend')->check() && auth('frontend')->user()->profile_image)
                    <img src="{{ asset(auth('frontend')->user()->profile_image) }}"
                        alt="{{ auth('frontend')->user()->name }}" class="rounded-circle me-2" width="36" height="36"
                        style="width:36px;height:36px;object-fit:cover;border:2px solid #8b5cf6;">
                @else
                    <span class="brand-icon"><i class="bi bi-stars"></i></span>
                @endif
                <span>AI Prompt Hub</span>
            </a>

            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center flex-row gap-2">
                    <li class="nav-item">
                        <button type="button" class="btn add-prompt-btn btn-sm fw-bold px-3 py-2" data-bs-toggle="modal"
                            data-bs-target="#addPromptModal">
                            <i class="bi bi-plus-lg me-1"></i> Add Prompt
                        </button>
                    </li>

                    {{-- Explicitly check 'frontend' guard --}}
                    @auth('frontend')
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle navbar-account d-flex align-items-center" href="#"
                                id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                @if(auth('frontend')->user()->profile_image)
                                    <img src="{{ asset(auth('frontend')->user()->profile_image) }}"
                                        alt="{{ auth('frontend')->user()->name }}" class="rounded-circle me-2" width="36"
                                        height="36" style="width:36px;height:36px;object-fit:cover;border:2px solid #8b5cf6;">
                                @else
                                    <i class="bi bi-person-circle fs-5 me-1"></i>
                                @endif
                                <span>My Account</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 rounded-3"
                                aria-labelledby="profileDropdown">
                                <li>
                                    <a class="dropdown-item py-2 d-flex align-items-center"
                                        href="{{ route('user.profile.edit') }}">
                                        @if(auth('frontend')->user()->profile_image)
                                            <img src="{{ asset(auth('frontend')->user()->profile_image) }}" alt="Profile"
                                                class="rounded-circle me-2" width="28" height="28"
                                                style="width:28px;height:28px;object-fit:cover;">
                                        @else
                                            <i class="bi bi-person-circle me-2 text-muted"></i>
                                        @endif
                                        My Profile
                                    </a>
                                </li>
                                <li><a class="dropdown-item py-2 px-3" href="{{ route('user.prompts') }}"><i
                                            class="bi bi-collection me-2"></i>My Prompts</a></li>
                                <li><a class="dropdown-item py-2 px-3" href="{{ route('user.saved-prompts') }}"><i
                                            class="bi bi-bookmark-fill me-2 text-primary"></i>Saved Prompts</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <form action="{{ route('frontend.logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item py-2 text-danger fw-semibold"><i
                                                class="bi bi-box-arrow-right me-2"></i>Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        {{-- Point to frontend login route --}}
                        <li class="nav-item"><a href="{{ route('frontend.login') }}" class="nav-link navbar-account"><i
                                    class="bi bi-box-arrow-in-right me-1"></i> Login</a></li>
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
            <p class="hero-description">Explore curated prompts for ChatGPT, Claude, Gemini, Midjourney and other
                powerful AI tools.</p>

            <div class="hero-search-wrapper">
                <form action="{{ route('home') }}" method="GET">
                    <div class="hero-search">
                        <i class="bi bi-search d-flex align-items-center ps-3" style="color:#94a3b8;"></i>
                        <div class="position-relative flex-grow-1">
                            <input type="text" id="prompt-search" name="search" value="{{ request('search') }}"
                                class="hero-search-input w-100" placeholder="Search prompts, SEO, marketing, coding..."
                                autocomplete="off">
                            <ul id="suggestionList" class="list-group position-absolute w-100 shadow-sm text-start"
                                style="display:none;left:0;top:100%;"></ul>
                        </div>
                        <button type="submit" class="btn btn-hero-search px-4">Search</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    {{-- Quick search suggestion modal --}}
    <div class="modal fade" id="quickSuggestionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header modern-modal-header border-0 pb-3">
                    <span id="modalCategoryBadge" class="badge bg-light text-primary fs-6">General</span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <h4 id="modalPromptTitle" class="fw-bold text-dark mb-3"></h4>
                    <div id="modalImageContainer" class="text-center mb-3" style="display:none;">
                        <img id="modalPromptImage" src="" alt="Prompt image" class="img-fluid rounded-4 border"
                            style="max-height:300px;">
                    </div>
                    <label class="fw-bold mb-2 text-muted small">PROMPT TEXT</label>
                    <div id="modalPromptText" class="p-3 modal-prompt-text"
                        style="white-space:pre-wrap;font-family:monospace;color:#475569;"></div>
                    <textarea id="modalHiddenTextarea" class="d-none"></textarea>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button id="modalCopyBtn" type="button" class="btn copy-btn fw-bold px-4"
                        onclick="copyModalPrompt()"><i class="bi bi-clipboard me-1"></i>Copy Prompt</button>
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <main class="container main-content flex-grow-1">
        <div class="category-filter-wrapper">
            <a href="{{ route('home') }}" class="category-pill {{ !request('category') ? 'active' : '' }}"><i
                    class="bi bi-grid"></i> All Prompts</a>
            @foreach($categories as $category)
                <a href="{{ route('home', ['category' => $category->id]) }}"
                    class="category-pill {{ request('category') == $category->id ? 'active' : '' }}">{{ $category->name }}</a>
            @endforeach
        </div>

        <div class="prompts-container">

            @forelse($prompts as $prompt)

                @php
                    $displayPromptText = $prompt->prompt_text ?? '';

                    $variableMeta = [];

                    if (preg_match('/<!--AI_PROMPT_VARIABLES:([\s\S]*?)-->\s*$/i', $displayPromptText, $metaMatch)) {
                        $decodedMeta = json_decode(urldecode($metaMatch[1]), true);

                        if (is_array($decodedMeta)) {
                            $variableMeta = $decodedMeta;
                        }

                        $displayPromptText = preg_replace(
                            '/<!--AI_PROMPT_VARIABLES:[\s\S]*?-->\s*$/i',
                            '',
                            $displayPromptText
                        );
                    }

                    preg_match_all('/\[([^\[\]]+)\]/', $displayPromptText, $matches);

                    $promptVariables = collect($matches[1] ?? [])
                        ->map(fn($value) => trim($value))
                        ->filter(fn($value) => $value !== '')
                        ->unique()
                        ->values();

                    $modalTools = is_array($prompt->ai_tool)
                        ? $prompt->ai_tool
                        : json_decode($prompt->ai_tool ?? '[]', true);

                    if (!is_array($modalTools)) {
                        $modalTools = !empty($prompt->ai_tool)
                            ? [$prompt->ai_tool]
                            : [];
                    }
                @endphp

                <article class="prompt-card">

                    {{-- Prompt Image with Floating Title --}}
                    @if($prompt->image)

                        <div class="prompt-card-image-wrapper" data-bs-toggle="modal"
                            data-bs-target="#publicModal{{ $prompt->id }}" role="button" tabindex="0"
                            aria-label="View {{ $prompt->title }}">

                            <img src="{{ asset('storage/' . $prompt->image) }}" class="prompt-card-image"
    alt="{{ $prompt->title }}" 
    @if($loop->first) fetchpriority="high" @else loading="lazy" @endif>

                            {{-- Floating Prompt Title --}}
                            {{-- Floating Prompt Title --}}
                            <div class="position-absolute top-0 start-0 m-3">
                                <span class="badge bg-dark bg-opacity-75 text-white px-3 py-2 rounded-pill shadow-sm"
                                    style="backdrop-filter: blur(4px); font-size: 0.85rem; white-space: nowrap; text-align: left;">
                                    <i class="bi bi-stars text-warning me-1"></i>
                                    {{ $prompt->title ?? 'AI Prompt' }}
                                </span>
                            </div>

                            <div class="image-view-overlay">
                                <i class="bi bi-eye"></i>
                                <span>View</span>
                            </div>

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


                    {{-- Prompt Actions --}}
                    <div class="prompt-card-actions">

                        {{-- Save Prompt --}}
                        @auth('frontend')

                            @php
                                $isSaved = auth('frontend')->user()
                                    ->savedPrompts()
                                    ->where('prompt_id', $prompt->id)
                                    ->exists();
                            @endphp

                            <button type="button" class="btn prompt-action-btn save-btn {{ $isSaved ? 'saved' : '' }}"
                                data-prompt-id="{{ $prompt->id }}" data-saved="{{ $isSaved ? '1' : '0' }}"
                                data-save-url="{{ route('prompts.save', $prompt) }}" onclick="toggleSavePrompt(this)"
                                title="{{ $isSaved ? 'Remove from Saved' : 'Save Prompt' }}">

                                <i class="bi {{ $isSaved ? 'bi-bookmark-fill' : 'bi-bookmark' }}"></i>

                                <span>
                                    {{ $isSaved ? 'Saved' : 'Save' }}
                                </span>

                            </button>

                        @else

                            <a class="btn prompt-action-btn save-btn" href="{{ route('frontend.login') }}"
                                title="Login to save prompt">

                                <i class="bi bi-bookmark"></i>

                                <span>Save</span>

                            </a>

                        @endauth


                        
    {{-- Copy Prompt --}}
@auth('frontend')

    <button type="button"
        class="btn prompt-action-btn copy-btn"
        onclick="copyPrompt('prompt-text-{{ $prompt->id }}', this, {{ $prompt->id }})"
        title="Copy Prompt">

        <i class="bi bi-clipboard"></i>
        <span>Copy</span>

    </button>

    {{-- Prompt text is rendered ONLY for logged-in users --}}
    <textarea id="prompt-text-{{ $prompt->id }}"
        class="d-none">{{ $displayPromptText }}</textarea>

@else

    <a class="btn prompt-action-btn copy-btn"
        href="{{ route('frontend.login') }}"
        title="Login to copy prompt">

        <i class="bi bi-clipboard"></i>
        <span>Copy</span>

    </a>

@endauth


                        {{-- Share Prompt --}}
                        <button type="button" class="btn prompt-action-btn share-btn"
                            onclick="sharePrompt({{ $prompt->id }}, @js($prompt->title))" title="Share Prompt">

                            <i class="bi bi-share"></i>

                            <span>Share</span>

                        </button>

                    </div>

                </article>

                <div class="modal fade" id="publicModal{{ $prompt->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                        <div class="modal-content border-0 shadow-lg rounded-4">

                            <div class="modal-header modern-modal-header border-0 pb-3">
                                <span class="badge bg-light text-primary fs-6">
                                    {{ $prompt->category->name ?? 'General' }}
                                </span>

                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                    aria-label="Close">
                                </button>
                            </div>

                            <div class="modal-body p-4">

                                <h4 class="fw-bold text-dark mb-3">
                                    {{ $prompt->title }}
                                </h4>

                                {{-- AI Tools --}}
                                @if(!empty($modalTools))
                                    <div class="mb-3 d-flex flex-wrap gap-2">

                                        @foreach($modalTools as $tool)

                                            @php
                                                $toolUrl = availableTools()[$tool] ?? '#';
                                                $readableName = ucfirst(str_replace('_', ' ', $tool));
                                            @endphp

                                            <a href="{{ $toolUrl }}" target="_blank" rel="noopener noreferrer"
                                                class="ai-tool-badge text-decoration-none">

                                                <i class="bi bi-robot"></i>
                                                {{ $readableName }}

                                            </a>

                                        @endforeach

                                    </div>
                                @endif


                                {{-- Prompt Image --}}
                                @if($prompt->image)
                                    <div class="text-center mb-3">

                                        <img src="{{ asset('storage/' . $prompt->image) }}" class="img-fluid rounded-4 border"
                                            style="max-height: 300px;" alt="{{ $prompt->title }}">

                                    </div>
                                @endif


                                {{-- Prompt Variables --}}
                                @if(!empty($promptVariables) && $promptVariables->isNotEmpty())

                                    <div class="prompt-variables-box" data-variable-box="{{ $prompt->id }}">

                                        <div class="prompt-variables-title">
                                            <i class="bi bi-sliders2-vertical"></i>
                                            Customize this prompt
                                        </div>

                                        <div class="prompt-variable-hint">
                                            Fill in the details below. The example shows you what to enter.
                                        </div>


                                        @foreach($promptVariables as $variable)

                                            @php
                                                $variableToken = trim((string) $variable);
                                                $variableSettings = $variableMeta[$variableToken] ?? [];

                                                $variableLabel = trim(
                                                    $variableSettings['label'] ?? ''
                                                );

                                                $variableExample = trim(
                                                    $variableSettings['example'] ?? ''
                                                );

                                                if ($variableLabel === '') {
                                                    $variableLabel = preg_match(
                                                        '/^\d+$/',
                                                        $variableToken
                                                    )
                                                        ? 'Variable ' . $variableToken
                                                        : 'Enter ' . $variableToken;
                                                }

                                                if ($variableExample === '') {
                                                    $variableExample = $variableToken;
                                                }
                                            @endphp


                                            <div class="prompt-variable-field">

                                                <label class="prompt-variable-label"
                                                    for="modal-prompt-variable-{{ $prompt->id }}-{{ $loop->index }}">

                                                    {{ $variableLabel }}

                                                </label>


                                                <input type="text" class="prompt-variable-input"
                                                    id="modal-prompt-variable-{{ $prompt->id }}-{{ $loop->index }}"
                                                    data-variable="{{ $variableToken }}" data-prompt-id="{{ $prompt->id }}"
                                                    placeholder="{{ $variableExample ?: 'Enter your answer' }}" autocomplete="off">

                                            </div>

                                        @endforeach


                                        <button type="button" class="prompt-variable-reset" data-prompt-id="{{ $prompt->id }}"
                                            onclick="resetPromptVariables({{ $prompt->id }})">

                                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                                            Reset

                                        </button>

                                    </div>

                                @endif


                                {{-- Prompt Text --}}
                                <label class="fw-bold mb-2 text-muted small">
                                    PROMPT TEXT
                                </label>


                                <div class="p-3 modal-prompt-text position-relative">

                                    @php
                                        $fullText = $displayPromptText;
                                        $isLong = \Illuminate\Support\Str::length($fullText) > 150;
                                        $shortText = \Illuminate\Support\Str::limit(
                                            $fullText,
                                            150,
                                            ''
                                        );
                                    @endphp


                                    <pre id="modal-text-short-{{ $prompt->id }}"
                                        style="white-space: pre-wrap; font-family: monospace; margin: 0; color: #475569;">{{ $shortText }}@if($isLong)...@endif</pre>


                                    @if($isLong)

                                        <pre id="modal-text-full-{{ $prompt->id }}"
                                            style="white-space: pre-wrap; font-family: monospace; margin: 0; display: none; color: #475569;">{{ $fullText }}</pre>


                                        <div class="text-end mt-2">

                                            <button type="button" class="btn btn-link btn-sm text-decoration-none fw-bold p-0"
                                                onclick="toggleModalText({{ $prompt->id }})">

                                                Read More
                                                <i class="bi bi-chevron-down"></i>

                                            </button>

                                        </div>

                                    @endif

                                </div>

                            </div>


                            {{-- Modal Footer --}}
                            <div class="modal-footer border-0 pt-0">

                               @auth('frontend')

    <button type="button"
        class="btn copy-btn fw-bold"
        onclick="copyPrompt('prompt-text-{{ $prompt->id }}', this, {{ $prompt->id }})">

        <i class="bi bi-clipboard-check me-1"></i>
        Copy Filled Prompt

    </button>

@else

    <a href="{{ route('frontend.login') }}"
        class="btn copy-btn fw-bold text-decoration-none">

        <i class="bi bi-box-arrow-in-right me-1"></i>
        Login to Copy

    </a>

@endauth

                            </div>

                        </div>
                    </div>
                </div>
            @empty

                <div class="text-center py-5">
                    <p class="text-muted">No prompts found.</p>
                </div>

            @endforelse

        </div>


       
{{-- Add prompt modal --}}
<div class="modal fade"
    id="addPromptModal"
    tabindex="-1"
    aria-labelledby="addPromptModalLabel"
    aria-hidden="true"
    data-bs-backdrop="static"
    data-bs-keyboard="false">

    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable frontend-prompt-modal-dialog">

        <div class="modal-content frontend-prompt-modal-content">

            {{-- Modal Header --}}
            <div class="modal-header frontend-prompt-modal-header">

                <div class="d-flex align-items-center gap-3">

                    <div class="frontend-prompt-header-icon">
                        <i class="bi bi-stars"></i>
                    </div>

                    <div>
                        <h5 class="modal-title frontend-prompt-modal-title mb-1"
                            id="addPromptModalLabel">
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
                    aria-label="Close">
                </button>

            </div>


            {{-- Form --}}
            <form action="{{ route('prompts.store') }}"
                method="POST"
                enctype="multipart/form-data">

                @csrf

                {{-- Scrollable Body --}}
                <div class="modal-body frontend-prompt-modal-body">

                    {{-- Basic Information --}}
                    <div class="frontend-form-section">

                        <div class="frontend-form-section-heading">

                            <div class="frontend-section-icon">
                                <i class="bi bi-info-circle"></i>
                            </div>

                            <div>
                                <h6 class="mb-1">Basic Information</h6>
                                <p class="mb-0">
                                    Add the basic details of your prompt.
                                </p>
                            </div>

                        </div>


                        {{-- Prompt Title --}}
                        <div class="frontend-form-group">

                            <label for="title" class="frontend-form-label">
                                Prompt Title
                                <span class="text-danger">*</span>
                            </label>

                            <input type="text"
                                class="form-control frontend-form-control @error('title') is-invalid @enderror"
                                id="title"
                                name="title"
                                value="{{ old('title') }}"
                                placeholder="Enter prompt title..."
                                required>

                            @error('title')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        {{-- Category --}}
                        <div class="frontend-form-group mb-0">

                            <label for="category_id" class="frontend-form-label">
                                Category
                                <span class="text-danger">*</span>
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
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                    </div>


                    {{-- AI Tools Section --}}
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
                        @endphp


                        <div class="frontend-form-group mb-0">

                            <label class="frontend-form-label">
                                AI Tools / Platforms
                                <span class="text-danger">*</span>
                            </label>


                            <div class="frontend-ai-tools-dropdown"
                                id="aiToolsDropdown">

                                <button type="button"
                                    class="frontend-ai-tools-dropdown-btn"
                                    id="aiToolsDropdownBtn"
                                    aria-expanded="false">

                                    <span id="selectedToolsText">
                                        Select AI Tools
                                    </span>

                                    <i class="bi bi-chevron-down"></i>

                                </button>


                                <div class="frontend-ai-tools-dropdown-menu"
                                    id="aiToolsDropdownMenu">

                                    @foreach(availableTools() as $key => $url)

                                        <label class="frontend-ai-tool-option">

                                            <input type="checkbox"
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

                                    @endforeach

                                </div>

                            </div>


                            <small class="frontend-field-hint">
                                <i class="bi bi-info-circle me-1"></i>
                                You can select multiple AI tools.
                            </small>

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
                                Prompt Text
                                <span class="text-danger">*</span>
                            </label>

                            <textarea
                                class="form-control frontend-prompt-textarea @error('prompt_text') is-invalid @enderror"
                                id="prompt_text"
                                name="prompt_text"
                                rows="7"
                                placeholder="Write your prompt here...

Use [1], [2], [3] for details that users can customize."
                                required>{{ old('prompt_text') }}</textarea>

                            <div class="frontend-prompt-hint">
                                <i class="bi bi-lightbulb me-1"></i>
                                Tip: Use
                                <strong>[1]</strong>,
                                <strong>[2]</strong>,
                                <strong>[3]</strong>
                                for customizable values.
                            </div>

                            @error('prompt_text')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                    </div>


{{-- Image Section --}}
<div class="frontend-form-section">

    <div class="frontend-form-section-heading">
        <div class="frontend-section-icon">
            <i class="bi bi-image"></i>
        </div>

        <div>
            <h6 class="mb-1">Prompt Image</h6>
            <p class="mb-0">
                Add an image to make your prompt more attractive.
            </p>
        </div>
    </div>

    <div class="frontend-form-group mb-0">

        <label for="image" class="frontend-form-label">
            Image
            <span class="frontend-optional-label">Optional</span>
        </label>

        {{-- Image Upload Option --}}
        <label for="image"
            class="frontend-image-upload-wrapper"
            id="imageUploadBox">

            <div class="frontend-image-upload-icon">
                <i class="bi bi-cloud-arrow-up"></i>
            </div>

            <div class="frontend-image-upload-content">
                <div class="frontend-image-upload-title">
                    Choose Prompt Image
                </div>

                <div class="frontend-image-upload-text">
                    JPG, JPEG, PNG, GIF or WEBP
                </div>
            </div>

        </label>

        <input type="file"
            class="visually-hidden @error('image') is-invalid @enderror"
            id="image"
            name="image"
            accept="image/jpeg,image/png,image/gif,image/webp">

        {{-- Selected Image Preview --}}
        <div id="imagePreviewBox"
            class="frontend-image-preview-box"
            style="display: none;">

            <div class="frontend-image-preview-header">
                <div>
                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                    <strong>Image Selected</strong>
                </div>

                <span class="frontend-image-preview-badge">
                    Ready to upload
                </span>
            </div>

            <img id="imagePreview"
                src=""
                alt="Selected prompt image preview">

            <div class="frontend-image-preview-details">
                <div id="imageFileName" class="frontend-image-file-name"></div>
                <div id="imageFileSize" class="frontend-image-file-size"></div>
            </div>

            <div class="frontend-image-preview-actions">
                <label for="image"
                    class="btn btn-sm btn-outline-primary mb-0">
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
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
        @enderror

        <small class="frontend-field-hint">
            <i class="bi bi-info-circle me-1"></i>
            Adding an image is optional. Maximum file size: 2 MB.
        </small>

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


        <footer class="modern-footer">
            <div class="container">
                <div class="text-center py-4 small">
                    © {{ date('Y') }} <a href="{{ route('home') }}" class="fw-bold">AI Prompt Hub</a>. All rights
                    reserved.
                </div>
            </div>
        </footer>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
</body>

  <script>
    /* Shared toast helper used by Blade modal handlers and Vue events. */
    window.showToast = function (id, message, type = 'success') {
        const container = document.getElementById('toastContainer');
        if (!container) return;
        if (typeof type === 'boolean') type = type ? 'success' : 'error';
        if (!['success', 'error', 'warning', 'info'].includes(type)) type = 'info';
        if (id) document.getElementById(id)?.remove();
        const icons = { success: 'bi-check-circle-fill', error: 'bi-x-circle-fill', warning: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill' };
        const toast = document.createElement('div');
        toast.className = 'app-toast toast-' + type;
        toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
        if (id) toast.id = id;
        const icon = document.createElement('i'); icon.className = 'bi ' + icons[type] + ' app-toast-icon';
        const content = document.createElement('div'); content.className = 'app-toast-content'; content.textContent = String(message ?? '');
        const close = document.createElement('button'); close.type = 'button'; close.className = 'app-toast-close'; close.innerHTML = '&times;'; close.setAttribute('aria-label', 'Dismiss notification');
        toast.append(icon, content, close); container.appendChild(toast);
        requestAnimationFrame(() => toast.classList.add('show'));
        let timer;
        const remove = () => { clearTimeout(timer); toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); };
        close.addEventListener('click', remove); timer = setTimeout(remove, 4000);
    };

    document.addEventListener('DOMContentLoaded', function () {
        /* AI tools multi-select dropdown in Add Prompt modal. */
        const dropdown = document.getElementById('aiToolsDropdown');
        const btn = document.getElementById('aiToolsDropdownBtn');
        const menu = document.getElementById('aiToolsDropdownMenu');
        const selectedText = document.getElementById('selectedToolsText');
        if (dropdown && btn && menu && selectedText) {
            const updateSelectedTools = () => {
                const names = Array.from(menu.querySelectorAll('input[type="checkbox"]:checked'))
                    .map(input => input.closest('label')?.querySelector('.ai-tool-name')?.textContent.trim()).filter(Boolean);
                selectedText.textContent = names.length ? names.join(', ') : 'Select AI Tools';
                selectedText.style.color = names.length ? '#1e293b' : '#475569';
                selectedText.style.fontWeight = names.length ? '600' : '400';
            };
            btn.addEventListener('click', event => {
                event.stopPropagation(); const open = menu.style.display === 'block';
                menu.style.display = open ? 'none' : 'block'; btn.setAttribute('aria-expanded', String(!open));
            });
            menu.querySelectorAll('input[type="checkbox"]').forEach(input => input.addEventListener('change', updateSelectedTools));
            document.addEventListener('click', event => {
                if (!dropdown.contains(event.target)) { menu.style.display = 'none'; btn.setAttribute('aria-expanded', 'false'); }
            });
            updateSelectedTools();
        }

        /* Prevent accidental double submission and show progress. */
        const form = document.querySelector('#addPromptModal form[enctype="multipart/form-data"]');
        const submitButton = document.getElementById('submitPromptBtn');
        if (form && submitButton) form.addEventListener('submit', event => {
            if (submitButton.dataset.submitting === 'true') { event.preventDefault(); return; }
            submitButton.dataset.submitting = 'true'; submitButton.disabled = true; submitButton.setAttribute('aria-busy', 'true');
            submitButton.querySelector('.submit-prompt-spinner')?.classList.remove('d-none');
            submitButton.querySelector('.submit-prompt-icon')?.classList.add('d-none');
            const label = submitButton.querySelector('.submit-prompt-label'); if (label) label.textContent = 'Submitting...';
        });

        * Add Prompt image preview and remove/reset behavior. */
        const imageInput = document.getElementById('image');
        const previewBox = document.getElementById('imagePreviewBox');
        const previewImage = document.getElementById('imagePreview');
        const fileName = document.getElementById('imageFileName');
        const fileSize = document.getElementById('imageFileSize');
        const uploadBox = document.getElementById('imageUploadBox');
        const removeButton = document.getElementById('removePromptImage');
        if (imageInput && previewBox && previewImage && fileName && fileSize && removeButton) {
            let previewUrl = null;
            const formatSize = bytes => bytes < 1048576 ? (bytes / 1024).toFixed(1) + ' KB' : (bytes / 1048576).toFixed(2) + ' MB';
            const clearPreview = () => {
                if (previewUrl) { URL.revokeObjectURL(previewUrl); previewUrl = null; }
                previewImage.removeAttribute('src'); fileName.textContent = ''; fileSize.textContent = ''; previewBox.style.display = 'none';
                if (uploadBox) uploadBox.style.display = 'flex';
            };
            imageInput.addEventListener('change', function () {
                const file = this.files && this.files[0]; if (!file) { clearPreview(); return; }
                if (!file.type.startsWith('image/')) { clearPreview(); imageInput.value = ''; window.showToast('imageValidationToast', 'Please select a valid image file.', 'error'); return; }
                if (previewUrl) URL.revokeObjectURL(previewUrl);
                previewUrl = URL.createObjectURL(file); previewImage.src = previewUrl; fileName.textContent = file.name; fileSize.textContent = formatSize(file.size);
                previewBox.style.display = 'block'; if (uploadBox) uploadBox.style.display = 'none';
            });
            removeButton.addEventListener('click', () => { imageInput.value = ''; clearPreview(); });
            document.getElementById('addPromptModal')?.addEventListener('hidden.bs.modal', () => { imageInput.value = ''; clearPreview(); });
        }

        @if(session('success'))
            window.showToast('sessionSuccessToast', @json(session('success')), 'success');
        @endif
    });
    </script>


</html>