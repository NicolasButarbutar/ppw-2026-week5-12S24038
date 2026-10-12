<x-layout>
    <x-slot:title>Kategori Buku Perpustakaan</x-slot:title>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: var(--del-purple-900);">
                <i class="bi bi-tags-fill text-warning me-2"></i>Kategori Koleksi Buku
            </h2>
            <p class="text-muted mb-0">Klasifikasi taksonomi koleksi pustaka Institut Teknologi Del.</p>
        </div>
        <div>
            <a href="{{ route('kategori.create') }}" class="btn btn-del shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Tambah Kategori Baru
            </a>
        </div>
    </div>

    <!-- Search Section -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('kategori.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0" 
                               placeholder="Cari nama atau kode kategori..." 
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-del flex-grow-1">
                        <i class="bi bi-search me-1"></i> Cari
                    </button>
                    @if(request('search'))
                        <a href="{{ route('kategori.index') }}" class="btn btn-outline-secondary" title="Reset">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Category Table Card -->
    <div class="card shadow-sm border-0 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold" style="color: var(--del-purple-800);">
                <i class="bi bi-folder2 me-2"></i>Daftar Kategori (Total: {{ $kategoris->total() }})
            </h6>
            <span class="badge bg-light text-dark border">Halaman {{ $kategoris->currentPage() }} dari {{ $kategoris->lastPage() }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="text-secondary small text-uppercase">
                        <th style="width: 6%;" class="text-center">No</th>
                        <th style="width: 20%;">Kode Kategori</th>
                        <th style="width: 40%;">Nama Kategori</th>
                        <th style="width: 14%;" class="text-center">Jumlah Buku</th>
                        <th style="width: 20%;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kategoris as $index => $kategori)
                        <tr>
                            <td class="text-center text-muted fw-bold">
                                {{ $kategoris->firstItem() + $index }}
                            </td>
                            <td>
                                <span class="badge badge-del font-monospace px-3 py-2">
                                    {{ $kategori->kode_kategori }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('kategori.show', $kategori) }}" class="fw-bold text-decoration-none" style="color: var(--del-purple-700);">
                                    {{ $kategori->nama_kategori }}
                                </a>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary-subtle text-secondary border px-3 py-1 rounded-pill">
                                    <i class="bi bi-book me-1"></i>{{ $kategori->bukus_count }} judul
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('kategori.show', $kategori) }}" class="btn btn-outline-primary" title="Lihat Koleksi Kategori">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('kategori.edit', $kategori) }}" class="btn btn-outline-warning" title="Edit Kategori">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="if(confirm('Apakah Anda yakin ingin menghapus kategori \'{{ addslashes($kategori->nama_kategori) }}\'? Semua buku dalam kategori ini juga akan terhapus.')) { document.getElementById('delete-kategori-{{ $kategori->id }}').submit(); }"
                                            title="Hapus Kategori">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                                <form id="delete-kategori-{{ $kategori->id }}" action="{{ route('kategori.destroy', $kategori) }}" method="POST" class="d-none">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-folder-x fs-1 text-secondary d-block mb-2"></i>
                                <h6 class="fw-bold">Belum Ada Data Kategori</h6>
                                <p class="small mb-3">Kategori belum tersedia atau tidak cocok dengan filter pencarian.</p>
                                <a href="{{ route('kategori.create') }}" class="btn btn-del btn-sm">
                                    <i class="bi bi-plus-circle me-1"></i> Buat Kategori Baru
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($kategoris->hasPages())
            <div class="card-footer bg-white py-3 border-top d-flex justify-content-center">
                {{ $kategoris->links() }}
            </div>
        @endif
    </div>
</x-layout>
