<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\BukuResource;
use App\Models\Buku;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BukuApiController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/bukus
     * Menampilkan koleksi buku terpaginasi lengkap dengan filter, pencarian, dan pengurutan
     */
    public function index(Request $request): JsonResponse
    {
        $allowedSorts = ['id', 'judul', 'penulis', 'tahun_terbit', 'stok', 'created_at'];
        $sortBy = in_array($request->query('sort'), $allowedSorts, true) ? $request->query('sort') : 'judul';
        $sortDir = strtolower($request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $perPage = min(max((int) $request->query('per_page', 5), 1), 50);

        $paginator = Buku::query()
            ->with('kategori')
            ->withCount('peminjamans')
            ->when($request->query('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('judul', 'like', "%{$search}%")
                        ->orWhere('penulis', 'like', "%{$search}%")
                        ->orWhere('isbn', 'like', "%{$search}%");
                });
            })
            ->when($request->query('kategori_id'), fn ($q, $katId) => $q->where('kategori_id', (int) $katId))
            ->when($request->boolean('tersedia'), fn ($q) => $q->where('stok', '>', 0))
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString();

        return $this->successResponse(
            data: BukuResource::collection($paginator->items()),
            message: 'Daftar koleksi katalog buku berhasil diambil.',
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
     * GET /api/v1/bukus/{id}
     * Menampilkan detail satu buku beserta objek kategori dan riwayat peminjamannya
     */
    public function show(string $id): JsonResponse
    {
        $buku = Buku::with(['kategori', 'peminjamans'])
            ->withCount('peminjamans')
            ->where('id', $id)
            ->orWhere('isbn', $id)
            ->first();

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan pengenal '{$id}' tidak ditemukan di sistem katalog perpustakaan.",
                status: 404,
                errorSlug: 'book-not-found'
            );
        }

        return $this->successResponse(
            data: new BukuResource($buku),
            message: 'Detail buku berhasil ditemukan.'
        );
    }

    /**
     * POST /api/v1/bukus
     * Menambahkan buku baru ke katalog perpustakaan (Wajib Bearer Token)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'isbn' => 'required|string|max:20|unique:bukus,isbn',
            'judul' => 'required|string|max:255',
            'penulis' => 'required|string|max:150',
            'penerbit' => 'required|string|max:150',
            'tahun_terbit' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'kategori_id' => 'required|integer|exists:kategoris,id',
            'stok' => 'required|integer|min:0|max:1000',
            'sinopsis' => 'nullable|string|max:2000',
        ]);

        $buku = Buku::create($validated);
        $buku->load(['kategori']);
        $buku->loadCount('peminjamans');

        return $this->createdResponse(
            data: new BukuResource($buku),
            locationUrl: url("/api/v1/bukus/{$buku->id}"),
            message: 'Buku baru berhasil ditambahkan ke katalog perpustakaan.'
        );
    }

    /**
     * PUT / PATCH /api/v1/bukus/{id}
     * Demonstrasi perbedaan semantik PUT (Full Replacement) vs PATCH (Partial Update)
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan ID '{$id}' tidak ditemukan.",
                status: 404,
                errorSlug: 'book-not-found'
            );
        }

        $isPut = $request->isMethod('put');

        if ($isPut) {
            // SEMANTIK PUT: Seluruh atribut representasi WAJIB dikirimkan utuh
            $validated = $request->validate([
                'isbn' => "required|string|max:20|unique:bukus,isbn,{$buku->id}",
                'judul' => 'required|string|max:255',
                'penulis' => 'required|string|max:150',
                'penerbit' => 'required|string|max:150',
                'tahun_terbit' => 'required|integer|min:1900|max:' . (date('Y') + 1),
                'kategori_id' => 'required|integer|exists:kategoris,id',
                'stok' => 'required|integer|min:0|max:1000',
                'sinopsis' => 'nullable|string|max:2000',
            ]);

            // Pada PUT murni, atribut nullable yang tidak dikirim direset ke null
            $validated['sinopsis'] = $request->input('sinopsis', null);
        } else {
            // SEMANTIK PATCH: Hanya atribut yang ingin diubah yang dikirim
            $validated = $request->validate([
                'isbn' => "sometimes|required|string|max:20|unique:bukus,isbn,{$buku->id}",
                'judul' => 'sometimes|required|string|max:255',
                'penulis' => 'sometimes|required|string|max:150',
                'penerbit' => 'sometimes|required|string|max:150',
                'tahun_terbit' => 'sometimes|required|integer|min:1900|max:' . (date('Y') + 1),
                'kategori_id' => 'sometimes|required|integer|exists:kategoris,id',
                'stok' => 'sometimes|required|integer|min:0|max:1000',
                'sinopsis' => 'sometimes|nullable|string|max:2000',
            ]);

            if (empty($validated)) {
                return $this->problemResponse(
                    title: 'Bad Request',
                    detail: 'Permintaan PATCH wajib menyertakan minimal satu atribut yang ingin diperbarui.',
                    status: 400,
                    errorSlug: 'empty-patch-payload'
                );
            }
        }

        $buku->update($validated);
        $buku->load(['kategori']);
        $buku->loadCount('peminjamans');

        return $this->successResponse(
            data: new BukuResource($buku),
            message: $isPut
                ? 'Seluruh representasi buku berhasil diganti (PUT).'
                : 'Atribut buku berhasil diperbarui sebagian (PATCH).'
        );
    }

    /**
     * DELETE /api/v1/bukus/{id}
     * Menghapus buku -> Ditolak 409 Conflict jika memiliki peminjaman aktif, 204 jika sukses
     */
    public function destroy(string $id): JsonResponse
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan ID '{$id}' tidak ditemukan.",
                status: 404,
                errorSlug: 'book-not-found'
            );
        }

        $activeLoans = $buku->peminjamans()->where('status', 'dipinjam')->count();

        if ($activeLoans > 0) {
            return $this->problemResponse(
                title: 'Conflict — Book Has Active Loans',
                detail: "Buku '{$buku->judul}' tidak dapat dihapus karena masih sedang dipinjam oleh {$activeLoans} transaksi peminjaman aktif.",
                status: 409,
                errorSlug: 'book-has-active-loans'
            );
        }

        $buku->delete();

        return $this->noContentResponse();
    }

    /**
     * GET /api/v1/bukus/{id}/referensi-eksternal
     * Menjadi REST Service Consumer: Mengambil metadata tambahan dari OpenLibrary API
     */
    public function referensiEksternal(string $id): JsonResponse
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan ID '{$id}' tidak ditemukan.",
                status: 404,
                errorSlug: 'book-not-found'
            );
        }

        // Bersihkan ISBN
        $cleanIsbn = preg_replace('/[^0-9X]/i', '', (string) $buku->isbn);

        try {
            $response = Http::timeout(8)
                ->acceptJson()
                ->withoutVerifying()
                ->get('https://openlibrary.org/api/books', [
                    'bibkeys' => "ISBN:{$cleanIsbn}",
                    'format' => 'json',
                    'jscmd' => 'data',
                ]);

            if ($response->status() === 404) {
                return $this->problemResponse(
                    title: 'External Resource Not Found',
                    detail: "Metadata eksternal untuk ISBN '{$buku->isbn}' tidak ditemukan di OpenLibrary.",
                    status: 404,
                    errorSlug: 'external-book-not-found'
                );
            }

            if ($response->failed()) {
                return $this->problemResponse(
                    title: 'Bad Gateway',
                    detail: 'Gagal berkomunikasi dengan layanan eksternal OpenLibrary API.',
                    status: 502,
                    errorSlug: 'external-service-error'
                );
            }

            $json = $response->json();
            $bibKey = "ISBN:{$cleanIsbn}";

            if (empty($json) || !isset($json[$bibKey])) {
                return $this->successResponse(
                    data: [
                        'isbn' => $buku->isbn,
                        'judul_lokal' => $buku->judul,
                        'sumber' => 'OpenLibrary REST API',
                        'status_pencarian' => 'Data tidak ditemukan di katalog OpenLibrary',
                        'metadata' => null,
                    ],
                    message: 'Pencarian metadata eksternal selesai (tidak ada data tambahan di OpenLibrary).'
                );
            }

            $ext = $json[$bibKey];

            return $this->successResponse(
                data: [
                    'isbn' => $buku->isbn,
                    'judul_lokal' => $buku->judul,
                    'sumber' => 'OpenLibrary REST API',
                    'openlibrary_url' => $ext['url'] ?? null,
                    'judul_eksternal' => $ext['title'] ?? null,
                    'subjudul' => $ext['subtitle'] ?? null,
                    'jumlah_halaman' => $ext['number_of_pages'] ?? null,
                    'penerbit' => isset($ext['publishers']) ? collect($ext['publishers'])->pluck('name')->all() : [],
                    'tahun_terbit' => $ext['publish_date'] ?? null,
                    'pratinjau_url' => $ext['preview_url'] ?? null,
                    'cover' => $ext['cover'] ?? null,
                ],
                message: 'Metadata buku eksternal berhasil diambil dari OpenLibrary API.'
            );
        } catch (\Throwable $e) {
            return $this->problemResponse(
                title: 'Gateway Timeout / Network Error',
                detail: 'Koneksi ke OpenLibrary API mengalami gangguan atau batas waktu tercapai (timeout).',
                status: 504,
                errorSlug: 'external-service-timeout'
            );
        }
    }
}
