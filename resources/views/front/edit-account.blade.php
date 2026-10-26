
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Profile - AI Prompt Hub</title>

    <link
        rel="icon"
        type="image/png"
        href="{{ asset('images/favicon.png') }}"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
  
@php
    $profileUserData = [
        'name' => old('name', $user->name),
        'email' => old('email', $user->email),
        'profileImage' => $user->profile_image
            ? asset($user->profile_image)
            : null,
    ];

    $profileErrors = $errors->messages();
    $profileSuccess = session('success', '');
@endphp

<div id="app">
    <user-profile
        :initial-user="{{ Illuminate\Support\Js::from($profileUserData) }}"
        update-url="{{ route('user.profile.update') }}"
        home-url="{{ url('/') }}"
        csrf-token="{{ csrf_token() }}"
        :success-message="{{ Illuminate\Support\Js::from($profileSuccess) }}"
        :errors="{{ Illuminate\Support\Js::from($profileErrors) }}"
    ></user-profile>
</div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
