<!DOCTYPE html>
<html lang="en">
<head>
    {{-- Basic Meta Tags --}}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $seo = \App\Models\SeoSetting::first();

        $defaultTitle = 'AI Prompt Hub - Discover & Copy Best Prompts';
        $defaultDescription = 'Find the best AI prompts for ChatGPT, Midjourney, and more.';
        $defaultKeywords = 'ai prompts, chatgpt prompts, midjourney prompts';

        $pageTitle = trim($__env->yieldContent(
            'title',
            $seo?->meta_title ?: $defaultTitle
        ));

        $metaDescription = $seo?->meta_description ?: $defaultDescription;
        $metaKeywords = $seo?->meta_keywords ?: $defaultKeywords;

        $ogImageWidth = 1200;
        $ogImageHeight = 630;

        $defaultOgImage = asset('images/seo.banner.jpeg');

        $ogImage = $defaultOgImage;

        if (!empty($seo?->og_image)) {
            $imagePath = public_path('storage/' . $seo->og_image);

            if (file_exists($imagePath)) {
                $ogImage = asset('storage/' . $seo->og_image);

                $size = @getimagesize($imagePath);

                if ($size) {
                    $ogImageWidth = $size[0];
                    $ogImageHeight = $size[1];
                }
            }
        }
    @endphp

    {{-- Page Title --}}
    <title>{{ $pageTitle }}</title>

    {{-- SEO Meta Tags --}}
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="keywords" content="{{ $metaKeywords }}">

    {{-- Open Graph --}}
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:secure_url" content="{{ $ogImage }}">
    <meta property="og:image:width" content="{{ $ogImageWidth }}">
    <meta property="og:image:height" content="{{ $ogImageHeight }}">
    <meta property="og:image:alt" content="AI Prompt Hub - Discover and copy AI prompts">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="AI Prompt Hub">

    {{-- Twitter / X --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
    <meta name="twitter:image:alt" content="AI Prompt Hub - Discover and copy AI prompts">

    {{-- Common Frontend CSS --}}
    @include('layouts.partials.css')

    {{-- Font Awesome --}}
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    >

    {{-- Page-Specific CSS --}}
    @stack('styles')

    {{-- Admin-Managed Raw Meta Tags --}}
    {{-- Ensure this field contains trusted, sanitized HTML only. --}}
    {!! $seo?->raw_meta_tags ?? '' !!}

    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

   
</head>

<body class="d-flex flex-column min-vh-100 bg-light">
     @include('front.nav')
     <div id="app">
            {{-- Main Content --}}
            <main class="flex-grow-1">
                @yield('content')
            </main>
     </div>

        {{-- Add Prompt Modal --}}
    @include('front.add-prompt-modal')
    {{-- Footer --}}
    @include('layouts.partials.footer')

    {{-- Common Frontend JS --}}
    @include('layouts.partials.js')

    {{-- Page-Specific JS --}}
    @stack('scripts')

</body>
</html>