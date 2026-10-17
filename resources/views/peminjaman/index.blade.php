<x-layout>
    <x-slot:title>Sirkulasi Peminjaman Buku</x-slot:title>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: var(--del-purple-900);">
                <i class="bi bi-arrow-left-right text-warning me-2"></i>Sirkulasi Peminjaman & Pengembalian
            </h2>
            <p class="text-muted mb-0">Manajemen sirkulasi buku perpustakaan dengan integritas transaksi ACID dan Concurrency Locking.</p>
        </div>
        <div>
            <a href="{{ route('peminjaman.create') }}" class="btn btn-del shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Pinjam Buku Baru
            </a>
        </div>
    </div>

    <!-- Filter & Search Section -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('peminjaman.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0" 
                               placeholder="Cari kode pinjam, NIM, nama, atau judul buku..." 
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">-- Semua Status Transaksi --</option>
                        <option value="dipinjam" {{ request('status') === 'dipinjam' ? 'selected' : '' }}>Sedang Dipinjam</option>
                        <option value="dikembalikan" {{ request('status') === 'dikembalikan' ? 'selected' : '' }}>Sudah Dikembalikan</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-del flex-grow-1">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('peminjaman.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Peminjaman Table Card -->
    <div class="card shadow-sm border-0 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold" style="color: var(--del-purple-800);">
                <i class="bi bi-journal-text me-2"></i>Daftar Catatan Peminjaman (Total: {{ $peminjamans->total() }})
            </h6>
            <span class="badge bg-light text-dark border">Halaman {{ $peminjamans->currentPage() }} dari {{ $peminjamans->lastPage() }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="text-secondary small text-uppercase">
                        <th style="width: 5%;" class="text-center">No</th>
                        <th style="width: 15%;">Kode Pinjam</th>
                        <th style="width: 25%;">Buku</th>
                        <th style="width: 20%;">Peminjam</th>
                        <th style="width: 15%;">Periode Pinjam</th>
                        <th style="width: 10%;" class="text-center">Status</th>
                        <th style="width: 10%;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($peminjamans as $index => $pinjam)
                        <tr>
                            <td class="text-center text-muted fw-bold">
                                {{ $peminjamans->firstItem() + $index }}
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace px-2 py-1">
                                    {{ $pinjam->kode_pinjam }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('buku.show', $pinjam->buku) }}" class="fw-bold text-decoration-none" style="color: var(--del-purple-700);">
                                    {{ $pinjam->buku->judul }}
                                </a>
                                <div class="small text-muted font-monospace">ISBN: {{ $pinjam->buku->isbn }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $pinjam->nama_peminjam }}</div>
                                <div class="small text-muted font-monospace"><i class="bi bi-person-badge me-1"></i>{{ $pinjam->nim_peminjam }}</div>
                            </td>
                            <td>
                                <div class="small"><i class="bi bi-calendar-event me-1 text-primary"></i>Pinjam: {{ \Carbon\Carbon::parse($pinjam->tanggal_pinjam)->translatedFormat('d M Y') }}</div>
                                <div class="small"><i class="bi bi-clock me-1 text-warning"></i>Tempo: {{ \Carbon\Carbon::parse($pinjam->tanggal_jatuh_tempo)->translatedFormat('d M Y') }}</div>
                                @if($pinjam->tanggal_kembali)
                                    <div class="small text-success"><i class="bi bi-check2-circle me-1"></i>Kembali: {{ \Carbon\Carbon::parse($pinjam->tanggal_kembali)->translatedFormat('d M Y') }}</div>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($pinjam->status === 'dipinjam')
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                        <i class="bi bi-hourglass-split me-1"></i>Dipinjam
                                    </span>
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="bi bi-check-circle-fill me-1"></i>Dikembalikan
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($pinjam->status === 'dipinjam')
                                    <form action="{{ route('peminjaman.kembali', $pinjam->id) }}" method="POST" onsubmit="return confirm('Konfirmasi pengembalian buku \'{{ addslashes($pinjam->buku->judul) }}\'? Stok buku akan otomatis bertambah (+1).')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success fw-semibold shadow-sm">
                                            <i class="bi bi-arrow-return-left me-1"></i> Kembalikan
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small"><i class="bi bi-check-all text-success"></i> Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 text-secondary d-block mb-2"></i>
                                <h6 class="fw-bold">Belum Ada Transaksi Peminjaman</h6>
                                <p class="small mb-3">Tidak ada data sirkulasi yang sesuai dengan kriteria filter.</p>
                                <a href="{{ route('peminjaman.create') }}" class="btn btn-del btn-sm">
                                    <i class="bi bi-plus-circle me-1"></i> Catat Transaksi Baru
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($peminjamans->hasPages())
            <div class="card-footer bg-white py-3 border-top d-flex justify-content-center">
                {{ $peminjamans->links() }}
            </div>
        @endif
    </div>
</x-layout>
