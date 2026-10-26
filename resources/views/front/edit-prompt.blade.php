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

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @php
        $availableToolOptions = function_exists('availableTools')
            ? availableTools()
            : [];

        $storedTools = old(
            'ai_tools',
            $prompt->ai_tools ?? $prompt->ai_tool ?? $prompt->tools ?? []
        );

        if (is_string($storedTools)) {
            $decodedTools = json_decode($storedTools, true);

            $storedTools = is_array($decodedTools)
                ? $decodedTools
                : (strlen($storedTools) ? explode(',', $storedTools) : []);
        }

        if (!is_array($storedTools)) {
            $storedTools = [];
        }

        $storedTools = array_map('strval', $storedTools);

        $promptData = [
            'id' => $prompt->id,
            'title' => old('title', $prompt->title),
            'category_id' => old('category_id', $prompt->category_id),
            'prompt_text' => old('prompt_text', $prompt->prompt_text),
        ];

        $categoryData = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
            ];
        })->values()->all();

        $toolData = [];

        foreach ($availableToolOptions as $key => $url) {
            $toolData[] = [
                'key' => (string) $key,
                'label' => ucfirst(str_replace('_', ' ', (string) $key)),
            ];
        }

        $currentImageUrl = $prompt->image
            ? asset('storage/' . $prompt->image)
            : '';

        $errorData = $errors->messages();
    @endphp

    <div id="app">
        <edit-prompt
            :prompt="{{ Illuminate\Support\Js::from($promptData) }}"
            :categories="{{ Illuminate\Support\Js::from($categoryData) }}"
            :available-tools="{{ Illuminate\Support\Js::from($toolData) }}"
            :selected-tools="{{ Illuminate\Support\Js::from($storedTools) }}"
            update-url="{{ route('user.prompts.update', $prompt->id) }}"
            my-prompts-url="{{ route('user.prompts') }}"
            home-url="{{ route('home') }}"
            csrf-token="{{ csrf_token() }}"
            :success-message="{{ Illuminate\Support\Js::from(session('success', '')) }}"
            :errors="{{ Illuminate\Support\Js::from($errorData) }}"
            current-image-url="{{ $currentImageUrl }}"
        ></edit-prompt>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
