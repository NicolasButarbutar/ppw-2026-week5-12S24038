<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PeminjamanController extends Controller
{
    /**
     * Menampilkan daftar transaksi peminjaman & pengembalian buku.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $peminjamans = Peminjaman::with('buku')
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('kode_pinjam', 'like', "%{$search}%")
                      ->orWhere('nim_peminjam', 'like', "%{$search}%")
                      ->orWhere('nama_peminjam', 'like', "%{$search}%")
                      ->orWhereHas('buku', function ($bq) use ($search) {
                          $bq->where('judul', 'like', "%{$search}%");
                      });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('peminjaman.index', compact('peminjamans', 'status', 'search'));
    }

    /**
     * Menampilkan formulir transaksi peminjaman buku.
     */
    public function create(Request $request): View
    {
        $selectedBukuId = $request->query('buku_id');
        $bukus = Buku::orderBy('judul')->get();

        return view('peminjaman.create', compact('bukus', 'selectedBukuId'));
    }

    /**
     * Memproses transaksi peminjaman buku secara ACID dengan Concurrency Locking.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'buku_id' => 'required|exists:bukus,id',
            'nim_peminjam' => 'required|string|min:6|max:20',
            'nama_peminjam' => 'required|string|min:3|max:150',
            'durasi_hari' => 'nullable|integer|min:1|max:30',
        ], [
            'buku_id.required' => 'Pilih buku yang hendak dipinjam.',
            'buku_id.exists' => 'Buku yang dipilih tidak ditemukan dalam katalog.',
            'nim_peminjam.required' => 'NIM peminjam wajib diisi.',
            'nim_peminjam.min' => 'NIM peminjam minimal 6 karakter.',
            'nim_peminjam.max' => 'NIM peminjam maksimal 20 karakter.',
            'nama_peminjam.required' => 'Nama lengkap peminjam wajib diisi.',
            'nama_peminjam.min' => 'Nama peminjam minimal 3 karakter.',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                // 1. Lock record buku dengan lockForUpdate untuk mencegah Race Condition
                $buku = Buku::where('id', $validated['buku_id'])->lockForUpdate()->firstOrFail();

                // 2. Periksa stok buku > 0 (Tolak & Rollback jika stok = 0)
                if ($buku->stok < 1) {
                    throw new \RuntimeException("Stok buku '{$buku->judul}' habis (0 eksemplar). Transaksi peminjaman dibatalkan (Rollback)!");
                }

                // 3. Mencegah NIM yang sama meminjam buku yang sama yang belum dikembalikan
                $nimFormatted = strtoupper(trim($validated['nim_peminjam']));
                $existingLoan = Peminjaman::where('buku_id', $buku->id)
                    ->where('nim_peminjam', $nimFormatted)
                    ->where('status', 'dipinjam')
                    ->lockForUpdate()
                    ->first();

                if ($existingLoan) {
                    throw new \RuntimeException("Mahasiswa dengan NIM {$nimFormatted} masih meminjam buku ini (Kode: {$existingLoan->kode_pinjam}) dan belum mengembalikannya.");
                }

                // 4. Mutasi atomik: kurangi stok buku
                $buku->decrement('stok');

                // 5. Generate kode pinjam unik dan tentukan tanggal
                $durasi = isset($validated['durasi_hari']) ? (int) $validated['durasi_hari'] : 7;
                $tglPinjam = now()->toDateString();
                $tglJatuhTempo = now()->addDays($durasi)->toDateString();
                $kodePinjam = 'PINJ-' . date('Ymd') . '-' . strtoupper(Str::random(5));

                // 6. Simpan transaksi peminjaman
                Peminjaman::create([
                    'kode_pinjam' => $kodePinjam,
                    'buku_id' => $buku->id,
                    'nim_peminjam' => $nimFormatted,
                    'nama_peminjam' => trim($validated['nama_peminjam']),
                    'tanggal_pinjam' => $tglPinjam,
                    'tanggal_jatuh_tempo' => $tglJatuhTempo,
                    'status' => 'dipinjam',
                ]);

                // Simpan state sesi peminjam terakhir
                session([
                    'last_borrower_nim' => $nimFormatted,
                    'last_borrower_name' => trim($validated['nama_peminjam']),
                ]);
            }, 3);

            return redirect()
                ->route('peminjaman.index')
                ->with('success', 'Transaksi peminjaman buku berhasil dicatat secara atomik (ACID)!');
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[DB EXCEPTION - PEMINJAMAN]: ' . $e->getMessage(), [
                'request_data' => $request->all(),
            ]);
            return back()->withInput()->with('error', 'Terjadi kegagalan basis data saat memproses peminjaman.');
        }
    }

    /**
     * Memproses transaksi pengembalian buku secara ACID dengan Concurrency Locking.
     */
    public function kembalikan(int $id): RedirectResponse
    {
        try {
            DB::transaction(function () use ($id) {
                // 1. Lock transaksi peminjaman
                $peminjaman = Peminjaman::where('id', $id)->lockForUpdate()->firstOrFail();

                if ($peminjaman->status === 'dikembalikan') {
                    throw new \RuntimeException("Buku dengan kode peminjaman {$peminjaman->kode_pinjam} sudah pernah dikembalikan sebelumnya.");
                }

                // 2. Lock record buku terkait
                $buku = Buku::where('id', $peminjaman->buku_id)->lockForUpdate()->firstOrFail();

                // 3. Ubah status peminjaman dan catat tanggal kembali
                $peminjaman->update([
                    'status' => 'dikembalikan',
                    'tanggal_kembali' => now()->toDateString(),
                ]);

                // 4. Mutasi atomik: pulihkan stok buku (+1)
                $buku->increment('stok');
            }, 3);

            return back()->with('success', 'Buku berhasil dikembalikan secara atomik dan stok telah dipulihkan (+1)!');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[DB EXCEPTION - PENGEMBALIAN]: ' . $e->getMessage(), [
                'peminjaman_id' => $id,
            ]);
            return back()->with('error', 'Terjadi kegagalan basis data saat memproses pengembalian buku.');
        }
    }
}
