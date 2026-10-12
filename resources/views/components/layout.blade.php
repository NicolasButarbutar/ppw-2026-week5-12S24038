<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? 'SIPUS-Del' }} — Institut Teknologi Del</title>

    <!-- Bootstrap 5.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --del-purple-900: #2E1065;
            --del-purple-800: #3B0764;
            --del-purple-700: #4C1D95;
            --del-purple-600: #6B21A8;
            --del-purple-500: #7E22CE;
            --del-purple-100: #F3E8FF;
            --del-purple-50:  #FAF5FF;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #F8FAFC;
            color: #1E293B;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar Styling */
        .navbar-del {
            background: linear-gradient(135deg, var(--del-purple-800) 0%, var(--del-purple-700) 100%);
            border-bottom: 3px solid #F59E0B;
        }

        .navbar-brand {
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .nav-link {
            font-weight: 500;
            color: rgba(255, 255, 255, 0.85) !important;
            transition: all 0.2s ease-in-out;
            padding: 0.5rem 1rem !important;
            border-radius: 0.375rem;
        }

        .nav-link:hover, .nav-link.active {
            color: #FFFFFF !important;
            background-color: rgba(255, 255, 255, 0.15);
        }

        /* Custom Del Buttons */
        .btn-del {
            background-color: var(--del-purple-700);
            border-color: var(--del-purple-700);
            color: #FFFFFF;
            font-weight: 600;
        }

        .btn-del:hover, .btn-del:focus {
            background-color: var(--del-purple-800);
            border-color: var(--del-purple-800);
            color: #FFFFFF;
        }

        .btn-outline-del {
            border-color: var(--del-purple-700);
            color: var(--del-purple-700);
            font-weight: 600;
        }

        .btn-outline-del:hover {
            background-color: var(--del-purple-700);
            border-color: var(--del-purple-700);
            color: #FFFFFF;
        }

        /* Del Badge */
        .badge-del {
            background-color: var(--del-purple-100);
            color: var(--del-purple-700);
            font-weight: 600;
        }

        .card {
            border-radius: 0.75rem;
            border: 1px solid #E2E8F0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }

        .card-header-del {
            background-color: #FFFFFF;
            border-bottom: 1px solid #E2E8F0;
            padding: 1.25rem 1.5rem;
        }

        .table > :not(caption) > * > * {
            padding: 0.875rem 1rem;
            vertical-align: middle;
        }

        .pagination {
            margin-bottom: 0;
        }

        .page-item.active .page-link {
            background-color: var(--del-purple-700);
            border-color: var(--del-purple-700);
        }

        .page-link {
            color: var(--del-purple-700);
        }

        footer {
            margin-top: auto;
            background-color: #0F172A;
            color: #94A3B8;
        }
    </style>
</head>
<body>
    <!-- Navbar Header -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-del shadow sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('buku.index') }}">
                <i class="bi bi-book-half fs-3 text-warning"></i>
                <div>
                    <span class="fs-5">SIPUS-Del</span>
                    <small class="d-block text-white-50" style="font-size: 0.7rem; font-weight: 400; margin-top: -3px;">Sistem Informasi Perpustakaan IT Del</small>
                </div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4 gap-lg-1">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('buku.*') ? 'active' : '' }}" href="{{ route('buku.index') }}">
                            <i class="bi bi-journal-bookmark me-1"></i> Katalog Buku
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('kategori.*') ? 'active' : '' }}" href="{{ route('kategori.index') }}">
                            <i class="bi bi-tags me-1"></i> Kategori Buku
                        </a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('buku.create') }}" class="btn btn-warning btn-sm fw-bold shadow-sm">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Buku
                    </a>
                    <a href="{{ route('kategori.create') }}" class="btn btn-outline-light btn-sm fw-bold">
                        <i class="bi bi-folder-plus me-1"></i> Tambah Kategori
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="container my-4 pb-5 flex-grow-1">
        <!-- Flash Alert Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 d-flex align-items-center" role="alert">
                <i class="bi bi-check-circle-fill fs-5 me-2 text-success"></i>
                <div class="flex-grow-1">{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-danger"></i>
                <div class="flex-grow-1">{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-warning alert-dismissible fade show shadow-sm border-0" role="alert">
                <div class="d-flex align-items-center mb-1">
                    <i class="bi bi-exclamation-circle-fill fs-5 me-2 text-warning"></i>
                    <strong>Terjadi kesalahan input pada formulir:</strong>
                </div>
                <ul class="mb-0 ps-4 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="py-4 border-top border-secondary">
        <div class="container text-center text-md-start">
            <div class="row align-items-center">
                <div class="col-md-6 mb-3 mb-md-0">
                    <h6 class="text-white fw-bold mb-1">
                        <i class="bi bi-building-check text-warning me-1"></i> Institut Teknologi Del
                    </h6>
                    <p class="small mb-0 text-secondary">
                        Sistem Informasi Perpustakaan Kampus Del (SIPUS-Del) &bull; Praktikum Pemrograman & Pengujian Web (12S3101)
                    </p>
                </div>
                <div class="col-md-6 text-md-end text-secondary small">
                    <div>Pola Arsitektur MVC &bull; Eloquent ORM &bull; Eager Loading Mitigation</div>
                    <div class="text-white-50">Laguboti, Toba, Sumatera Utara &bull; &copy; {{ date('Y') }}</div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
