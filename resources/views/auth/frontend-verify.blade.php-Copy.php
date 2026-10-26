<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Verify OTP - AI Prompt Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .hero-section { 
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%); 
            color: white; 
            padding: 60px 0; 
        }
        .btn-hero-search {
            background-color: #6366f1;
            color: #ffffff;
            border: 1px solid #4f46e5;
        }
        .prompt-card { border: 1px solid #e5e7eb; }
        .prompt-text-box { background: #f8fafc; font-family: monospace; font-size: 0.9rem; max-height: 120px; overflow-y: auto; }
        
        .background-dashboard-wrapper {
            position: relative;
            min-height: 100vh;
            overflow-x: hidden;
            background-color: #f8f9fa;
        }
        .real-dashboard-content {
            filter: blur(3px); 
            -webkit-filter: blur(3px);
            opacity: 0.95;
            pointer-events: none; 
            user-select: none;
        }
        .login-overlay-container {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1050;
            width: 100%;
            max-width: 420px;
        }
    </style>
</head>
<body class="background-dashboard-wrapper d-flex flex-column min-vh-100 m-0">

    <!-- Background Dashboard Content -->
    <div class="real-dashboard-content flex-grow-1">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 shadow-sm">
            <div class="container-fluid">
                <a class="navbar-brand d-flex align-items-center" href="#">
                    <span class="fw-bold">AI Prompt Hub</span>
                </a>
                <div class="collapse navbar-collapse show">
                    <ul class="navbar-nav ms-auto align-items-center flex-row gap-3">
                        <li class="nav-item">
                            <button type="button" class="btn btn-primary btn-sm fw-bold px-3 py-2">+ Add Prompt</button>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center text-white fw-semibold px-0" href="#">
                                <i class="bi bi-person-circle fs-5 me-1 text-primary"></i> My Account
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="hero-section text-center mb-5">
            <div class="container">
                <h1 class="fw-bold display-5 mb-3">Find & Copy Premium AI Prompts</h1>
                <p class="lead mb-4">Explore curated prompts for ChatGPT, Midjourney, and LLMs.</p>
                <div class="row justify-content-center">
                    <div class="col-md-8 position-relative">
                        <div class="row g-2 justify-content-center">
                            <div class="col-md-9">
                                <input type="text" class="form-control form-control-lg shadow-sm" placeholder="Search prompts..." disabled>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-hero-search btn-lg w-100 fw-bold">Search</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mb-5">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100 prompt-card shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <div class="mb-2"><span class="badge bg-primary">Content & Blog Writing</span></div>
                            <h5 class="card-title fw-bold text-dark mb-2">SEO Optimized Blog Post Outline Generator</h5>
                            <div class="p-2 border rounded prompt-text-box mb-3 text-muted">You are an expert content writer and SEO specialist...</div>
                            <div class="mt-auto"><button class="btn btn-success btn-sm w-100 fw-bold">Copy Prompt</button></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 prompt-card shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <div class="mb-2"><span class="badge bg-primary">Social Media & Copywriting</span></div>
                            <h5 class="card-title fw-bold text-dark mb-2">High-Converting Instagram Caption Generator</h5>
                            <div class="p-2 border rounded prompt-text-box mb-3 text-muted">You are a professional social media copywriter...</div>
                            <div class="mt-auto"><button class="btn btn-success btn-sm w-100 fw-bold">Copy Prompt</button></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 prompt-card shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <div class="mb-2"><span class="badge bg-primary">Coding & Software Development</span></div>
                            <h5 class="card-title fw-bold text-dark mb-2">Clean Code Bug Fixer & Refactor Assistant</h5>
                            <div class="p-2 border rounded prompt-text-box mb-3 text-muted">You are a senior software developer...</div>
                            <div class="mt-auto"><button class="btn btn-success btn-sm w-100 fw-bold">Copy Prompt</button></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Foreground Verify OTP Box Overlay -->
    <div class="login-overlay-container px-3">
        <div class="card shadow-lg p-4 rounded-4 border-0 bg-white">
            <div class="text-center mb-3">
                <h3 class="fw-bold text-dark">Enter OTP</h3>
                <p class="text-muted small">Please enter the 6-digit code sent to your email.</p>
            </div>

            @if(session('success'))
                <div class="alert alert-success py-2 small">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger py-2 small">{{ session('error') }}</div>
            @endif

            <!-- Form Action Route (POST request to verify OTP) -->
            <form method="POST" action="{{ route('frontend.otp.verify') }}">
                @csrf
                <div class="mb-3 text-center">
                    <input type="text" name="otp" class="form-control text-center fs-4" placeholder="------" maxlength="6" required style="letter-spacing: 5px;">
                    @error('otp')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <button type="submit" class="btn btn-success w-100 py-2 fw-bold rounded-pill shadow-sm">Verify & Login</button>
            </form>

            <div class="text-center mt-3">
                <a href="{{ route('frontend.login') }}" class="text-decoration-none small text-primary">Resend OTP / Change Email</a>
            </div>
        </div>
    </div>

</body>
</html>