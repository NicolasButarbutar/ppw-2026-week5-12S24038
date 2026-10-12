<x-layout>
    <x-slot:title>Tambah Kategori Buku Baru</x-slot:title>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('kategori.index') }}" class="text-decoration-none">Kategori Buku</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tambah Kategori</li>
                </ol>
            </nav>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold" style="color: var(--del-purple-900);">
                        <i class="bi bi-folder-plus text-warning me-2"></i>Formulir Tambah Kategori
                    </h5>
                    <a href="{{ route('kategori.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('kategori.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="kode_kategori" class="form-label fw-semibold">
                                Kode Kategori <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('kode_kategori') is-invalid @enderror" 
                                   id="kode_kategori" 
                                   name="kode_kategori" 
                                   value="{{ old('kode_kategori') }}" 
                                   placeholder="Contoh: KAT-RPL atau TI-01" 
                                   style="text-transform: uppercase;">
                            @error('kode_kategori')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">Kode unik kategori maksimal 20 karakter.</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="nama_kategori" class="form-label fw-semibold">
                                Nama Kategori <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('nama_kategori') is-invalid @enderror" 
                                   id="nama_kategori" 
                                   name="nama_kategori" 
                                   value="{{ old('nama_kategori') }}" 
                                   placeholder="Contoh: Rekayasa Perangkat Lunak">
                            @error('nama_kategori')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">Nama klasifikasi kategori buku (3-100 karakter).</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('kategori.index') }}" class="btn btn-light px-4">Batal</a>
                            <button type="submit" class="btn btn-del px-4 shadow-sm">
                                <i class="bi bi-save me-1"></i> Simpan Kategori
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>
