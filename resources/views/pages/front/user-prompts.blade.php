<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>My Prompts - AI Prompt Hub</title>

    {{-- Website Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>

        .navbar-brand img { max-height: 40px; width: auto; object-fit: contain; }

        .hero-section { 

            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%); 

            color: white; 

            padding: 40px 0; 

        }

    </style>

</head>

<body class="bg-light d-flex flex-column min-vh-100">



    <!-- Navbar Header -->

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 shadow-sm">

        <div class="container-fluid">

            <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">

                <img src="{{ asset('images/logo.png') }}"
                     alt="AI Prompt Hub Logo"
                     class="me-2"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
                <span class="fw-bold" style="display:none;">AI Prompt Hub</span>

            </a>

            <div class="ms-auto">

                <a href="{{ route('home') }}" class="btn btn-outline-light btn-sm fw-bold">

                    <i class="bi bi-arrow-left me-1"></i> Back to Home

                </a>

            </div>

        </div>

    </nav>



    <!-- Hero Header & Search Bar with Auto-suggestion -->

    <div class="hero-section text-center mb-4">

        <div class="container">

            <h1 class="fw-bold display-6 mb-2">My Submitted Prompts Lists</h1>

            <p class="lead text-muted mb-4" style="font-size: 1rem;">Manage your custom prompts, track their approval status, and update them.</p>



            <!-- Search Bar Form -->

            <div class="row justify-content-center position-relative">

                <div class="col-md-6">

                    <form action="{{ route('user.prompts') }}" method="GET" class="input-group shadow-sm">

                        <input type="text" id="searchBox" name="search" class="form-control border-0 py-2" placeholder="Search your prompts by title..." value="{{ request('search') }}" autocomplete="off">

                        <button class="btn btn-primary px-4" type="submit">

                            <i class="bi bi-search"></i> Search

                        </button>

                        @if(request('search'))

                            <a href="{{ route('user.prompts') }}" class="btn btn-secondary px-3 d-flex align-items-center">

                                <i class="bi bi-x-lg"></i>

                            </a>

                        @endif

                    </form>

                    <!-- Suggestions Dropdown Box -->

                    <ul id="suggestionList" class="list-group position-absolute w-100 shadow-sm mt-1 text-start" style="z-index: 1000; display: none;"></ul>

                </div>

            </div>

            <!-- Search Bar End -->



        </div>

    </div>



    <div class="container mb-5 flex-grow-1">

        @if(session('success'))

            <div id="successAlert" class="alert alert-success alert-dismissible fade show" role="alert">

                {{ session('success') }}

                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>

            </div>

        @endif



        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-dark">

                            <tr>

                                <th class="py-3 ps-4">#</th>

                                <th class="py-3">Title</th>

                                <th class="py-3">Category</th>

                                <th class="py-3">Status</th>

                                <th class="py-3">Ratings</th>

                                <th class="py-3">Created Date</th>

                                <th class="py-3 text-end pe-4">Actions</th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($prompts as $key =>$prompt)

                                <tr>

                                    <td class="ps-4 fw-semibold">{{ ($prompts->currentPage() - 1) * $prompts->perPage() +$key + 1 }}</td>

                                    <td>

                                        <span class="fw-bold text-dark">{{ $prompt->title }}</span>

                                    </td>

                                    <td>

                                        <span class="badge bg-secondary">{{ $prompt->category->name ?? 'General' }}</span>

                                    </td>

                                    <td>

                                        @if($prompt->status == 'approved')

                                            <span class="badge bg-success px-2 py-1">Approved</span>

                                        @elseif($prompt->status == 'pending')

                                            <span class="badge bg-warning text-dark px-2 py-1">Pending Approval</span>

                                        @else

                                            <span class="badge bg-danger px-2 py-1">Rejected</span>

                                        @endif

                                    </td>

                                    <!-- Rating Stars Column -->

                                    <td>

                                        <div class="rating-stars" title="Popularity Rating">

                                            {!! generateRatings($prompt->rating) !!}

                                        </div>

                                    </td>

                                    <td class="text-muted small">{{ $prompt->created_at->format('d M, Y') }}</td>

                                    <td class="text-end pe-4">

                                        <a href="{{ route('user.prompts.edit', $prompt->id) }}" class="btn btn-sm btn-outline-primary me-1">

                                            <i class="bi bi-pencil"></i> Edit

                                        </a>

                                        <form action="{{ route('user.prompts.destroy', $prompt->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to move this prompt to trash?')">

                                            @csrf

                                            @method('DELETE')

                                            <button type="submit" class="btn btn-sm btn-outline-danger">

                                                <i class="bi bi-trash"></i> Delete

                                            </button>

                                        </form>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="7" class="text-center py-5 text-muted">

                                        <i class="bi bi-folder2-open display-6 d-block mb-2 text-secondary"></i>

                                        You haven't submitted any prompts yet.

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>



        <!-- Pagination Links -->

        <div class="d-flex justify-content-center mt-4">

            {{ $prompts->links() }}

        </div>

    </div>



    <!-- Footer -->

    <div class="container-fluid px-0 mt-auto">

        <footer class="text-center text-lg-start text-white" style="background-color: #1c2331">

            <div class="text-center p-3 small" style="background-color: rgba(0, 0, 0, 0.2)">

                © {{ date('Y') }} Copyright: <a class="text-white fw-bold text-decoration-none" href="{{ route('home') }}">AI Prompt Hub</a>. All rights reserved.

            </div>

        </footer>

    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Automatically hide success messages after 1 second.
        document.addEventListener('DOMContentLoaded', function () {
            const successAlert = document.getElementById('successAlert');

            if (successAlert) {
                window.setTimeout(function () {
                    const alertInstance = bootstrap.Alert.getOrCreateInstance(successAlert);
                    alertInstance.close();
                }, 1000);
            }
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>

    $(document).ready(function() {$('#searchBox').on('keyup', function() {

            let query = $(this).val();



            if (query.length > 1) {

                $.ajax({

                    url: "{{ route('user.prompts.suggestions') }}",

                    method: "GET",

                    data: { query: query },

                    success: function(data) {

                        let list = $('#suggestionList');

                        list.empty();



                        if (data.length > 0) {

                            list.show();

                            data.forEach(function(item) {

                                list.append(`<li class="list-group-item list-group-item-action text-dark" style="cursor: pointer;" onclick="selectPrompt('${item.title}')">${item.title}</li>`);

                            });

                        } else {

                            list.hide();

                        }

                    }

                });

            } else {

                $('#suggestionList').hide();

            }

        });

    });



    function selectPrompt(title) {

        $('#searchBox').val(title);

        $('#suggestionList').hide();

    }





    $(document).click(function(e) {

        if (!$(e.target).closest('#searchBox, #suggestionList').length) {

            $('#suggestionList').hide();

        }

    });

    </script>

</body>

</html>