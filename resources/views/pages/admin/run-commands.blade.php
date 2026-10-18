{{-- resources/views/pages/admin/run-commands.blade.php --}}

@extends('layouts.backlayout')

@section('content')

@php
    $savedCommands = \App\Models\RunCommand::latest()->get();
@endphp

<div class="container-fluid py-4 run-command-page">

{{-- PAGE HEADER --}}
<div class="rc-page-header mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">

        <div class="d-flex align-items-center gap-3">
            <div class="rc-header-icon">
                <i class="mdi mdi-console"></i>
            </div>

            <div>
                <h3 class="rc-page-title mb-0">Run SQL Command</h3>
                <p class="rc-page-subtitle mb-0">
                    Manage, save and copy your frequently used SQL commands.
                </p>
            </div>
        </div>

        <button
            type="button"
            class="btn rc-add-btn"
            data-bs-toggle="modal"
            data-bs-target="#addCommandModal"
        >
            <i class="mdi mdi-plus me-1"></i>
            Add New Command
        </button>

    </div>
</div>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rc-alert" role="alert">
            <i class="mdi mdi-check-circle-outline me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rc-alert" role="alert">
            <i class="mdi mdi-alert-circle-outline me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- VALIDATION ERRORS --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rc-alert" role="alert">
            <strong>
                <i class="mdi mdi-alert-circle-outline me-1"></i>
                Please check the following:
            </strong>

            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- STATS CARD (QUICK TIP REMOVED) --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-5 col-xl-4">
            <div class="rc-stat-card">
                <div class="rc-stat-icon">
                    <i class="mdi mdi-database"></i>
                </div>

                <div>
                    <div class="rc-stat-label">Total Saved Commands</div>
                    <div class="rc-stat-number">{{ $savedCommands->count() }}</div>
                    <div class="rc-stat-description">Commands available in your list</div>
                </div>
            </div>
        </div>
    </div>

    {{-- COMMANDS LIST --}}
    <div class="rc-main-card">

        <div class="rc-card-header">
            <div>
                <h5 class="mb-1">
                    <i class="mdi mdi-format-list-bulleted me-2 text-primary"></i>
                    Saved Commands
                </h5>

                <p class="mb-0">
                    View and copy your saved commands.
                </p>
            </div>

            <span class="rc-count-badge">
                {{ $savedCommands->count() }} {{ $savedCommands->count() === 1 ? 'Command' : 'Commands' }}
            </span>
        </div>

        {{-- SEARCH --}}
        @if($savedCommands->isNotEmpty())
            <div class="rc-toolbar">
                <div class="rc-search-box">
                    <i class="mdi mdi-magnify"></i>

                    <input
                        type="search"
                        id="commandSearch"
                        class="form-control"
                        placeholder="Search by heading or SQL command..."
                        aria-label="Search saved commands"
                    >

                    <button
                        type="button"
                        id="clearCommandSearch"
                        class="rc-clear-search"
                        title="Clear search"
                        aria-label="Clear search"
                    >
                        <i class="mdi mdi-close"></i>
                    </button>
                </div>

                <span class="rc-search-result" id="commandSearchResult">
                    {{ $savedCommands->count() }} records
                </span>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table rc-table align-middle mb-0" id="commandsTable">

                <thead>
                    <tr>
                        <th class="rc-number-col">#</th>
                        <th class="rc-heading-col">Command Name</th>
                        <th>Command / SQL</th>
                        <th class="rc-action-col text-end">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($savedCommands as $key => $item)
                        <tr class="rc-command-row">

                            <td class="rc-row-number">
                                {{ $key + 1 }}
                            </td>

                            <td>
                                <div class="rc-command-title">
                                    <span class="rc-title-icon">
                                        <i class="mdi mdi-code-tags"></i>
                                    </span>

                                    <span class="rc-title-text">
                                        {{ $item->heading }}
                                    </span>
                                </div>
                            </td>

                            <td>
                                <code class="rc-code-block">{{ $item->command_sql }}</code>
                            </td>

                            <td class="text-end">
                                <button
                                    type="button"
                                    class="btn rc-copy-btn run-command-copy"
                                    data-command="{{ $item->command_sql }}"
                                    title="Copy command"
                                >
                                    <i class="mdi mdi-content-copy me-1"></i>
                                    <span>Copy</span>
                                </button>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-5">
                                <div class="rc-empty-state">
                                    <div class="rc-empty-icon">
                                        <i class="mdi mdi-code-greater-than-or-equal"></i>
                                    </div>

                                    <h5>No Commands Saved Yet</h5>

                                    <p>
                                        Your saved commands will appear here.
                                        Add your first command to get started.
                                    </p>

                                    <button
                                        type="button"
                                        class="btn rc-add-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#addCommandModal"
                                    >
                                        <i class="mdi mdi-plus me-1"></i>
                                        Add First Command
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse

                    @if($savedCommands->isNotEmpty())
                        <tr id="noSearchResults" style="display:none;">
                            <td colspan="4" class="text-center py-5">
                                <i class="mdi mdi-magnify rc-no-result-icon"></i>
                                <h6 class="fw-bold mt-2">No Matching Commands</h6>
                                <p class="text-muted mb-0">
                                    Try searching with another heading or SQL keyword.
                                </p>
                            </td>
                        </tr>
                    @endif
                </tbody>

            </table>
        </div>

       
    </div>

