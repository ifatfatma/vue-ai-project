
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $prompt->title }} | AI Prompt Hub</title>
    <meta name="description"
          content="{{ \Illuminate\Support\Str::limit(strip_tags($prompt->prompt_text), 155) }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
          rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"
          rel="stylesheet">

    <style>
        body {
            background: #f4f7ff;
            color: #111827;
            font-family: Inter, -apple-system, BlinkMacSystemFont,
                         "Segoe UI", sans-serif;
        }

        .modern-navbar {
            background: #0f172a;
            padding: 16px 0;
        }

        .brand {
            color: #fff;
            font-size: 21px;
            font-weight: 800;
            text-decoration: none;
        }

        .detail-card {
            background: #fff;
            border: 1px solid rgba(99, 102, 241, .12);
            border-radius: 18px;
            box-shadow: 0 12px 35px rgba(15, 23, 42, .08);
            overflow: hidden;
        }

        .prompt-image {
            display: block;
            width: 100%;
            max-height: 520px;
            object-fit: contain;
            background: #f8fafc;
            border-radius: 12px;
        }

        .prompt-content {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            line-height: 1.8;
            position: relative;
        }

        .prompt-content.collapsed {
            max-height: 180px;
            overflow: hidden;
        }

        .prompt-content.collapsed::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 45px;
            background: linear-gradient(
                to bottom,
                rgba(248, 250, 252, 0),
                #f8fafc
            );
            pointer-events: none;
        }

        .read-more-btn {
            display: none;
            margin-top: -12px;
            margin-bottom: 20px;
            padding: 8px 0;
            border: none;
            background: transparent;
            color: #6366f1;
            font-weight: 600;
            cursor: pointer;
        }

        .read-more-btn:hover {
            color: #4f46e5;
            text-decoration: underline;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            border: 0;
        }

        .page-heading {
            font-weight: 800;
            color: #111827;
        }
    </style>
</head>

<body>
    <nav class="modern-navbar">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="{{ url('/') }}" class="brand">
                <i class="bi bi-stars me-2"></i>AI Prompt Hub
            </a>

            <a href="{{ url('/') }}" class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Home
            </a>
        </div>
    </nav>

    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-9">

                <div class="detail-card">
                    <div class="p-4 p-md-5">

                        @if($prompt->category)
                            <span class="badge text-bg-light border mb-3">
                                {{ $prompt->category->name }}
                            </span>
                        @endif

                        <h1 class="page-heading mb-3">
                            {{ $prompt->title }}
                        </h1>

                        @if($prompt->image)
                            <div class="mb-4">
                                <img
                                    src="{{ asset('storage/' . $prompt->image) }}"
                                    alt="{{ $prompt->title }}"
                                    class="prompt-image"
                                >
                            </div>
                        @endif

                        @if($prompt->label)
                            <p class="text-muted mb-3">
                                {{ $prompt->label }}
                            </p>
                        @endif

                        @if($prompt->ai_tool)
                            @php
                                $tools = is_array($prompt->ai_tool)
                                    ? $prompt->ai_tool
                                    : json_decode($prompt->ai_tool, true);

                                $tools = is_array($tools) ? $tools : [];
                            @endphp

                            @if(count($tools))
                                <div class="mb-4">
                                    <strong class="d-block mb-2">AI Tools</strong>

                                    @foreach($tools as $tool)
                                        <span class="badge text-bg-light border me-1 mb-1">
                                            {{ $tool }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        @endif

                        <h5 class="fw-bold mb-3">Prompt</h5>

                        <div class="prompt-content collapsed mb-3"
                             id="promptText">{{ $prompt->prompt_text }}</div>

                        <button type="button"
                                id="togglePrompt"
                                class="read-more-btn"
                                aria-expanded="false">
                            Read More <i class="bi bi-chevron-down"></i>
                        </button>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="button"
                                    class="btn btn-primary"
                                    id="copyPrompt">
                                <i class="bi bi-clipboard me-1"></i>
                                Copy Prompt
                            </button>

                            <button type="button"
                                    class="btn btn-outline-primary"
                                    id="sharePrompt">
                                <i class="bi bi-share me-1"></i>
                                Share Prompt
                            </button>

                            <a href="{{ url('/') }}"
                               class="btn btn-outline-secondary">
                                <i class="bi bi-house me-1"></i>
                                Home
                            </a>
                        </div>

                        <div id="actionMessage"
                             class="small mt-3"
                             role="status"
                             aria-live="polite"></div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const copyButton = document.getElementById('copyPrompt');
            const shareButton = document.getElementById('sharePrompt');
            const promptText = document.getElementById('promptText');
            const toggleButton = document.getElementById('togglePrompt');
            const message = document.getElementById('actionMessage');

            // Read More / Read Less
            function initializeReadMore() {
                if (promptText.scrollHeight > promptText.clientHeight + 1) {
                    toggleButton.style.display = 'inline-block';
                } else {
                    promptText.classList.remove('collapsed');
                    toggleButton.style.display = 'none';
                }
            }

            initializeReadMore();

            toggleButton.addEventListener('click', function () {
                const isCollapsed = promptText.classList.toggle('collapsed');

                toggleButton.innerHTML = isCollapsed
                    ? 'Read More <i class="bi bi-chevron-down"></i>'
                    : 'Read Less <i class="bi bi-chevron-up"></i>';

                toggleButton.setAttribute(
                    'aria-expanded',
                    String(!isCollapsed)
                );
            });

            // Copy the complete prompt, even when collapsed
            copyButton.addEventListener('click', async function () {
                try {
                    await navigator.clipboard.writeText(promptText.innerText);

                    message.textContent = 'Prompt copied successfully!';
                    message.className = 'small mt-3 text-success';
                } catch (error) {
                    message.textContent =
                        'Unable to copy. Please select and copy the prompt manually.';
                    message.className = 'small mt-3 text-danger';
                }
            });

            // Share the prompt
            shareButton.addEventListener('click', async function () {
                const shareData = {
                    title: @json($prompt->title),
                    text: promptText.innerText,
                    url: window.location.href
                };

                try {
                    if (navigator.share) {
                        await navigator.share(shareData);
                    } else {
                        await navigator.clipboard.writeText(window.location.href);

                        message.textContent = 'Prompt link copied successfully!';
                        message.className = 'small mt-3 text-success';
                    }
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        message.textContent = 'Unable to share this prompt.';
                        message.className = 'small mt-3 text-danger';
                    }
                }
            });
        });
    </script>
</body>
</html>
