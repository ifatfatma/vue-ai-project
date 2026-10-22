<nav class="navbar navbar-expand-lg modern-navbar">
        <div class="container-fluid px-3 px-lg-4">
            <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                @if(auth('frontend')->check() && auth('frontend')->user()->profile_image)
                    <img src="{{ asset(auth('frontend')->user()->profile_image) }}"
                        alt="{{ auth('frontend')->user()->name }}" class="rounded-circle me-2" width="36" height="36"
                        style="width:36px;height:36px;object-fit:cover;border:2px solid #8b5cf6;">
                @else
                    <span class="brand-icon"><i class="bi bi-stars"></i></span>
                @endif
                <span>AI Prompt Hub</span>
            </a>

            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center flex-row gap-2">
                    <li class="nav-item">
                        <button type="button" class="btn add-prompt-btn btn-sm fw-bold px-3 py-2" data-bs-toggle="modal"
                            data-bs-target="#addPromptModal">
                            <i class="bi bi-plus-lg me-1"></i> Add Prompt
                        </button>
                    </li>

                   
                    @auth('frontend')
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle navbar-account d-flex align-items-center" href="#"
                                id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                @if(auth('frontend')->user()->profile_image)
                                    <img src="{{ asset(auth('frontend')->user()->profile_image) }}"
                                        alt="{{ auth('frontend')->user()->name }}" class="rounded-circle me-2" width="36"
                                        height="36" style="width:36px;height:36px;object-fit:cover;border:2px solid #8b5cf6;">
                                @else
                                    <i class="bi bi-person-circle fs-5 me-1"></i>
                                @endif
                                <span>My Account</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 rounded-3"
                                aria-labelledby="profileDropdown">
                                <li>
                                    <a class="dropdown-item py-2 d-flex align-items-center"
                                        href="{{ route('user.profile.edit') }}">
                                        @if(auth('frontend')->user()->profile_image)
                                            <img src="{{ asset(auth('frontend')->user()->profile_image) }}" alt="Profile"
                                                class="rounded-circle me-2" width="28" height="28"
                                                style="width:28px;height:28px;object-fit:cover;">
                                        @else
                                            <i class="bi bi-person-circle me-2 text-muted"></i>
                                        @endif
                                        My Profile
                                    </a>
                                </li>
                                <li><a class="dropdown-item py-2 px-3" href="{{ route('user.prompts') }}"><i
                                            class="bi bi-collection me-2"></i>My Prompts</a></li>
                                <li><a class="dropdown-item py-2 px-3" href="{{ route('user.saved-prompts') }}"><i
                                            class="bi bi-bookmark-fill me-2 text-primary"></i>Saved Prompts</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <form action="{{ route('frontend.logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item py-2 text-danger fw-semibold"><i
                                                class="bi bi-box-arrow-right me-2"></i>Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        {{-- Point to frontend login route --}}
                        <li class="nav-item"><a href="{{ route('frontend.login') }}" class="nav-link navbar-account"><i
                                    class="bi bi-box-arrow-in-right me-1"></i> Login</a></li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>
