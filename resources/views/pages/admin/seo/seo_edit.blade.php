@extends('layouts.backlayout')

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Page Heading --}}
    <div class="mb-4">
        <h2 class="fw-bold mb-2">Website SEO Settings</h2>
        <p class="text-muted mb-0">
            Manage your website's HTML meta tags and social sharing image.
        </p>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close">
            </button>
        </div>
    @endif

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger rounded-3" role="alert">
            <strong>
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Please fix the following errors:
            </strong>

            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 seo-settings-card">
        <div class="card-body p-4 p-md-5">

            <form
                action="{{ route('admin.seo.update') }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf

                {{-- Raw HTML Meta Tags --}}
                <div class="mb-4">
                    <label for="raw_meta_tags" class="form-label fw-bold">
                        <i class="bi bi-code-slash me-2 text-primary"></i>
                        Raw HTML Meta Tags
                    </label>

                    <p class="text-muted small mb-2">
                        Paste your HTML meta tags below, including title,
                        description, keywords and Open Graph tags.
                    </p>

                    <textarea
                        id="raw_meta_tags"
                        name="raw_meta_tags"
                        class="form-control font-monospace seo-meta-textarea @error('raw_meta_tags') is-invalid @enderror"
                        rows="12"
                        placeholder="<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<title>Your Website Title</title>
<meta name='description' content='Your website description'>
<meta property='og:title' content='Your Website Title'>
<meta property='og:description' content='Your website description'>"
                    >{{ old('raw_meta_tags', $seo->raw_meta_tags ?? '') }}</textarea>

                    @error('raw_meta_tags')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="form-text">
                        Keep your tags valid HTML. Avoid duplicate title,
                        description and Open Graph tags.
                    </div>
                </div>

                <hr class="my-4">

                {{-- Social Share Image --}}
                <div class="mb-4">
                    <label for="og_image" class="form-label fw-bold">
                        <i class="bi bi-image me-2 text-primary"></i>
                        Social Share Image (OG Image)
                    </label>

                    <p class="text-muted small mb-3">
                        This image can appear when your website link is shared
                        on WhatsApp, Facebook and other social platforms.
                    </p>

                    {{-- Current Image Preview --}}
                <div class="mb-3">
    <p class="small fw-semibold mb-2">
        Current social sharing image:
    </p>

    @if(!empty($seo->og_image))
      
        <img
            src="{{ asset('storage/' . $seo->og_image) }}"
            alt="Current social sharing image"
            class="img-fluid rounded-3 border seo-og-preview"
            loading="lazy"
        >
    @else
      
        <img
            src="{{ asset('images/seo.banner.jpeg') }}"
            alt="Default social sharing image"
            class="img-fluid rounded-3 border seo-og-preview"
            loading="lazy"
        >
    @endif
</div>

                    {{-- New Image Preview --}}
                    <div id="ogImagePreviewWrapper" class="mb-3" style="display: none;">
                        <p class="small fw-semibold mb-2">
                            New image preview:
                        </p>

                        <img
                            id="ogImagePreview"
                            src=""
                            alt="New social sharing image preview"
                            class="img-fluid rounded-3 border seo-og-preview"
                        >
                    </div>

                    <input
                        type="file"
                        id="og_image"
                        name="og_image"
                        class="form-control @error('og_image') is-invalid @enderror"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    >

                    @error('og_image')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="form-text">
                        Recommended dimensions: 1200 × 630 pixels.
                        Allowed formats: JPG, JPEG, PNG and WebP.
                        Maximum file size: 2 MB.
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="d-flex flex-wrap gap-2 pt-2">
                    <button
                        type="submit"
                        class="btn btn-primary px-4 py-2 fw-semibold rounded-3"
                    >
                        <i class="bi bi-save me-2"></i>
                        Save SEO Settings
                    </button>

                    <button
                        type="reset"
                        class="btn btn-outline-secondary px-4 py-2 rounded-3"
                        id="resetSeoForm"
                    >
                        <i class="bi bi-arrow-counterclockwise me-2"></i>
                        Reset Changes
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- Page Styles --}}
<style>
    .seo-settings-card {
        border: 1px solid #e9ecef;
        overflow: hidden;
    }

    .seo-meta-textarea {
        min-height: 260px;
        resize: vertical;
        font-size: 13px;
        line-height: 1.7;
        background-color: #f8fafc;
        border-color: #dee2e6;
        border-radius: 10px;
        padding: 15px;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
    }

    .seo-meta-textarea:focus {
        background-color: #fff;
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.12);
    }

    .seo-og-preview {
        display: block;
        width: 100%;
        max-width: 420px;
        max-height: 260px;
        object-fit: contain;
        background-color: #f8f9fa;
    }

    #og_image {
        padding: 12px;
        border-radius: 10px;
    }

    @media (max-width: 576px) {
        .seo-meta-textarea {
            min-height: 220px;
            font-size: 12px;
        }

        .seo-settings-card .card-body {
            padding: 20px !important;
        }
    }
</style>

{{-- Image Preview Script --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const imageInput = document.getElementById('og_image');
        const previewWrapper = document.getElementById('ogImagePreviewWrapper');
        const previewImage = document.getElementById('ogImagePreview');
        const resetButton = document.getElementById('resetSeoForm');

        let previewUrl = null;

        if (imageInput && previewWrapper && previewImage) {
            imageInput.addEventListener('change', function () {
                const file = this.files && this.files[0];

                if (previewUrl) {
                    URL.revokeObjectURL(previewUrl);
                    previewUrl = null;
                }

                if (!file) {
                    previewImage.removeAttribute('src');
                    previewWrapper.style.display = 'none';
                    return;
                }

                const allowedTypes = [
                    'image/jpeg',
                    'image/png',
                    'image/webp'
                ];

                if (!allowedTypes.includes(file.type)) {
                    alert('Please select a JPG, PNG or WebP image.');
                    this.value = '';
                    previewImage.removeAttribute('src');
                    previewWrapper.style.display = 'none';
                    return;
                }

                if (file.size > 2 * 1024 * 1024) {
                    alert('Image size must not exceed 2 MB.');
                    this.value = '';
                    previewImage.removeAttribute('src');
                    previewWrapper.style.display = 'none';
                    return;
                }

                previewUrl = URL.createObjectURL(file);
                previewImage.src = previewUrl;
                previewWrapper.style.display = 'block';
            });
        }

        if (resetButton) {
            resetButton.addEventListener('click', function () {
                if (previewUrl) {
                    URL.revokeObjectURL(previewUrl);
                    previewUrl = null;
                }

                if (previewImage) {
                    previewImage.removeAttribute('src');
                }

                if (previewWrapper) {
                    previewWrapper.style.display = 'none';
                }
            });
        }
    });
</script>
@endsection
