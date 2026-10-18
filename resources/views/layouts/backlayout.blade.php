
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin Dashboard - AI Prompt Project</title>

    @include('layouts.partials.css')
</head>

<body>
    <div class="container-scroller">

        <!-- Header -->
        @include('layouts.partials.header')

        <div class="container-fluid page-body-wrapper">

            <!-- Sidebar -->
            @include('layouts.partials.nav')

            <!-- Main Panel -->
            <div class="main-panel">

                <!-- Main Content -->
                <div class="content-wrapper">
                    @yield('content')
                </div>

                <!-- Footer -->
                @include('layouts.partials.footer')

            </div>
        </div>
    </div>

    <!-- JavaScript -->
    @include('layouts.partials.js')
</body>
</html>