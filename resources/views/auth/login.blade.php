<x-layout>
    <x-slot:title>Login Pustakawan</x-slot:title>

    <div class="row justify-content-center align-items-center py-4">
        <div class="col-md-7 col-lg-5">
            <div class="card shadow-lg border-0 overflow-hidden">
                <div class="card-header py-4 text-center text-white" style="background: linear-gradient(135deg, var(--del-purple-900) 0%, var(--del-purple-700) 100%); border-bottom: 3px solid #F59E0B;">
                    <div class="mb-2">
                        <i class="bi bi-shield-lock-fill text-warning fs-1"></i>
                    </div>
                    <h4 class="fw-bold mb-0">Portal Pustakawan</h4>
                    <p class="small text-white-50 mb-0">Sistem Informasi Perpustakaan Institut Teknologi Del</p>
                </div>

                <div class="card-body p-4 p-md-5">
                    <div class="alert alert-light border small text-muted mb-4 d-flex align-items-center gap-2">
                        <i class="bi bi-key-fill text-warning fs-5"></i>
                        <div>
                            <strong>Akun Pustakawan Uji:</strong><br>
                            Email: <code class="text-primary">pustakawan@del.ac.id</code><br>
                            Kata Sandi: <code class="text-primary">password123</code>
                        </div>
                    </div>

                    <form action="{{ route('login.post') }}" method="POST">
                        @csrf

                        <!-- Email Input -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold text-secondary">
                                <i class="bi bi-envelope me-1"></i> Alamat Email
                            </label>
                            <input type="email" name="email" id="email" 
                                   class="form-control form-control-lg @error('email') is-invalid @enderror" 
                                   placeholder="pustakawan@del.ac.id" 
                                   value="{{ old('email', $rememberedEmail) }}" required autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($rememberedEmail && !old('email'))
                                <div class="form-text text-success small">
                                    <i class="bi bi-cookie me-1"></i> Email diingat otomatis dari HTTP Cookie (HttpOnly).
                                </div>
                            @endif
                        </div>

                        <!-- Password Input -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold text-secondary">
                                <i class="bi bi-lock me-1"></i> Kata Sandi
                            </label>
                            <input type="password" name="password" id="password" 
                                   class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                   placeholder="••••••••" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Remember Me Checkbox -->
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label small text-secondary" for="remember">
                                Ingat saya pada perangkat ini (Remember Me)
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-del btn-lg fw-bold shadow-sm">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang
                            </button>
                            <a href="{{ route('buku.index') }}" class="btn btn-light text-muted small">
                                <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog Publik
                            </a>
                        </div>
                    </form>
                </div>

                <div class="card-footer bg-light py-3 text-center border-top small text-muted">
                    <i class="bi bi-shield-check text-success me-1"></i> Dilindungi Session Regeneration & Anti-Session Fixation
                </div>
            </div>
        </div>
    </div>
</x-layout>
