<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - AI Prompt Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-light d-flex flex-column min-vh-100">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="{{ route('home') }}">
                <i class="bi bi-cpu-fill text-primary me-1"></i> AI Prompt Hub
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link text-white fw-semibold" href="{{ route('home') }}"><i
                        class="bi bi-arrow-left me-1"></i> Back to Home</a>
            </div>
        </div>
    </nav>

    <!-- Contact Form Section -->
    <div class="container my-5 flex-grow-1">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border-0 rounded-4 p-4">
                    <h2 class="fw-bold mb-3 text-center">Get in Touch</h2>
                    <p class="text-muted text-center mb-4">Have any questions or suggestions? Drop us a message below.
                    </p>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('contact.submit') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Your Name</label>
                            <input type="text" name="name" class="form-control" required placeholder="Enter your name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control" required
                                placeholder="Enter your email">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Message</label>
                            <textarea name="message" class="form-control" rows="5" required
                                placeholder="How can we help you?"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center text-white py-3 bg-dark mt-auto">
        <small>© {{ date('Y') }} AI Prompt Hub. All rights reserved.</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>