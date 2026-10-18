@extends('layouts.backlayout')

@section('content')

<div class="content-wrapper settings-page">

    {{-- Page Header --}}
    <div class="page-header mb-4">
        <div>
            <h3 class="page-title fw-bold">Account & System Settings</h3>
            <p class="text-muted mb-0">
                Manage your account, website branding and SEO settings.
            </p>
        </div>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
            <i class="mdi mdi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- General Error Message --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
            <i class="mdi mdi-alert-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger rounded-3" role="alert">
            <strong>
                <i class="mdi mdi-alert-circle me-2"></i>
                Please fix the following errors:
            </strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- PERSONAL DETAILS & PASSWORD --}}
    <div class="row">

        {{-- Personal Details --}}
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card settings-card">
                <div class="card-body">
                    <h4 class="card-title text-primary">
                        <i class="mdi mdi-account-edit me-2"></i>
                        Personal Details
                    </h4>
                    <p class="card-description">
                        Update your account personal information.
                    </p>

                    <form action="{{ route('admin.settings.profile.update') }}" method="POST">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="name" class="fw-bold">Full Name</label>
                            <input
                                type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                id="name"
                                name="name"
                                value="{{ old('name', $user->name ?? '') }}"
                                maxlength="255"
                                required
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label for="email" class="fw-bold">Email Address</label>
                            <input
                                type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                id="email"
                                name="email"
                                value="{{ old('email', $user->email ?? '') }}"
                                maxlength="255"
                                required
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-content-save me-1"></i>
                            Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>


        {{-- Change Password --}}
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card settings-card">
                <div class="card-body">
                    <h4 class="card-title text-danger">
                        <i class="mdi mdi-lock-reset me-2"></i>
                        Change Password
                    </h4>
                    <p class="card-description">
                        Use a strong password to protect your account.
                    </p>

                    <form action="{{ route('admin.settings.password.update') }}" method="POST">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="current_password" class="fw-bold">Current Password</label>
                            <input
                                type="password"
                                class="form-control @error('current_password') is-invalid @enderror"
                                id="current_password"
                                name="current_password"
                                autocomplete="current-password"
                                required
                            >
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="password" class="fw-bold">New Password</label>
                            <input
                                type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                id="password"
                                name="password"
                                autocomplete="new-password"
                                minlength="8"
                                required
                            >
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-4">
                            <label for="password_confirmation" class="fw-bold">
                                Confirm New Password
                            </label>
                            <input
                                type="password"
                                class="form-control"
                                id="password_confirmation"
                                name="password_confirmation"
                                autocomplete="new-password"
                                minlength="8"
                                required
                            >
                        </div>

                        <button type="submit" class="btn btn-danger">
                            <i class="mdi mdi-lock-check me-1"></i>
                            Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>


    {{-- PROFILE PICTURE & WEBSITE LOGO --}}
    <div class="row">

        {{-- Profile Picture --}}
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card settings-card">
                <div class="card-body">
                    <h4 class="card-title text-info">
                        <i class="mdi mdi-account-circle me-2"></i>
                        Profile Picture
                    </h4>
                    <p class="card-description">
                        Update your personal profile avatar.
                    </p>

                    <form
                        action="{{ route('profile.image.update') }}"
                        method="POST"
                        enctype="multipart/form-data"
                    >
                        @csrf

                        <div class="row align-items-center mb-3">
                            <div class="col-4 col-md-3 text-center mb-3 mb-md-0">
                                @if(!empty($user->profile_image))
                                    <img
                                        src="{{ asset($user->profile_image) }}"
                                        alt="Profile Picture"
                                        class="rounded-circle shadow-sm profile-preview"
                                        width="80"
                                        height="80"
                                    >
                                @else
                                    <img
                                        src="https://ui-avatars.com/api/?name={{ urlencode($user->name ?? 'Admin') }}&background=0D6EFD&color=fff"
                                        alt="Default Profile Picture"
                                        class="rounded-circle shadow-sm profile-preview"
                                        width="80"
                                        height="80"
                                    >
                                @endif
                            </div>

                            <div class="col-8 col-md-9">
                                <label for="profile_image" class="fw-bold mb-2">
                                    Choose Profile Image
                                </label>
                                <input
                                    type="file"
                                    class="form-control @error('profile_image') is-invalid @enderror"
                                    id="profile_image"
                                    name="profile_image"
                                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                    required
                                >
                                @error('profile_image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    JPG, PNG or WebP. Follow the server's upload size limit.
                                </small>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-info text-white">
                            <i class="mdi mdi-upload me-1"></i>
                            Update Profile Picture
                        </button>
                    </form>
                </div>
            </div>
        </div>


        {{-- Website Logo --}}
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card settings-card">
                <div class="card-body">
                    <h4 class="card-title text-success">
                        <i class="mdi mdi-image-edit me-2"></i>
                        Website Logo
                    </h4>
                    <p class="card-description">
                        Update the brand logo displayed in your website header.
                    </p>

                    <form
                        action="{{ route('admin.settings.logo.update') }}"
                        method="POST"
                        enctype="multipart/form-data"
                    >
                        @csrf

                        <div class="row align-items-center mb-3">
                            <div class="col-4 col-md-3 text-center mb-3 mb-md-0">
                                @if(!empty($logo ?? null))
                                    <img
                                        src="{{ asset($logo) }}"
                                        alt="Website Logo"
                                        class="logo-preview shadow-sm"
                                    >
                                @else
                                    <span class="text-muted small">No logo uploaded</span>
                                @endif
                            </div>

                            <div class="col-8 col-md-9">
                                <label for="logo" class="fw-bold mb-2">
                                    Choose Brand Logo
                                </label>
                                <input
                                    type="file"
                                    class="form-control @error('logo') is-invalid @enderror"
                                    id="logo"
                                    name="logo"
                                    accept=".jpg,.jpeg,.png,.webp,.svg,image/jpeg,image/png,image/webp,image/svg+xml"
                                    required
                                >
                                @error('logo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success">
                            <i class="mdi mdi-upload me-1"></i>
                            Update Website Logo
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>


    {{-- LOGIN BACKGROUND & SEO SETTINGS --}}
    <div class="row">

        {{-- Admin Login Background --}}
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card settings-card">
                <div class="card-body">
                    <h4 class="card-title text-warning">
                        <i class="mdi mdi-wallpaper me-2"></i>
                        Admin Login Background
                    </h4>
                    <p class="card-description">
                        Change the background image on the admin login screen.
                    </p>

                    <form
                        action="{{ route('admin.settings.login.bg.update') }}"
                        method="POST"
                        enctype="multipart/form-data"
                    >
                        @csrf

                        <div class="form-group mb-3">
                            <label for="login_background" class="form-label fw-bold">
                                Choose Background Image
                            </label>
                            <input
                                type="file"
                                class="form-control @error('login_background') is-invalid @enderror"
                                id="login_background"
                                name="login_background"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                required
                            >
                            @error('login_background')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-warning text-white">
                            <i class="mdi mdi-upload me-1"></i>
                            Update Background
                        </button>
                    </form>
                </div>
            </div>
        </div>


        {{-- Website SEO Settings --}}
        <div class="col-md-6 grid-margin stretch-card">
            <div class="card settings-card">
                <div class="card-body">
                    <h4 class="card-title text-primary">
                        <i class="mdi mdi-web me-2"></i>
                        Website SEO Settings
                    </h4>
                    <p class="card-description">
                        Manage raw HTML meta tags and your social sharing image.
                    </p>

                    <form
                        action="{{ route('admin.seo.update') }}"
                        method="POST"
                        enctype="multipart/form-data"
                    >
                        @csrf

                        {{-- Raw HTML Meta Tags --}}
                        <div class="form-group mb-3">
                            <label for="raw_meta_tags" class="fw-bold">
                                Raw HTML Meta Tags
                            </label>

                            <textarea
                                class="form-control font-monospace seo-meta-textarea @error('raw_meta_tags') is-invalid @enderror"
                                id="raw_meta_tags"
                                name="raw_meta_tags"
                                rows="8"
                                placeholder="Paste your HTML meta tags here..."
                            >{{ old('raw_meta_tags', $seo->raw_meta_tags ?? '') }}</textarea>

                            @error('raw_meta_tags')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <small class="text-muted">
                                Add valid HTML meta tags. Avoid duplicate tags in your website head.
                            </small>
                        </div>

                       {{-- Current OG Image --}}
<div class="mb-3">
    <label class="form-label fw-bold">
        Current Social Sharing Image
    </label>

    @if(!empty($seo?->og_image))
        {{-- Agar database mein custom image uploaded hai --}}
        <div class="mb-3">
            <img
                src="{{ asset('storage/' . $seo->og_image) }}"
                alt="Current OG Image"
                class="og-preview rounded-3 border"
            >
        </div>
    @else
        
        <div class="mb-3">
            <img
                src="{{ asset('images/seo.banner.jpeg') }}"
                alt="Default Social Banner"
                class="og-preview rounded-3 border"
            >
            <div class="form-text text-muted mt-1">Showing default system banner. Upload a custom image above to replace it.</div>
        </div>
    @endif
</div>

                        {{-- New OG Image Preview --}}
                        <div id="ogPreviewWrapper" class="mb-3" style="display: none;">
                            <label class="form-label fw-bold">New Image Preview</label>
                            <div>
                                <img
                                    id="ogPreview"
                                    src=""
                                    alt="New social sharing image preview"
                                    class="og-preview rounded-3 border"
                                >
                            </div>
                        </div>

                        {{-- OG Image Upload --}}
                        <div class="form-group mb-3">
                            <label for="og_image" class="fw-bold">
                                Choose Social Sharing Image
                            </label>

                            <input
                                type="file"
                                class="form-control @error('og_image') is-invalid @enderror"
                                id="og_image"
                                name="og_image"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            >

                            @error('og_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <small class="text-muted">
                                Recommended size: 1200 × 630 pixels. Maximum size: 2 MB.
                                Allowed formats: JPG, PNG and WebP.
                            </small>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-content-save me-1"></i>
                            Save SEO Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>

</div>







{{-- Page Styles --}}
<style>
    .settings-page .settings-card {
        height: 100%;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        box-shadow: 0 3px 15px rgba(0, 0, 0, 0.04);
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }

    .settings-page .settings-card:hover {
        box-shadow: 0 7px 22px rgba(0, 0, 0, 0.07);
    }

    .settings-page .card-body {
        padding: 24px;
    }

    .settings-page .card-title {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .settings-page .card-description {
        color: #7b8190;
        font-size: 13px;
        margin-bottom: 22px;
    }

    .settings-page .form-control {
        border-radius: 8px;
        min-height: 42px;
    }

    .settings-page textarea.form-control {
        min-height: 180px;
        resize: vertical;
        line-height: 1.6;
        font-size: 12px;
        background: #f8fafc;
        overflow-wrap: anywhere;
    }

    .settings-page textarea.form-control:focus,
    .settings-page input.form-control:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.12);
    }

    .settings-page .btn {
        border-radius: 7px;
        font-weight: 600;
        padding: 9px 16px;
    }

    .profile-preview {
        object-fit: cover;
        border: 2px solid #e9ecef;
    }

    .logo-preview {
        max-width: 100%;
        max-height: 75px;
        object-fit: contain;
    }

    .og-preview {
        display: block;
        width: 100%;
        max-width: 320px;
        max-height: 190px;
        object-fit: contain;
        background: #f8f9fa;
    }

    @media (max-width: 767px) {
        .settings-page .card-body {
            padding: 18px;
        }

        .settings-page .page-title {
            font-size: 22px;
        }

        .settings-page textarea.form-control {
            min-height: 150px;
        }
    }




</style>


{{-- OG Image Preview Script --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const imageInput = document.getElementById('og_image');
        const previewWrapper = document.getElementById('ogPreviewWrapper');
        const previewImage = document.getElementById('ogPreview');

        let previewUrl = null;

        if (!imageInput || !previewWrapper || !previewImage) {
            return;
        }

        imageInput.addEventListener('change', function () {
            const file = this.files && this.files[0];

            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
                previewUrl = null;
            }

            previewImage.removeAttribute('src');
            previewWrapper.style.display = 'none';

            if (!file) {
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
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                alert('The OG image must not exceed 2 MB.');
                this.value = '';
                return;
            }

            previewUrl = URL.createObjectURL(file);
            previewImage.src = previewUrl;
            previewWrapper.style.display = 'block';
        });
    });
    


@endsection
