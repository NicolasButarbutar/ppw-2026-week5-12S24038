<x-layout>
    <x-slot:title>Tambah Buku Baru</x-slot:title>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('buku.index') }}" class="text-decoration-none">Katalog Buku</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tambah Buku</li>
                </ol>
            </nav>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold" style="color: var(--del-purple-900);">
                        <i class="bi bi-journal-plus text-warning me-2"></i>Formulir Tambah Koleksi Buku
                    </h5>
                    <a href="{{ route('buku.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('buku.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <!-- ISBN -->
                            <div class="col-md-6">
                                <label for="isbn" class="form-label fw-semibold">
                                    Nomor ISBN <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('isbn') is-invalid @enderror" 
                                       id="isbn" 
                                       name="isbn" 
                                       value="{{ old('isbn') }}" 
                                       placeholder="Contoh: 978-602-291-765-1">
                                @error('isbn')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="form-text">Masukkan ISBN standar (10-20 karakter, unik).</div>
                                @enderror
                            </div>

                            <!-- Kategori -->
                            <div class="col-md-6">
                                <label for="kategori_id" class="form-label fw-semibold">
                                    Kategori Buku <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('kategori_id') is-invalid @enderror" 
                                        id="kategori_id" 
                                        name="kategori_id">
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($kategoris as $kategori)
                                        <option value="{{ $kategori->id }}" {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                            {{ $kategori->nama_kategori }} ({{ $kategori->kode_kategori }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('kategori_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Judul Buku -->
                            <div class="col-12">
                                <label for="judul" class="form-label fw-semibold">
                                    Judul Buku <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('judul') is-invalid @enderror" 
                                       id="judul" 
                                       name="judul" 
                                       value="{{ old('judul') }}" 
                                       placeholder="Masukkan judul lengkap buku (minimal 5 karakter)">
                                @error('judul')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Penulis -->
                            <div class="col-md-6">
                                <label for="penulis" class="form-label fw-semibold">
                                    Nama Penulis / Pengarang <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('penulis') is-invalid @enderror" 
                                       id="penulis" 
                                       name="penulis" 
                                       value="{{ old('penulis') }}" 
                                       placeholder="Contoh: Robert C. Martin">
                                @error('penulis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Penerbit -->
                            <div class="col-md-6">
                                <label for="penerbit" class="form-label fw-semibold">
                                    Nama Penerbit <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('penerbit') is-invalid @enderror" 
                                       id="penerbit" 
                                       name="penerbit" 
                                       value="{{ old('penerbit') }}" 
                                       placeholder="Contoh: Informatika Bandung">
                                @error('penerbit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tahun Terbit -->
                            <div class="col-md-6">
                                <label for="tahun_terbit" class="form-label fw-semibold">
                                    Tahun Terbit <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       class="form-control @error('tahun_terbit') is-invalid @enderror" 
                                       id="tahun_terbit" 
                                       name="tahun_terbit" 
                                       value="{{ old('tahun_terbit', date('Y')) }}" 
                                       min="1900" 
                                       max="{{ date('Y') + 1 }}">
                                @error('tahun_terbit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Stok -->
                            <div class="col-md-6">
                                <label for="stok" class="form-label fw-semibold">
                                    Jumlah Stok (Eksemplar) <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       class="form-control @error('stok') is-invalid @enderror" 
                                       id="stok" 
                                       name="stok" 
                                       value="{{ old('stok', 1) }}" 
                                       min="0">
                                @error('stok')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="form-text">Stok minimal 0 (tidak boleh negatif).</div>
                                @enderror
                            </div>

                            <!-- Sinopsis / Deskripsi -->
                            <div class="col-12">
                                <label for="sinopsis" class="form-label fw-semibold">
                                    Sinopsis / Ringkasan Buku
                                </label>
                                <textarea class="form-control @error('sinopsis') is-invalid @enderror" 
                                          id="sinopsis" 
                                          name="sinopsis" 
                                          rows="4" 
                                          placeholder="Tuliskan ringkasan isi buku atau deskripsi singkat...">{{ old('sinopsis') }}</textarea>
                                @error('sinopsis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('buku.index') }}" class="btn btn-light px-4">Batal</a>
                            <button type="submit" class="btn btn-del px-4 shadow-sm">
                                <i class="bi bi-save me-1"></i> Simpan Buku
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>
