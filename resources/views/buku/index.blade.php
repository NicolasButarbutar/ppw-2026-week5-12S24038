<x-layout>
    <x-slot:title>Katalog Buku Perpustakaan</x-slot:title>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: var(--del-purple-900);">
                <i class="bi bi-book-half text-warning me-2"></i>Katalog Koleksi Buku
            </h2>
            <p class="text-muted mb-0">Kelola dan telusuri seluruh koleksi pustaka civitas akademika IT Del.</p>
        </div>
        <div>
            <a href="{{ route('buku.create') }}" class="btn btn-del shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Tambah Buku Baru
            </a>
        </div>
    </div>

    <!-- Filter & Search Section -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('buku.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0" 
                               placeholder="Cari judul, penulis, penerbit, atau ISBN..." 
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="kategori_id" class="form-select">
                        <option value="">-- Semua Kategori --</option>
                        @foreach($kategoris as $cat)
                            <option value="{{ $cat->id }}" {{ request('kategori_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->nama_kategori }} ({{ $cat->kode_kategori }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-del flex-grow-1">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if(request('search') || request('kategori_id'))
                        <a href="{{ route('buku.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Book Table Card -->
    <div class="card shadow-sm border-0 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold" style="color: var(--del-purple-800);">
                <i class="bi bi-list-columns-reverse me-2"></i>Daftar Buku (Total: {{ $bukus->total() }})
            </h6>
            <span class="badge bg-light text-dark border">Halaman {{ $bukus->currentPage() }} dari {{ $bukus->lastPage() }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="text-secondary small text-uppercase">
                        <th style="width: 5%;" class="text-center">No</th>
                        <th style="width: 15%;">ISBN</th>
                        <th style="width: 25%;">Judul & Tahun</th>
                        <th style="width: 15%;">Penulis & Penerbit</th>
                        <th style="width: 15%;">Kategori</th>
                        <th style="width: 8%;" class="text-center">Stok</th>
                        <th style="width: 17%;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bukus as $index => $buku)
                        <tr>
                            <td class="text-center text-muted fw-bold">
                                {{ $bukus->firstItem() + $index }}
                            </td>
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
                            <td>
                                <span class="badge badge-del px-2 py-1 rounded-pill">
                                    <i class="bi bi-tag-fill me-1"></i>{{ $buku->kategori->nama_kategori }}
                                </span>
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
                                    <a href="{{ route('buku.show', $buku) }}" class="btn btn-outline-primary" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('buku.edit', $buku) }}" class="btn btn-outline-warning" title="Edit Buku">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="if(confirm('Apakah Anda yakin ingin menghapus buku \'{{ addslashes($buku->judul) }}\'?')) { document.getElementById('delete-form-{{ $buku->id }}').submit(); }"
                                            title="Hapus Buku">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                                <form id="delete-form-{{ $buku->id }}" action="{{ route('buku.destroy', $buku) }}" method="POST" class="d-none">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 text-secondary d-block mb-2"></i>
                                <h6 class="fw-bold">Belum Ada Data Buku</h6>
                                <p class="small mb-3">Tidak ada buku yang sesuai dengan kriteria pencarian atau belum ada data yang tersimpan.</p>
                                <a href="{{ route('buku.create') }}" class="btn btn-del btn-sm">
                                    <i class="bi bi-plus-circle me-1"></i> Tambah Koleksi Sekarang
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bukus->hasPages())
            <div class="card-footer bg-white py-3 border-top d-flex justify-content-center">
                {{ $bukus->links() }}
            </div>
        @endif
    </div>
</x-layout>
