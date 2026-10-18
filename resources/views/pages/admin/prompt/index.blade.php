@extends('layouts.backlayout')

@section('content')

<div class="content-wrapper prompt-management-page" style="padding-top: 60px !important;">

    {{-- =========================
        PAGE HEADER
    ========================== --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3 class="page-title font-weight-bold text-dark mb-1">
                Prompts Management
            </h3>

            <p class="text-muted mb-0">
                All AI Prompts
            </p>
        </div>

        <a href="{{ route('admin.prompts.create') }}"
           class="btn btn-primary fw-bold">

            + Add New Prompt

        </a>

    </div>


    {{-- =========================
        SUCCESS MESSAGE
    ========================== --}}
    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show"
             role="alert">

            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close">
            </button>

        </div>

    @endif


    {{-- =========================
        FILTER FORM
    ========================== --}}
    <form method="GET"
          action="{{ route('admin.prompts.index') }}"
          class="mb-4 row g-3 align-items-center">

        {{-- User Filter --}}
        <div class="col-md-3 col-sm-6">

            <select name="user_id"
                    class="form-select select2-user"
                    onchange="this.form.submit()">

                <option value="">
                    All Users
                </option>

                @foreach($users as $user)

                    <option value="{{ $user->id }}"
                        {{ request('user_id') == $user->id ? 'selected' : '' }}>

                        {{ $user->name ?? $user->email }}

                    </option>

                @endforeach

            </select>

        </div>


        {{-- Date Filter --}}
        <div class="col-md-2 col-sm-6">

            <input type="date"
                   name="date"
                   value="{{ request('date') }}"
                   class="form-control"
                   onchange="this.form.submit()">

        </div>


        {{-- Reset --}}
        @if(request('user_id') || request('date'))

            <div class="col-auto">

                <a href="{{ route('admin.prompts.index') }}"
                   class="btn btn-outline-secondary btn-sm">

                    Reset Filters

                </a>

            </div>

        @endif

    </form>


    {{-- =========================
        CUSTOM CSS
    ========================== --}}
    <style>

        /* =========================================
           MAIN PAGE
        ========================================= */

        .prompt-management-page {
            overflow: visible !important;
        }


        /* =========================================
           CARD FIX
        ========================================= */

        .prompt-table-card {
            overflow: visible !important;
            position: relative !important;
        }

        .prompt-table-card .card-body {
            overflow: visible !important;
            position: relative !important;
        }


        /* =========================================
           TABLE
        ========================================= */

        .prompts-table {
            width: 100%;
            margin-bottom: 0;
        }

        .prompts-table td {
            white-space: normal !important;
            word-break: break-word !important;
            vertical-align: middle;
        }

        .prompts-table th {
            vertical-align: middle;
            white-space: nowrap;
        }


        .prompts-table .badge {
            max-width: 180px;
            white-space: normal !important;
            text-align: left;
            height: auto;
            display: inline-block;
        }


        /* =========================================
           TABLE RESPONSIVE
           
           IMPORTANT:
           Desktop par overflow visible rakha hai
           taaki dropdown clip na ho.
        ========================================= */

        .prompt-table-responsive {
            overflow: visible !important;
            position: relative !important;
        }


        /* =========================================
           ACTION COLUMN
        ========================================= */

        .prompt-actions {
            white-space: nowrap !important;
            position: relative !important;
            overflow: visible !important;
            z-index: 1000 !important;
        }


        /* =========================================
           ACTION DROPDOWN
        ========================================= */

        .prompt-actions .dropdown {
            position: relative !important;
        }


        .prompt-actions .dropdown-menu {
            min-width: 190px !important;

            width: auto !important;

            max-height: none !important;

            height: auto !important;

            overflow: visible !important;

            position: absolute !important;

            z-index: 999999 !important;

            border: 0 !important;

            box-shadow:
                0 8px 25px rgba(0, 0, 0, 0.15) !important;

            border-radius: 10px !important;

            padding-top: 6px !important;

            padding-bottom: 6px !important;
        }


        /* Dropdown open state */

        .prompt-actions .dropdown-menu.show {
            display: block !important;

            max-height: none !important;

            overflow: visible !important;

            z-index: 999999 !important;
        }


        /* =========================================
           ACTION ITEMS
        ========================================= */

        .prompt-actions .dropdown-item {
            white-space: nowrap !important;

            overflow: visible !important;

            min-height: 38px;

            display: flex !important;

            align-items: center !important;
        }


        .prompt-actions .dropdown-item:hover {
            background-color: #f8f9fa;
        }


        /* =========================================
           ACTION BUTTON
        ========================================= */

        .prompt-actions .dropdown-toggle {
            white-space: nowrap !important;
        }


        /* =========================================
           PROMPT TEXT
        ========================================= */

        .prompt-text {
            white-space: pre-wrap;
            word-break: break-word;
            font-family: monospace;
            margin: 0;
        }


        /* =========================================
           PROMPT IMAGE
        ========================================= */

        .prompt-image {
            max-height: 250px;
            max-width: 100%;
            object-fit: contain;
        }


        /* =========================================
           MODAL
        ========================================= */

        .modal {
            z-index: 1000000 !important;
        }

        .modal-backdrop {
            z-index: 999999 !important;
        }


        /* =========================================
           MOBILE
        ========================================= */

        @media (max-width: 767px) {

            .prompt-table-responsive {
                overflow-x: auto !important;
                overflow-y: visible !important;
            }

            .prompts-table {
                min-width: 900px;
            }

            .prompt-actions {
                overflow: visible !important;
            }

            .prompt-actions .dropdown-menu {
                min-width: 180px !important;
            }

        }

    </style>


    {{-- =========================
        PROMPTS TABLE
    ========================== --}}
    <div class="row">

        <div class="col-12 grid-margin stretch-card">

            <div class="card border-0 shadow-sm rounded-3 prompt-table-card">

                <div class="card-body">

                    <div class="table-responsive prompt-table-responsive">

                        <table class="table table-hover align-middle prompts-table">

                            <thead>

                                <tr>

                                    <th>#</th>

                                    <th>Title</th>

                                    <th>Category</th>

                                    <th>Created By</th>

                                    <th>Label</th>

                                    <th>Status</th>

                                    <th>Copies</th>

                                    <th class="text-center">
                                        Actions
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                            @forelse($prompts as $key => $prompt)

                                {{-- =========================
                                    STATUS
                                ========================== --}}
                                @php

                                    $status = $prompt->status ?? null;

                                    if ($status === null) {
                                        $status = $prompt->is_approved ?? 0;
                                    }

                                    if ($status === 'approved' || $status == 1) {

                                        $statusText = 'approved';

                                    } elseif ($status === 'rejected' || $status == 2) {

                                        $statusText = 'rejected';

                                    } else {

                                        $statusText = 'pending';

                                    }

                                @endphp


                                <tr>

                                    {{-- NUMBER --}}
                                    <td>

                                        {{ ($prompts->currentPage() - 1)
                                            * $prompts->perPage()
                                            + $key + 1 }}

                                    </td>


                                    {{-- TITLE --}}
                                    <td class="fw-bold">

                                        {{ $prompt->title }}

                                    </td>


                                    {{-- CATEGORY --}}
                                    <td>

                                        <span class="badge px-2 py-1"
                                              style="
                                                background-color: #eef2ff;
                                                color: #4f46e5;
                                                border-radius: 6px;
                                              ">

                                            {{ $prompt->category->name ?? 'N/A' }}

                                        </span>

                                    </td>


                                    {{-- CREATED BY --}}
                                    <td>

                                        <span class="text-dark fw-medium">

                                            {{ $prompt->user->name
                                                ?? $prompt->user->email
                                                ?? 'N/A' }}

                                        </span>

                                    </td>


                                    {{-- LABEL --}}
                                    <td>

                                        @if(!empty($prompt->label))

                                            <span class="badge bg-secondary">

                                                {{ $prompt->label }}

                                            </span>

                                        @else

                                            <span class="text-muted">
                                                -
                                            </span>

                                        @endif

                                    </td>


                                    {{-- STATUS --}}
                                    <td>

                                        @if($statusText === 'approved')

                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">

                                                Approved

                                            </span>

                                        @elseif($statusText === 'rejected')

                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">

                                                Rejected

                                            </span>

                                        @else

                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">

                                                Pending

                                            </span>

                                        @endif

                                    </td>


                                    {{-- COPIES --}}
                                    <td>

                                        <span class="badge bg-light text-dark border">

                                            {{ $prompt->copies_count ?? 0 }}

                                        </span>

                                    </td>


                                    {{-- =========================
                                        ACTIONS
                                    ========================== --}}
                                    <td class="text-center prompt-actions">

                                        <div class="dropdown">

                                            <button
                                                type="button"
                                                class="btn btn-light btn-sm border px-3 py-1 rounded-pill shadow-sm dropdown-toggle d-inline-flex align-items-center"
                                                data-bs-toggle="dropdown"
                                                data-bs-boundary="viewport"
                                                data-bs-offset="0,8"
                                                aria-expanded="false">

                                                <i class="bi bi-three-dots-vertical me-1 text-secondary"></i>

                                                Actions

                                            </button>


                                            <ul
                                                class="dropdown-menu dropdown-menu-end shadow border-0 py-2 rounded-3"
                                                style="min-width: 190px;">

                                                {{-- APPROVE --}}
                                                <li>

                                                    <form
                                                        action="{{ route('admin.prompts.approve', $prompt->id) }}"
                                                        method="POST">

                                                        @csrf

                                                        <button
                                                            type="submit"
                                                            class="dropdown-item py-2 px-3 text-success d-flex align-items-center"
                                                            style="font-size: 13px; font-weight: 500;">

                                                            <i class="bi bi-check-circle-fill me-2"></i>

                                                            Approve

                                                        </button>

                                                    </form>

                                                </li>


                                                {{-- REJECT --}}
                                                <li>

                                                    <form
                                                        action="{{ route('admin.prompts.reject', $prompt->id) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Reject this prompt?');">

                                                        @csrf

                                                        <button
                                                            type="submit"
                                                            class="dropdown-item py-2 px-3 text-warning d-flex align-items-center"
                                                            style="font-size: 13px; font-weight: 500;">

                                                            <i class="bi bi-x-circle-fill me-2"></i>

                                                            Reject

                                                        </button>

                                                    </form>

                                                </li>


                                                <li>

                                                    <hr class="dropdown-divider my-1">

                                                </li>


                                                {{-- VIEW --}}
                                                <li>

                                                    <button
                                                        type="button"
                                                        class="dropdown-item py-2 px-3 text-dark d-flex align-items-center"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#viewModal{{ $prompt->id }}">

                                                        <i class="bi bi-eye-fill me-2 text-info"></i>

                                                        View

                                                    </button>

                                                </li>


                                                {{-- EDIT --}}
                                                <li>

                                                    <a
                                                        href="{{ route('admin.prompts.edit', $prompt->id) }}"
                                                        class="dropdown-item py-2 px-3 text-dark d-flex align-items-center"
                                                        style="font-size: 13px;">

                                                        <i class="bi bi-pencil-square me-2 text-primary"></i>

                                                        Edit

                                                    </a>

                                                </li>


                                                <li>

                                                    <hr class="dropdown-divider my-1">

                                                </li>


                                                {{-- DELETE --}}
                                                <li>

                                                    <form
                                                        action="{{ route('admin.prompts.destroy', $prompt->id) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Delete this prompt?');">

                                                        @csrf

                                                        @method('DELETE')

                                                        <button
                                                            type="submit"
                                                            class="dropdown-item py-2 px-3 text-danger d-flex align-items-center"
                                                            style="font-size: 13px;">

                                                            <i class="bi bi-trash-fill me-2"></i>

                                                            Delete

                                                        </button>

                                                    </form>

                                                </li>

                                            </ul>

                                        </div>

                                    </td>

                                </tr>


                            @empty

                                <tr>

                                    <td colspan="8"
                                        class="text-center py-4 text-muted">

                                        No prompts found.

                                    </td>

                                </tr>

                            @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================
        VIEW MODALS
    ========================== --}}
    @foreach($prompts as $prompt)

        <div
            class="modal fade"
            id="viewModal{{ $prompt->id }}"
            tabindex="-1"
            aria-labelledby="viewModalLabel{{ $prompt->id }}"
            aria-hidden="true">

            <div class="modal-dialog modal-dialog-centered modal-lg">

                <div class="modal-content border-0 shadow rounded-4">


                    {{-- MODAL HEADER --}}
                    <div class="modal-header">

                        <h5
                            class="modal-title fw-bold"
                            id="viewModalLabel{{ $prompt->id }}">

                            {{ $prompt->title }}

                        </h5>


                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close">
                        </button>

                    </div>


                    {{-- MODAL BODY --}}
                    <div class="modal-body">

                        {{-- CATEGORY / LABEL / USER --}}
                        <div class="mb-3">

                            <span class="badge bg-primary">

                                {{ $prompt->category->name ?? 'N/A' }}

                            </span>


                            @if(!empty($prompt->label))

                                <span class="badge bg-secondary ms-1">

                                    {{ $prompt->label }}

                                </span>

                            @endif


                            <span class="badge bg-light text-dark border ms-1">

                                By:

                                {{ $prompt->user->name
                                    ?? $prompt->user->email
                                    ?? 'Admin' }}

                            </span>

                        </div>


                        {{-- IMAGE --}}
                        @if(!empty($prompt->image))

                            <div class="text-center mb-3">

                                <img
                                    src="{{ asset('storage/' . $prompt->image) }}"
                                    alt="{{ $prompt->title }}"
                                    class="img-fluid rounded border prompt-image">

                            </div>

                        @endif


                        {{-- PROMPT TEXT --}}
                        <div class="mb-2">

                            <label class="fw-bold text-muted small">

                                PROMPT TEXT:

                            </label>

                        </div>


                        <div class="p-3 bg-light rounded border text-dark">

                            <pre class="prompt-text">{{ $prompt->prompt_text }}</pre>

                        </div>

                    </div>


                    {{-- MODAL FOOTER --}}
                    <div class="modal-footer">

                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">

                            Close

                        </button>

                    </div>

                </div>

            </div>

        </div>

    @endforeach


    {{-- =========================
        PAGINATION
    ========================== --}}
    <div class="d-flex justify-content-center mt-3">

        {{ $prompts->links() }}

    </div>

