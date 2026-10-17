<x-layout>
    <x-slot:title>{{ $buku->judul }}</x-slot:title>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('buku.index') }}" class="text-decoration-none">Katalog Buku</a></li>
            <li class="breadcrumb-item"><a href="{{ route('kategori.show', $buku->kategori) }}" class="text-decoration-none">{{ $buku->kategori->nama_kategori }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($buku->judul, 30) }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Main Book Details -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge badge-del px-3 py-2 rounded-pill">
                            <i class="bi bi-tag-fill me-1"></i>{{ $buku->kategori->nama_kategori }}
                        </span>
                        <span class="badge bg-light text-secondary border font-monospace px-3 py-2">
                            ISBN: {{ $buku->isbn }}
                        </span>
                    </div>

                    <h2 class="fw-bold mb-3" style="color: var(--del-purple-900);">
                        {{ $buku->judul }}
                    </h2>

                    <div class="row g-3 py-3 border-top border-bottom my-3">
                        <div class="col-sm-6">
                            <div class="text-muted small">Penulis</div>
                            <div class="fw-bold text-dark fs-6">{{ $buku->penulis }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted small">Penerbit</div>
                            <div class="fw-bold text-dark fs-6">{{ $buku->penerbit }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted small">Tahun Terbit</div>
                            <div class="fw-bold text-dark fs-6">{{ $buku->tahun_terbit }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-muted small">Status Ketersediaan</div>
                            <div>
                                @if($buku->stok > 0)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">
                                        <i class="bi bi-check-circle me-1"></i> Tersedia ({{ $buku->stok }} Eksemplar)
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1">
                                        <i class="bi bi-x-circle me-1"></i> Stok Habis
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5 class="fw-bold mb-2" style="color: var(--del-purple-800);">
                            <i class="bi bi-card-text me-1 text-warning"></i> Sinopsis & Deskripsi
                        </h5>
                        <div class="text-secondary lh-lg" style="white-space: pre-line;">
                            {{ $buku->sinopsis ?? 'Belum ada sinopsis untuk buku ini.' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Riwayat Peminjaman Buku Ini -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold" style="color: var(--del-purple-800);">
                        <i class="bi bi-clock-history me-2"></i>Riwayat Sirkulasi Peminjaman Buku Ini
                    </h6>
                    <span class="badge bg-light text-secondary border">Total: {{ $buku->peminjamans->count() }} Transaksi</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Kode Pinjam</th>
                                <th>NIM & Peminjam</th>
                                <th>Tgl Pinjam</th>
                                <th>Jatuh Tempo</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($buku->peminjamans()->latest()->get() as $p)
                                <tr>
                                    <td class="font-monospace fw-bold">{{ $p->kode_pinjam }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $p->nama_peminjam }}</div>
                                        <div class="text-muted">{{ $p->nim_peminjam }}</div>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($p->tanggal_pinjam)->translatedFormat('d M Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($p->tanggal_jatuh_tempo)->translatedFormat('d M Y') }}</td>
                                    <td>
                                        @if($p->status === 'dipinjam')
                                            <span class="badge bg-warning-subtle text-warning-emphasis">Dipinjam</span>
                                        @else
                                            <span class="badge bg-success-subtle text-success">Dikembalikan</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($p->status === 'dipinjam')
                                            <form action="{{ route('peminjaman.kembali', $p->id) }}" method="POST" onsubmit="return confirm('Konfirmasi pengembalian buku? Stok akan bertambah (+1).')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success py-0 px-2">Kembalikan</button>
                                            </form>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        Belum ada riwayat peminjaman untuk buku ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar / Action Card -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4 sticky-top" style="top: 80px;">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold" style="color: var(--del-purple-800);">
                        <i class="bi bi-gear-fill me-2 text-secondary"></i>Tindakan Pengelolaan
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="d-grid gap-2">
                        @if($buku->stok > 0)
                            <a href="{{ route('peminjaman.create', ['buku_id' => $buku->id]) }}" class="btn btn-success fw-bold shadow-sm">
                                <i class="bi bi-arrow-left-right me-1"></i> Pinjam Buku Ini
                            </a>
                        @else
                            <button class="btn btn-secondary fw-semibold" disabled>
                                <i class="bi bi-x-circle me-1"></i> Stok Habis (Tidak Dapat Dipinjam)
                            </button>
                        @endif

                        <a href="{{ route('buku.edit', $buku) }}" class="btn btn-warning fw-bold">
                            <i class="bi bi-pencil-square me-1"></i> Edit Data Buku
                        </a>

                        <button type="button" class="btn btn-outline-danger" 
                                onclick="if(confirm('Yakin ingin menghapus buku \'{{ addslashes($buku->judul) }}\'? Data yang dihapus tidak dapat dipulihkan.')) { document.getElementById('delete-form-detail').submit(); }">
                            <i class="bi bi-trash me-1"></i> Hapus dari Katalog
                        </button>
                        
                        <form id="delete-form-detail" action="{{ route('buku.destroy', $buku) }}" method="POST" class="d-none">
                            @csrf
                            @method('DELETE')
                        </form>

                        <hr class="my-2">

                        <a href="{{ route('kategori.show', $buku->kategori) }}" class="btn btn-outline-del">
                            <i class="bi bi-folder2-open me-1"></i> Lihat Kategori: {{ $buku->kategori->nama_kategori }}
                        </a>

                        <a href="{{ route('buku.index') }}" class="btn btn-light">
                            <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog
                        </a>
                    </div>
                </div>
                <div class="card-footer bg-light py-3 border-top small text-muted text-center">
                    <div>Ditambahkan: {{ $buku->created_at?->translatedFormat('d F Y, H:i') ?? '-' }}</div>
                    <div>Pembaruan Terakhir: {{ $buku->updated_at?->translatedFormat('d F Y, H:i') ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
