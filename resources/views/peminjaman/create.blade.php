<x-layout>
    <x-slot:title>Catat Peminjaman Buku Baru</x-slot:title>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('buku.index') }}" class="text-decoration-none">Katalog Buku</a></li>
            <li class="breadcrumb-item"><a href="{{ route('peminjaman.index') }}" class="text-decoration-none">Sirkulasi Peminjaman</a></li>
            <li class="breadcrumb-item active" aria-current="page">Catat Peminjaman</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="card-title fw-bold mb-0" style="color: var(--del-purple-800);">
                            <i class="bi bi-journal-plus text-warning me-2"></i>Formulir Transaksi Peminjaman Buku
                        </h5>
                        <small class="text-muted">Proses mutasi stok buku dan pencatatan sirkulasi secara atomik (ACID).</small>
                    </div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                        <i class="bi bi-shield-lock me-1"></i>ACID Protected
                    </span>
                </div>

                <div class="card-body p-4">
                    <div class="alert alert-info border-0 d-flex align-items-start gap-2 mb-4" role="alert">
                        <i class="bi bi-info-circle-fill fs-5 mt-1 text-primary"></i>
                        <div class="small">
                            <strong>Mekanisme Integritas Transaksi ACID:</strong><br>
                            Sistem menerapkan <code>DB::transaction()</code> dan Pessimistic Locking (<code>lockForUpdate()</code>) pada baris data buku. Transaksi akan otomatis ditolak dan di-rollback apabila stok buku habis (0) atau mahasiswa dengan NIM yang sama belum mengembalikan peminjaman buku tersebut.
                        </div>
                    </div>

                    <form action="{{ route('peminjaman.store') }}" method="POST">
                        @csrf

                        <!-- Buku Selection -->
                        <div class="mb-3">
                            <label for="buku_id" class="form-label fw-semibold text-secondary">
                                Pilih Buku yang Dipinjam <span class="text-danger">*</span>
                            </label>
                            <select name="buku_id" id="buku_id" class="form-select @error('buku_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Buku dari Katalog --</option>
                                @foreach($bukus as $b)
                                    <option value="{{ $b->id }}" 
                                        {{ (old('buku_id', $selectedBukuId) == $b->id) ? 'selected' : '' }}
                                        {{ $b->stok < 1 ? 'class=text-danger' : '' }}>
                                        {{ $b->judul }} (ISBN: {{ $b->isbn }}) — Sisa Stok: {{ $b->stok }} eksemplar {{ $b->stok < 1 ? '[HABIS]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('buku_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-muted">
                                Pastikan memilih buku dengan sisa stok minimal 1 eksemplar.
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <!-- NIM Peminjam -->
                            <div class="col-md-6">
                                <label for="nim_peminjam" class="form-label fw-semibold text-secondary">
                                    NIM Mahasiswa / Sivitas <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="bi bi-person-badge"></i></span>
                                    <input type="text" name="nim_peminjam" id="nim_peminjam" 
                                           class="form-control @error('nim_peminjam') is-invalid @enderror" 
                                           placeholder="Contoh: 12S24038" 
                                           value="{{ old('nim_peminjam', session('last_borrower_nim')) }}" required>
                                    @error('nim_peminjam')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                @if(session('last_borrower_nim'))
                                    <div class="form-text text-primary small">
                                        <i class="bi bi-clock-history"></i> Terisi otomatis dari sesi peminjaman terakhir ({{ session('last_borrower_nim') }}).
                                    </div>
                                @endif
                            </div>

                            <!-- Nama Peminjam -->
                            <div class="col-md-6">
                                <label for="nama_peminjam" class="form-label fw-semibold text-secondary">
                                    Nama Lengkap Mahasiswa <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="bi bi-person"></i></span>
                                    <input type="text" name="nama_peminjam" id="nama_peminjam" 
                                           class="form-control @error('nama_peminjam') is-invalid @enderror" 
                                           placeholder="Contoh: Nicolas J Grace Butarbutar" 
                                           value="{{ old('nama_peminjam', session('last_borrower_name')) }}" required>
                                    @error('nama_peminjam')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Durasi Pinjam -->
                        <div class="mb-4">
                            <label for="durasi_hari" class="form-label fw-semibold text-secondary">
                                Durasi Peminjaman (Hari) <span class="text-danger">*</span>
                            </label>
                            <select name="durasi_hari" id="durasi_hari" class="form-select @error('durasi_hari') is-invalid @enderror">
                                <option value="7" {{ old('durasi_hari', 7) == 7 ? 'selected' : '' }}>7 Hari (Standar 1 Minggu)</option>
                                <option value="14" {{ old('durasi_hari') == 14 ? 'selected' : '' }}>14 Hari (2 Minggu)</option>
                                <option value="3" {{ old('durasi_hari') == 3 ? 'selected' : '' }}>3 Hari (Peminjaman Singkat)</option>
                            </select>
                            <div class="form-text text-muted">Tanggal jatuh tempo akan dihitung otomatis sejak tanggal hari ini.</div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('peminjaman.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-del fw-bold shadow-sm">
                                <i class="bi bi-check2-circle me-1"></i> Proses Transaksi Peminjaman (ACID)
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>
