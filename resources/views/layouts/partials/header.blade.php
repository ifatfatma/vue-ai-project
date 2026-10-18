@php
    $adminUser = auth()->user();

    // Profile image URL
    $profileImage = $adminUser?->profile_image;
    $profileImageUrl = null;

    if ($profileImage) {
        if (filter_var($profileImage, FILTER_VALIDATE_URL)) {
            $profileImageUrl = $profileImage;
        } else {
            $profileImage = ltrim(
                str_replace('\\', '/', $profileImage),
                '/'
            );

            // Remove existing storage/ or public/ prefix
            $profileImage = preg_replace(
                '#^(storage/|public/)#',
                '',
                $profileImage
            );

            // 🌟 Yahan 'storage/' hata diya hai kyunki file direct public folder me hai
            $profileImageUrl = asset($profileImage);
        }
    }

    // Fallback avatar
    $avatarUrl = 'https://ui-avatars.com/api/?name='
        . urlencode($adminUser?->name ?? 'Admin')
        . '&background=0D6EFD&color=fff';
@endphp

<nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex flex-row">

    <!-- Brand Logo Area -->
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">

        <a class="navbar-brand brand-logo text-decoration-none d-flex align-items-center justify-content-center"
           href="{{ route('admin.dashboard') }}">

            @if($adminUser?->logo)
                <img src="{{ asset($adminUser->logo) }}"
                     alt="Logo"
                     class="me-2"
                     style="height: 30px; width: auto; max-width: 90px; object-fit: contain;">
            @else
                <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center me-2"
                     style="width: 28px; height: 28px; font-weight: bold; font-size: 13px;">
                    {{ strtoupper(substr($adminUser?->name ?? 'A', 0, 1)) }}
                </div>
            @endif

            <span style="font-size: 14px; font-weight: 700; color: #FFFFFF; letter-spacing: 0.3px;">
                AI Prompt Hub
            </span>

        </a>

        <!-- Mini Logo -->
        <a class="navbar-brand brand-logo-mini"
           href="{{ route('admin.dashboard') }}">

            @if($adminUser?->logo)
                <img src="{{ asset($adminUser->logo) }}"
                     alt="Logo"
                     style="max-height: 30px; object-fit: contain;">
            @else
                <span class="text-white fw-bold">AI</span>
            @endif

        </a>

    </div>


    <!-- Navbar Menu Wrapper -->
    <div class="navbar-menu-wrapper d-flex align-items-center justify-content-between flex-grow-1 px-3">

        <!-- Right Side: Icons & Profile -->
        <ul class="navbar-nav navbar-nav-right ms-auto d-flex align-items-center mb-0 gap-3"
            style="list-style: none;">

            <!-- Live Website -->
            <li class="nav-item">
                <a class="nav-link text-dark p-0"
                   href="{{ url('/') }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   title="Visit Live Website">

                    <i class="mdi mdi-earth" style="font-size: 20px;"></i>

                </a>
            </li>


            <!-- User Profile Dropdown -->
            <li class="nav-item dropdown" id="userProfileDropdownContainer">

                <a class="nav-link dropdown-toggle d-flex align-items-center text-dark text-decoration-none p-0"
                   id="profileDropdown"
                   href="#"
                   role="button"
                   data-bs-toggle="dropdown"
                   aria-expanded="false">

                    <!-- Header Profile Image -->
                    @if($profileImageUrl)
                        <img class="rounded-circle me-2 shadow-sm"
                             src="{{ $profileImageUrl }}"
                             alt="Profile"
                             style="width: 32px; height: 32px; object-fit: cover;"
                             onerror="this.onerror=null;this.src='{{ $avatarUrl }}';">
                    @else
                        <img class="rounded-circle me-2 shadow-sm"
                             src="{{ $avatarUrl }}"
                             alt="Profile"
                             style="width: 32px; height: 32px; object-fit: cover;">
                    @endif

                    <span class="fw-bold" style="font-size: 14px;">
                        {{ $adminUser?->name ?? 'Admin' }}
                    </span>

                </a>


                <!-- Dropdown Card -->
                <div class="dropdown-menu dropdown-menu-end navbar-dropdown p-3 shadow-lg border-0"
                     aria-labelledby="profileDropdown"
                     style="min-width: 220px;">

                    <!-- Profile Details -->
                    <div class="text-center pb-3 border-bottom mb-2">

                        <!-- Dropdown Profile Image -->
                        @if($profileImageUrl)
                            <img class="rounded-circle mb-2 shadow-sm"
                                 src="{{ $profileImageUrl }}"
                                 alt="Profile"
                                 style="width: 60px; height: 60px; object-fit: cover;"
                                 onerror="this.onerror=null;this.src='{{ $avatarUrl }}';">
                        @else
                            <img class="rounded-circle mb-2 shadow-sm"
                                 src="{{ $avatarUrl }}"
                                 alt="Profile"
                                 style="width: 60px; height: 60px; object-fit: cover;">
                        @endif

                        <h6 class="mb-0 fw-bold">
                            {{ $adminUser?->name ?? 'Admin User' }}
                        </h6>

                        <small class="text-muted">
                            {{ $adminUser?->email ?? 'admin@aiprompthub.com' }}
                        </small>

                    </div>


                    <!-- Settings -->
                    <a class="dropdown-item py-2 rounded"
                       href="{{ route('admin.settings.index') }}">

                        <i class="mdi mdi-cog-outline text-primary me-2"></i>
                        Settings

                    </a>


                    <div class="dropdown-divider"></div>


                    <!-- Logout -->
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf

                        <button type="submit"
                                class="dropdown-item py-2 text-danger rounded border-0 bg-transparent w-100 text-start">

                            <i class="mdi mdi-power text-danger me-2"></i>
                            Log Out

                        </button>
                    </form>

                </div>

            </li>

        </ul>


        <!-- Mobile Menu Toggle -->
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center"
                type="button"
                data-toggle="offcanvas">

            <span class="mdi mdi-menu"></span>

        </button>

    </div>

</nav>