</div>


{{-- ADD COMMAND MODAL --}}
<div
    class="modal fade rc-modal"
    id="addCommandModal"
    tabindex="-1"
    aria-labelledby="addCommandModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">

            <form action="{{ route('admin.commands.store') }}" method="POST">
                @csrf

                {{-- MODAL HEADER FIXED --}}
                <div class="modal-header border-bottom px-4 py-3 align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rc-modal-icon">
                            <i class="mdi mdi-console"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="addCommandModalLabel">
                                Add New Command
                            </h5>
                            <p class="mb-0 text-muted small">Save a command for future reference.</p>
                        </div>
                    </div>
                    
                    {{-- FIXED CROSS BUTTON --}}
                    <button
                        type="button"
                        class="rc-close-btn"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    >&times;</button>
                </div>

                {{-- MODAL BODY --}}
                <div class="modal-body p-4">

                    <div class="rc-form-section mb-3">
                        <label for="command_heading" class="form-label">
                            Command Heading <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            id="command_heading"
                            name="heading"
                            class="form-control @error('heading') is-invalid @enderror"
                            placeholder="e.g. Update User Password"
                            maxlength="255"
                            value="{{ old('heading') }}"
                            required
                        >

                        @error('heading')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <div class="form-text">
                            Choose a short and recognizable name.
                        </div>
                    </div>

                    <div class="rc-form-section mb-0">
                        <label for="command_sql" class="form-label">
                            Command / SQL Text <span class="text-danger">*</span>
                        </label>

                        <textarea
                            id="command_sql"
                            name="command_sql"
                            class="form-control rc-code-input @error('command_sql') is-invalid @enderror"
                            rows="5"
                            placeholder="Enter the command or SQL query..."
                            required
                        >{{ old('command_sql') }}</textarea>

                        @error('command_sql')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                       
                    </div>

                </div>

                {{-- MODAL FOOTER --}}
                <div class="modal-footer bg-light px-4 py-3 border-top">
                    <button
                        type="button"
                        class="btn rc-cancel-btn border"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button type="submit" class="btn rc-add-btn">
                        <i class="mdi mdi-content-save-outline me-1"></i>
                        Save Command
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>


{{-- PAGE STYLES --}}
<style>
.run-command-page {
    color: #253047;
}

.rc-page-header {
    padding: 18px 22px;
    background: #fff;
    border: 1px solid #e8edf4;
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(15, 23, 42, .035);
}


