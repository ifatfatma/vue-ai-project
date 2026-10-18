
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin Login | AI Prompt Hub</title>

    @include('layouts.partials.css')


@php
    $loginBg = \App\Models\Setting::where('key', 'login_background')->first();

    $bgUrl = null;

    if ($loginBg && $loginBg->value) {
        $bgUrl = asset('storage/' . ltrim($loginBg->value, '/'));
    }
@endphp

    <style>
        html, body {
            min-height: 100%;
            margin: 0;
        }

        body {
            padding-top: 0 !important;
            background: #18243a;
        }

        .container-scroller,
        .page-body-wrapper,
        .full-page-wrapper {
            min-height: 100vh;
            width: 100%;
        }

        

.login-page {
    min-height: 100vh;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 30px 15px;

    background-color: #18243a;
    background-image:
        linear-gradient(
            135deg,
            rgba(10, 20, 40, .35),
            rgba(40, 20, 70, .25)
        ),
        {{ $bgUrl ? 'url(' . $bgUrl . ')' : 'none' }};

    background-position: center;
    background-size: cover;
    background-repeat: no-repeat;
    background-attachment: fixed;
}
        .login-card {
            width: 100%;
            max-width: 440px;
            padding: 38px 35px;
            background: rgba(255, 255, 255, .16);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255, 255, 255, .35);
            border-radius: 20px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, .3);
        }

        .login-card h3 {
            color: #fff;
            font-weight: 700;
            margin-bottom: 28px;
        }

        .login-card label {
            color: #fff;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .login-card .form-group {
            margin-bottom: 22px;
        }

        .login-card .form-control {
            height: 48px;
            padding: 12px 15px;
            border-radius: 10px;
            color: #fff;
            background: rgba(255, 255, 255, .17);
            border: 1px solid rgba(255, 255, 255, .5);
        }

        .login-card .form-control::placeholder {
            color: rgba(255, 255, 255, .8);
        }

        .login-card .form-control:focus {
            background: rgba(255, 255, 255, .24);
            border-color: #fff;
            color: #fff;
            box-shadow: 0 0 0 .2rem rgba(255,255,255,.15);
        }

        .login-card .submit-btn {
            width: 100%;
            height: 48px;
            border: 0;
            border-radius: 10px;
            background: #6558d3;
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            transition: .2s ease;
        }

        .login-card .submit-btn:hover {
            background: #5143bf;
            transform: translateY(-2px);
        }

        .login-card .alert {
            border-radius: 10px;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 28px 22px;
            }
        }
    </style>
</head>

<body>
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <main class="login-page">
                <div class="login-card">

                    <h3 class="text-center">Admin Login</h3>

                    @if ($errors->any())
                        <div class="alert alert-danger py-2" role="alert">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close"
                                data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('admin.login.submit') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control"
                                placeholder="Enter your email"
                                value="{{ old('email', 'test@gmail.com') }}"
                                autocomplete="username"
                                required
                            >
                        </div>
<div class="form-group">
    <label for="password">Password</label>
    <input
        type="password"
        id="password"
        name="password"
        class="form-control"
        placeholder="Enter your password"
        autocomplete="current-password"
        value=""
        required
    >
</div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary submit-btn">
                                Login
                            </button>
                        </div>
                    </form>

                </div>
            </main>
        </div>
    </div>

    @include('layouts.partials.js')
</body>


</html>