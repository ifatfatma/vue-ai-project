<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $prompt->title }} | AI Prompt Hub</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        <prompt-details
            :prompt="{{ Illuminate\Support\Js::from([
                'id' => $prompt->id,
                'title' => $prompt->title,
                'label' => $prompt->label,
                'prompt_text' => $prompt->prompt_text,
                'image' => $prompt->image,
                'image_url' => $prompt->image
                    ? asset('storage/' . $prompt->image)
                    : null,
                'ai_tool' => $prompt->ai_tool,
                'category' => $prompt->category
                    ? ['name' => $prompt->category->name]
                    : null,
            ]) }}"
            home-url="{{ url('/') }}"
            copy-track-url="{{ url('/prompts/' . $prompt->id . '/copy-track') }}"
        ></prompt-details>
    </div>
</body>
</html>