.rc-header-icon {
    width: 30px !important;
    height: 30px !important;
    min-width: 30px !important;
    min-height: 30px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    flex-shrink: 0 !important;
    color: #fff;
    background: linear-gradient(135deg, #2563eb, #6366f1);
    border-radius: 8px;
    font-size: 18px;
    line-height: 1;
}

.rc-page-title {
    color: #172033;
    font-size: 18px;
    font-weight: 700;
    line-height: 1.2;
}

.rc-page-subtitle {
    color: #7a8497;
    font-size: 12px;
    margin-top: 2px;
}

.rc-add-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 9px 16px;
    color: #fff;
    background: #2563eb;
    border: 1px solid #2563eb;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
}

.rc-add-btn:hover,
.rc-add-btn:focus {
    color: #fff;
    background: #1d4ed8;
    border-color: #1d4ed8;
}

.rc-stat-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 18px 20px;
    background: #fff;
    border: 1px solid #e8edf4;
    border-radius: 12px;
}

.rc-stat-icon {
    width: 44px !important;
    height: 44px !important;
    min-width: 44px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 10px;
    font-size: 22px;
    color: #2563eb;
    background: #eff6ff;
    line-height: 1;
}

.rc-stat-label {
    margin-bottom: 2px;
    color: #758096;
    font-size: 12px;
    font-weight: 600;
}

.rc-stat-number {
    color: #172033;
    font-size: 24px;
    font-weight: 750;
    line-height: 1.2;
}

.rc-stat-description {
    color: #8b95a7;
    font-size: 11px;
}

.rc-main-card {
    overflow: hidden;
    background: #fff;
    border: 1px solid #e8edf4;
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(15, 23, 42, .035);
}

.rc-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 20px;
    border-bottom: 1px solid #edf0f5;
}

.rc-card-header h5 {
    color: #202b40;
    font-size: 15px;
    font-weight: 750;
}

.rc-count-badge {
    padding: 5px 11px;
    color: #1d4ed8;
    background: #eff6ff;
    border: 1px solid #dbeafe;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
}

.rc-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 20px;
    background: #fcfdff;
    border-bottom: 1px solid #edf0f5;
}

.rc-search-box {
    position: relative;
    display: flex;
    align-items: center;
    width: min(100%, 380px);
}

.rc-search-box > i {
    position: absolute;
    left: 12px;
    color: #94a3b8;
    font-size: 18px;
    pointer-events: none;
}

.rc-search-box .form-control {
    min-height: 38px;
    padding: 8px 36px 8px 38px;
    background: #fff;
    border: 1px solid #e0e6ef;
    border-radius: 8px;
    font-size: 12px;
}

.rc-table thead th {
    padding: 12px 16px;
    color: #7b879b;
    background: #f8fafc;
    border-bottom: 1px solid #e8edf4;
    font-size: 11px;
    font-weight: 750;
    text-transform: uppercase;
}

.rc-table tbody td {
    padding: 14px 16px;
    border-bottom: 1px solid #f0f2f6;
    vertical-align: middle;
}

.rc-command-title {
    display: flex;
    align-items: center;
    gap: 10px;
}

.rc-title-icon {
    width: 32px !important;
    height: 32px !important;
    min-width: 32px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    color: #2563eb;
    background: #eff6ff;
    border-radius: 6px;
    font-size: 16px;
    line-height: 1;
}

.rc-title-text {
    color: #334155;
    font-size: 13px;
    font-weight: 700;
}

.rc-code-block {
    display: block;
    padding: 8px 12px;
    color: #be3455;
    background: #fff7f8;
    border: 1px solid #f9e2e7;
    border-radius: 6px;
    font-family: Consolas, "Courier New", monospace;
    font-size: 12px;
}

.rc-copy-btn {
    padding: 6px 12px;
    color: #2563eb;
    background: #fff;
    border: 1px solid #dbe6fb;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
}

.rc-copy-btn:hover {
    color: #fff;
    background: #2563eb;
    border-color: #2563eb;
}

/* MODAL FIX - HEADER & CLOSE BUTTON */
.rc-modal .modal-dialog {
    max-width: 520px;
    margin: 1.75rem auto;
}

.rc-modal .modal-content {
    border-radius: 12px;
    overflow: hidden;
}

