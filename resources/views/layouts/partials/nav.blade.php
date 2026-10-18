
<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav" style="margin-top: 15px;">

        {{-- Dashboard --}}
        <li class="nav-item">
            <a
                class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                href="{{ route('admin.dashboard') }}"
            >
                <i class="mdi mdi-grid-large menu-icon"></i>
                <span class="menu-title">Dashboard</span>
            </a>
        </li>


        {{-- Prompts Dropdown --}}
        <li class="nav-item">

            @php
                $promptsMenuActive =
                    request()->routeIs('admin.categories.*') ||
                    request()->routeIs('admin.prompts.*');
            @endphp

            <a
                class="nav-link {{ $promptsMenuActive ? '' : 'collapsed' }}"
                id="categoryToggleBtn"
                href="javascript:void(0);"
                style="cursor: pointer;"
                aria-expanded="{{ $promptsMenuActive ? 'true' : 'false' }}"
            >
                <i class="menu-icon mdi mdi-folder-cog-outline"></i>
                <span class="menu-title">Prompts</span>
                <i class="menu-arrow mdi mdi-chevron-down ms-auto"></i>
            </a>

            <div
                class="collapse {{ $promptsMenuActive ? 'show' : '' }}"
                id="manageCategoryMenu"
                style="{{ $promptsMenuActive ? 'display: block;' : 'display: none;' }}"
            >
                <ul class="nav flex-column sub-menu">

                    {{-- All Categories --}}
                    <li class="nav-item">
                        <a
                            class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"
                            href="{{ route('admin.categories.index') }}"
                        >
                            <i class="mdi mdi-format-list-bulleted me-2"></i>
                            All Categories
                        </a>
                    </li>

                    {{-- All Prompts --}}
                    <li class="nav-item">
                        <a
                            class="nav-link {{ request()->routeIs('admin.prompts.*') ? 'active' : '' }}"
                            href="{{ route('admin.prompts.index') }}"
                        >
                            <i class="mdi mdi-format-list-bulleted me-2"></i>
                            All Prompts
                        </a>
                    </li>

                </ul>
            </div>
        </li>


        {{-- Settings Dropdown --}}
        <li class="nav-item">

            @php
                $settingsMenuActive =
                    request()->routeIs('admin.settings.*') ||
                    request()->routeIs('admin.commands.*');
            @endphp

            <a
                class="nav-link {{ $settingsMenuActive ? '' : 'collapsed' }}"
                id="settingsToggleBtn"
                href="javascript:void(0);"
                style="cursor: pointer;"
                aria-expanded="{{ $settingsMenuActive ? 'true' : 'false' }}"
            >
                <i class="menu-icon mdi mdi-cog-outline"></i>
                <span class="menu-title">Settings</span>
                <i class="menu-arrow mdi mdi-chevron-down ms-auto"></i>
            </a>

            <div
                class="collapse {{ $settingsMenuActive ? 'show' : '' }}"
                id="settingsSubMenu"
                style="{{ $settingsMenuActive ? 'display: block;' : 'display: none;' }}"
            >
                <ul class="nav flex-column sub-menu">

                    {{-- General Settings --}}
                    <li class="nav-item">
                        <a
                            class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"
                            href="{{ route('admin.settings.index') }}"
                        >
                            <i class="mdi mdi-tune-variant me-2"></i>
                            General Settings
                        </a>
                    </li>

                    {{-- Run SQL Command --}}
                    <li class="nav-item">
                        <a
                            class="nav-link {{ request()->routeIs('admin.commands.*') ? 'active' : '' }}"
                            href="{{ route('admin.commands.index') }}"
                        >
                            <i class="mdi mdi-database-cog-outline me-2"></i>
                            Run SQL Command
                        </a>
                    </li>

                </ul>
            </div>
        </li>


        {{-- Logout --}}
        <li class="nav-item">
            <a
                class="nav-link text-danger"
                href="{{ route('admin.logout') }}"
                onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();"
            >
                <i class="mdi mdi-power me-2 text-danger"></i>
                <span class="menu-title">Log-out</span>
            </a>
        </li>

    </ul>
</nav>


{{-- Hidden Logout Form --}}
<form
    id="logout-form-sidebar"
    action="{{ route('admin.logout') }}"
    method="POST"
    style="display: none;"
>
    @csrf
</form>


{{-- Sidebar Dropdown JavaScript --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Prompts Dropdown
    const promptsToggleBtn = document.getElementById('categoryToggleBtn');
    const promptsSubMenu = document.getElementById('manageCategoryMenu');

    if (promptsToggleBtn && promptsSubMenu) {

        promptsToggleBtn.addEventListener('click', function (e) {
            e.preventDefault();

            const isOpen = promptsSubMenu.style.display !== 'none';

            if (isOpen) {
                promptsSubMenu.style.display = 'none';
                promptsSubMenu.classList.remove('show');
                promptsToggleBtn.classList.add('collapsed');
                promptsToggleBtn.setAttribute('aria-expanded', 'false');
            } else {
                promptsSubMenu.style.display = 'block';
                promptsSubMenu.classList.add('show');
                promptsToggleBtn.classList.remove('collapsed');
                promptsToggleBtn.setAttribute('aria-expanded', 'true');
            }
        });
    }


    // Settings Dropdown
    const settingsToggleBtn = document.getElementById('settingsToggleBtn');
    const settingsSubMenu = document.getElementById('settingsSubMenu');

    if (settingsToggleBtn && settingsSubMenu) {

        settingsToggleBtn.addEventListener('click', function (e) {
            e.preventDefault();

            const isOpen = settingsSubMenu.style.display !== 'none';

            if (isOpen) {
                settingsSubMenu.style.display = 'none';
                settingsSubMenu.classList.remove('show');
                settingsToggleBtn.classList.add('collapsed');
                settingsToggleBtn.setAttribute('aria-expanded', 'false');
            } else {
                settingsSubMenu.style.display = 'block';
                settingsSubMenu.classList.add('show');
                settingsToggleBtn.classList.remove('collapsed');
                settingsToggleBtn.setAttribute('aria-expanded', 'true');
            }
        });
    }

});
</script>
