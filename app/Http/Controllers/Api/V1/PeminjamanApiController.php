<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PeminjamanResource;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PeminjamanApiController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/peminjamans
     * Menampilkan daftar transaksi peminjaman terpaginasi (Wajib Bearer Token)
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 10), 1), 50);

        $paginator = Peminjaman::query()
            ->with('buku')
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('nim'), fn ($q, $nim) => $q->where('nim_peminjam', strtoupper(trim($nim))))
            ->when($request->query('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('kode_pinjam', 'like', "%{$search}%")
                        ->orWhere('nim_peminjam', 'like', "%{$search}%")
                        ->orWhere('nama_peminjam', 'like', "%{$search}%")
                        ->orWhereHas('buku', fn ($bq) => $bq->where('judul', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return $this->successResponse(
            data: PeminjamanResource::collection($paginator->items()),
            message: 'Daftar transaksi peminjaman berhasil diambil.',
            status: 200,
            meta: [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total_items' => $paginator->total(),
                'total_pages' => $paginator->lastPage(),
                'links' => [
                    'first' => $paginator->url(1),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                    'last' => $paginator->url($paginator->lastPage()),
                ],
            ]
        );
    }

    /**
     * GET /api/v1/peminjamans/{id}
     * Detail satu transaksi peminjaman (Wajib Bearer Token)
     */
    public function show(string $id): JsonResponse
    {
        $peminjaman = Peminjaman::with('buku')->find($id);

        if (!$peminjaman) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Transaksi peminjaman dengan ID '{$id}' tidak ditemukan.",
                status: 404,
                errorSlug: 'loan-not-found'
            );
        }

        return $this->successResponse(
            data: new PeminjamanResource($peminjaman),
            message: 'Detail transaksi peminjaman berhasil ditemukan.'
        );
    }

    /**
     * POST /api/v1/peminjamans
     * Mencatat transaksi peminjaman buku secara ACID + Concurrency Locking (Wajib Bearer Token)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'buku_id' => 'required|integer|exists:bukus,id',
            'nim_peminjam' => 'required|string|min:6|max:20',
            'nama_peminjam' => 'required|string|min:3|max:150',
            'durasi_hari' => 'nullable|integer|min:1|max:30',
        ]);

        return DB::transaction(function () use ($validated) {
            // 1. Kunci baris buku secara eksklusif (SELECT ... FOR UPDATE) untuk menangkal Race Condition
            $buku = Buku::where('id', $validated['buku_id'])->lockForUpdate()->first();

            if (!$buku) {
                return $this->problemResponse(
                    title: 'Resource Not Found',
                    detail: 'Buku yang dipilih tidak ditemukan.',
                    status: 404,
                    errorSlug: 'book-not-found'
                );
            }

            // 2. Cek ketersediaan sisa stok buku -> 422 Unprocessable Content jika habis
            if ($buku->stok < 1) {
                return $this->problemResponse(
                    title: 'Course Quota Full',
                    detail: "Stok buku '{$buku->judul}' sedang habis (0 eksemplar).",
                    status: 422,
                    errorSlug: 'quota-exhausted'
                );
            }

            // 3. Cek apakah mahasiswa masih meminjam buku yang sama dan belum dikembalikan -> 409 Conflict
            $nimFormatted = strtoupper(trim($validated['nim_peminjam']));
            $existingLoan = Peminjaman::where('buku_id', $buku->id)
                ->where('nim_peminjam', $nimFormatted)
                ->where('status', 'dipinjam')
                ->lockForUpdate()
                ->first();

            if ($existingLoan) {
                return $this->problemResponse(
                    title: 'Duplicate Loan Conflict',
                    detail: "Mahasiswa dengan NIM '{$nimFormatted}' masih meminjam buku ini (Kode: {$existingLoan->kode_pinjam}) dan belum mengembalikannya.",
                    status: 409,
                    errorSlug: 'duplicate-loan'
                );
            }

            // 4. Kurangi stok buku (-1) secara atomik
            $buku->decrement('stok');

            // 5. Generate kode pinjam unik dan catat data peminjaman
            $durasi = isset($validated['durasi_hari']) ? (int) $validated['durasi_hari'] : 7;
            $kodePinjam = 'PINJ-' . date('Ymd') . '-' . strtoupper(Str::random(5));

            $peminjaman = Peminjaman::create([
                'kode_pinjam' => $kodePinjam,
                'buku_id' => $buku->id,
                'nim_peminjam' => $nimFormatted,
                'nama_peminjam' => trim($validated['nama_peminjam']),
                'tanggal_pinjam' => now()->toDateString(),
                'tanggal_jatuh_tempo' => now()->addDays($durasi)->toDateString(),
                'status' => 'dipinjam',
            ]);

            $peminjaman->load('buku');

            return $this->createdResponse(
                data: new PeminjamanResource($peminjaman),
                locationUrl: url("/api/v1/peminjamans/{$peminjaman->id}"),
                message: "Peminjaman buku '{$buku->judul}' berhasil dicatat. Sisa stok: {$buku->fresh()->stok}."
            );
        });
    }

    /**
     * PATCH /api/v1/peminjamans/{id}/kembalikan
     * Mengembalikan buku secara ACID + Concurrency Locking (Wajib Bearer Token)
     */
    public function kembalikan(string $id): JsonResponse
    {
        return DB::transaction(function () use ($id) {
            // 1. Kunci baris peminjaman secara eksklusif
            $peminjaman = Peminjaman::where('id', $id)->lockForUpdate()->first();

            if (!$peminjaman) {
                return $this->problemResponse(
                    title: 'Resource Not Found',
                    detail: "Transaksi peminjaman dengan ID '{$id}' tidak ditemukan.",
                    status: 404,
                    errorSlug: 'loan-not-found'
                );
            }

            // 2. Jika sudah pernah dikembalikan -> tolak 409 Conflict
            if ($peminjaman->status === 'dikembalikan') {
                return $this->problemResponse(
                    title: 'Conflict — Already Returned',
                    detail: "Buku dengan kode peminjaman '{$peminjaman->kode_pinjam}' sudah pernah dikembalikan sebelumnya.",
                    status: 409,
                    errorSlug: 'already-returned'
                );
            }

            // 3. Kunci buku terkait dan pulihkan stok (+1)
            $buku = Buku::where('id', $peminjaman->buku_id)->lockForUpdate()->first();

            $peminjaman->update([
                'status' => 'dikembalikan',
                'tanggal_kembali' => now()->toDateString(),
            ]);

            if ($buku) {
                $buku->increment('stok');
            }

            $peminjaman->load('buku');

            return $this->successResponse(
                data: new PeminjamanResource($peminjaman),
                message: "Buku '{$peminjaman->buku?->judul}' berhasil dikembalikan dan stok telah dipulihkan (+1)."
            );
        });
    }
}
