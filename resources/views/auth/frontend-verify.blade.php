<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Verify OTP - AI Prompt Hub</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        <verify-otp
            verify-url="{{ route('frontend.otp.verify') }}"
            login-url="{{ route('frontend.login') }}"
            csrf-token="{{ csrf_token() }}"
            initial-success="{{ session('success') }}"
            initial-error="{{ session('error') }}"
            initial-otp="{{ old('otp') }}"
            :validation-errors="{{ Illuminate\Support\Js::from($errors->messages()) }}"
        ></verify-otp>
    </div>
</body>
</html>
