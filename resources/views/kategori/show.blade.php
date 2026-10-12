<x-layout>
    <x-slot:title>Kategori: {{ $kategori->nama_kategori }}</x-slot:title>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('kategori.index') }}" class="text-decoration-none">Kategori Buku</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $kategori->nama_kategori }}</li>
        </ol>
    </nav>

    <!-- Header Section -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge badge-del font-monospace fs-6 px-3 py-1">
                            {{ $kategori->kode_kategori }}
                        </span>
                        <span class="badge bg-secondary-subtle text-secondary border px-3 py-1 rounded-pill">
                            <i class="bi bi-book me-1"></i>{{ $kategori->bukus->count() }} Judul Buku Terdaftar
                        </span>
                    </div>
                    <h2 class="fw-bold mb-0" style="color: var(--del-purple-900);">
                        {{ $kategori->nama_kategori }}
                    </h2>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('kategori.edit', $kategori) }}" class="btn btn-warning fw-semibold">
                        <i class="bi bi-pencil-square me-1"></i> Edit Kategori
                    </a>
                    <a href="{{ route('buku.create') }}" class="btn btn-del">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Buku ke Kategori Ini
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Books List -->
    <div class="card shadow-sm border-0 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold" style="color: var(--del-purple-800);">
                <i class="bi bi-journal-text me-2"></i>Daftar Buku Dalam Kategori Ini
            </h6>
            <a href="{{ route('kategori.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Kategori
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="text-secondary small text-uppercase">
                        <th style="width: 5%;" class="text-center">No</th>
                        <th style="width: 15%;">ISBN</th>
                        <th style="width: 35%;">Judul & Tahun</th>
                        <th style="width: 20%;">Penulis & Penerbit</th>
                        <th style="width: 10%;" class="text-center">Stok</th>
                        <th style="width: 15%;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kategori->bukus as $index => $buku)
                        <tr>
                            <td class="text-center text-muted fw-bold">{{ $index + 1 }}</td>
                            <td>
                                <span class="badge bg-light text-secondary border font-monospace">
                                    {{ $buku->isbn }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('buku.show', $buku) }}" class="fw-bold text-decoration-none" style="color: var(--del-purple-700);">
                                    {{ $buku->judul }}
                                </a>
                                <div class="small text-muted">
                                    <i class="bi bi-calendar3 me-1"></i> Terbit: {{ $buku->tahun_terbit }}
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $buku->penulis }}</div>
                                <div class="small text-muted">{{ $buku->penerbit }}</div>
                            </td>
                            <td class="text-center">
                                @if($buku->stok > 0)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        {{ $buku->stok }} eks
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                        Habis
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('buku.show', $buku) }}" class="btn btn-outline-primary" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('buku.edit', $buku) }}" class="btn btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-journal-x fs-1 text-secondary d-block mb-2"></i>
                                <h6 class="fw-bold">Belum Ada Buku Terdaftar</h6>
                                <p class="small mb-3">Kategori ini belum memiliki koleksi buku.</p>
                                <a href="{{ route('buku.create') }}" class="btn btn-del btn-sm">
                                    <i class="bi bi-plus-circle me-1"></i> Tambahkan Buku Sekarang
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layout>