.rc-modal-icon {
    width: 40px !important;
    height: 40px !important;
    min-width: 40px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    color: #fff;
    background: linear-gradient(135deg, #2563eb, #6366f1);
    border-radius: 8px;
    font-size: 20px;
    line-height: 1;
}

/* CUSTOM CLEAN CLOSE BUTTON */
.rc-close-btn {
    background: transparent;
    border: 0;
    font-size: 24px;
    line-height: 1;
    color: #64748b;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: background .2s ease;
}

.rc-close-btn:hover {
    background: #f1f5f9;
    color: #0f172a;
}

.rc-form-section .form-label {
    display: block;
    margin-bottom: 6px;
    color: #334155;
    font-size: 13px;
    font-weight: 700;
}

.rc-form-section .form-control {
    min-height: 40px;
    padding: 8px 12px;
    border: 1px solid #dfe5ee;
    border-radius: 6px;
    font-size: 13px;
}

.rc-form-section .rc-code-input {
    min-height: 120px;
    background: #f8fafc;
    font-family: Consolas, "Courier New", monospace;
    font-size: 13px;
}

.rc-cancel-btn {
    padding: 7px 14px;
    color: #475569;
    background: #fff;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
}

@media (max-width: 767px) {
    .rc-modal .modal-dialog {
        margin: 12px;
    }
}
</style>


{{-- PAGE SCRIPT --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* COPY COMMAND */
    document.querySelectorAll('.run-command-copy').forEach(function (button) {
        button.addEventListener('click', async function () {
            const command = this.getAttribute('data-command') || '';
            const originalHTML = this.innerHTML;
            const buttonElement = this;

            try {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(command);
                } else {
                    const textarea = document.createElement('textarea');
                    textarea.value = command;
                    textarea.style.position = 'fixed';
                    textarea.style.left = '-9999px';
                    document.body.appendChild(textarea);
                    textarea.select();
                    const copied = document.execCommand('copy');
                    textarea.remove();
                    if (!copied) throw new Error('Copy failed');
                }

                buttonElement.innerHTML = '<i class="mdi mdi-check me-1"></i><span>Copied</span>';
                buttonElement.classList.add('copied');

                window.setTimeout(function () {
                    buttonElement.innerHTML = originalHTML;
                    buttonElement.classList.remove('copied');
                }, 1500);

            } catch (error) {
                alert('Unable to copy the command. Please copy it manually.');
            }
        });
    });

    /* SEARCH COMMANDS */
    const searchInput = document.getElementById('commandSearch');
    const clearSearchButton = document.getElementById('clearCommandSearch');
    const searchResult = document.getElementById('commandSearchResult');
    const noResultsRow = document.getElementById('noSearchResults');

    if (searchInput) {
        const commandRows = Array.from(document.querySelectorAll('.rc-command-row'));

        function filterCommands() {
            const query = searchInput.value.trim().toLowerCase();
            let visibleCount = 0;

            commandRows.forEach(function (row) {
                const matches = row.textContent.toLowerCase().includes(query);
                row.style.display = matches ? '' : 'none';
                if (matches) visibleCount++;
            });

            if (noResultsRow) {
                noResultsRow.style.display = visibleCount === 0 ? '' : 'none';
            }

            if (searchResult) {
                searchResult.textContent = visibleCount + (visibleCount === 1 ? ' record' : ' records');
            }

            if (clearSearchButton) {
                clearSearchButton.classList.toggle('visible', searchInput.value.length > 0);
            }
        }

        searchInput.addEventListener('input', filterCommands);

        if (clearSearchButton) {
            clearSearchButton.addEventListener('click', function () {
                searchInput.value = '';
                filterCommands();
                searchInput.focus();
            });
        }
    }

    /* REOPEN MODAL IF VALIDATION FAILS */
    @if($errors->has('heading') || $errors->has('command_sql'))
        const commandModalElement = document.getElementById('addCommandModal');
        if (commandModalElement && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(commandModalElement).show();
        }
    @endif

});
</script>

@endsection