</div>


{{-- =========================
    DROPDOWN FIX JAVASCRIPT
========================== --}}
<script>

document.addEventListener('DOMContentLoaded', function () {

    /*
     * Bootstrap dropdown ko body ke andar move kar dete hain
     * taaki table/card ke overflow ki wajah se clip na ho.
     */

    document.querySelectorAll('.prompt-actions .dropdown-toggle')
        .forEach(function (button) {

            button.addEventListener('shown.bs.dropdown', function () {

                const dropdown = button.closest('.dropdown');

                const menu = dropdown
                    ? dropdown.querySelector('.dropdown-menu')
                    : null;

                if (!menu) {
                    return;
                }

                /*
                 * Menu ko body me move karo
                 */
                if (menu.parentElement !== document.body) {

                    document.body.appendChild(menu);

                }

                /*
                 * Button ki screen position
                 */
                const rect =
                    button.getBoundingClientRect();


                /*
                 * Dropdown ki width
                 */
                const menuWidth =
                    menu.offsetWidth || 190;


                /*
                 * Right aligned position
                 */
                let left =
                    rect.right - menuWidth;


                /*
                 * Screen ke left se bahar na jaye
                 */
                if (left < 10) {

                    left = 10;

                }


                /*
                 * Screen ke right se bahar na jaye
                 */
                if (
                    left + menuWidth >
                    window.innerWidth - 10
                ) {

                    left =
                        window.innerWidth -
                        menuWidth -
                        10;

                }


                /*
                 * Vertical position
                 */
                let top =
                    rect.bottom + 8;


                /*
                 * Agar neeche space kam ho
                 * to button ke upar dropdown kholo
                 */
                const menuHeight =
                    menu.offsetHeight || 250;


                if (
                    top + menuHeight >
                    window.innerHeight - 10
                ) {

                    top =
                        rect.top -
                        menuHeight -
                        8;

                }


                /*
                 * Final position
                 */
                menu.style.position = 'fixed';

                menu.style.left =
                    left + 'px';

                menu.style.top =
                    top + 'px';

                menu.style.margin = '0';

                menu.style.zIndex = '9999999';

            });


            button.addEventListener('hidden.bs.dropdown', function () {

                const dropdown =
                    button.closest('.dropdown');

                const menu =
                    document.querySelector(
                        'body > .dropdown-menu'
                    );

                if (!menu || !dropdown) {
                    return;
                }

                /*
                 * Menu ko original dropdown me wapas rakho
                 */
                dropdown.appendChild(menu);

                menu.style.position = '';

                menu.style.left = '';

                menu.style.top = '';

                menu.style.margin = '';

                menu.style.zIndex = '';

            });

        });

});

</script>

@endsection