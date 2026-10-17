<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BukuController extends Controller
{
    /**
     * Display a listing of books (with Eager Loading, withCount peminjamans, and Pagination).
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $kategoriId = $request->query('kategori_id');

        if ($request->filled('search')) {
            session(['last_buku_search' => $request->search]);
        }

        $bukus = Buku::with('kategori')
            ->withCount('peminjamans')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('judul', 'like', "%{$search}%")
                      ->orWhere('penulis', 'like', "%{$search}%")
                      ->orWhere('penerbit', 'like', "%{$search}%")
                      ->orWhere('isbn', 'like', "%{$search}%");
                });
            })
            ->when($kategoriId, function ($query, $kategoriId) {
                $query->where('kategori_id', $kategoriId);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $kategoris = Kategori::orderBy('nama_kategori')->get();

        // Rekapitulasi Statistik Perpustakaan Menggunakan Fluent Query Builder (DB::table)
        $stats = [
            'total_judul' => DB::table('bukus')->count(),
            'total_stok' => (int) DB::table('bukus')->sum('stok'),
            'total_dipinjam' => DB::table('peminjamans')->where('status', 'dipinjam')->count(),
            'total_dikembalikan' => DB::table('peminjamans')->where('status', 'dikembalikan')->count(),
        ];

        return view('buku.index', compact('bukus', 'kategoris', 'search', 'kategoriId', 'stats'));
    }

    /**
     * Show the form for creating a new book.
     */
    public function create(): View
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();
        return view('buku.create', compact('kategoris'));
    }

    /**
     * Store a newly created book in storage (Server-Side Validation).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'isbn' => 'required|string|min:10|max:20|unique:bukus,isbn',
            'judul' => 'required|string|min:5|max:255',
            'penulis' => 'required|string|min:3|max:150',
            'penerbit' => 'required|string|min:3|max:150',
            'tahun_terbit' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'kategori_id' => 'required|exists:kategoris,id',
            'stok' => 'required|integer|min:0',
            'sinopsis' => 'nullable|string',
        ], [
            'isbn.required' => 'Nomor ISBN wajib diisi.',
            'isbn.unique' => 'Nomor ISBN sudah terdaftar.',
            'isbn.min' => 'ISBN minimal 10 karakter.',
            'isbn.max' => 'ISBN maksimal 20 karakter.',
            'judul.required' => 'Judul buku wajib diisi.',
            'judul.min' => 'Judul buku minimal 5 karakter.',
            'judul.max' => 'Judul buku maksimal 255 karakter.',
            'penulis.required' => 'Nama penulis wajib diisi.',
            'penulis.min' => 'Nama penulis minimal 3 karakter.',
            'penulis.max' => 'Nama penulis maksimal 150 karakter.',
            'penerbit.required' => 'Nama penerbit wajib diisi.',
            'penerbit.min' => 'Nama penerbit minimal 3 karakter.',
            'penerbit.max' => 'Nama penerbit maksimal 150 karakter.',
            'tahun_terbit.required' => 'Tahun terbit wajib diisi.',
            'tahun_terbit.integer' => 'Tahun terbit harus berupa angka.',
            'tahun_terbit.min' => 'Tahun terbit minimal 1900.',
            'tahun_terbit.max' => 'Tahun terbit tidak boleh melebihi tahun depan.',
            'kategori_id.required' => 'Kategori buku wajib dipilih.',
            'kategori_id.exists' => 'Kategori yang dipilih tidak valid.',
            'stok.required' => 'Jumlah stok buku wajib diisi.',
            'stok.integer' => 'Stok harus berupa bilangan bulat.',
            'stok.min' => 'Stok minimal 0 (tidak boleh negatif).',
        ]);

        $buku = Buku::create($validated);

        return redirect()
            ->route('buku.index')
            ->with('success', "Buku '{$buku->judul}' berhasil ditambahkan ke katalog!");
    }

    /**
     * Display the specified book (Loads category relation).
     */
    public function show(Buku $buku): View
    {
        $buku->load('kategori');
        return view('buku.show', compact('buku'));
    }

    /**
     * Show the form for editing the specified book.
     */
    public function edit(Buku $buku): View
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();
        return view('buku.edit', compact('buku', 'kategoris'));
    }

    /**
     * Update the specified book in storage (Server-Side Validation).
     */
    public function update(Request $request, Buku $buku): RedirectResponse
    {
        $validated = $request->validate([
            'isbn' => 'required|string|min:10|max:20|unique:bukus,isbn,' . $buku->id,
            'judul' => 'required|string|min:5|max:255',
            'penulis' => 'required|string|min:3|max:150',
            'penerbit' => 'required|string|min:3|max:150',
            'tahun_terbit' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'kategori_id' => 'required|exists:kategoris,id',
            'stok' => 'required|integer|min:0',
            'sinopsis' => 'nullable|string',
        ], [
            'isbn.required' => 'Nomor ISBN wajib diisi.',
            'isbn.unique' => 'Nomor ISBN sudah terdaftar pada buku lain.',
            'isbn.min' => 'ISBN minimal 10 karakter.',
            'isbn.max' => 'ISBN maksimal 20 karakter.',
            'judul.required' => 'Judul buku wajib diisi.',
            'judul.min' => 'Judul buku minimal 5 karakter.',
            'judul.max' => 'Judul buku maksimal 255 karakter.',
            'penulis.required' => 'Nama penulis wajib diisi.',
            'penulis.min' => 'Nama penulis minimal 3 karakter.',
            'penulis.max' => 'Nama penulis maksimal 150 karakter.',
            'penerbit.required' => 'Nama penerbit wajib diisi.',
            'penerbit.min' => 'Nama penerbit minimal 3 karakter.',
            'penerbit.max' => 'Nama penerbit maksimal 150 karakter.',
            'tahun_terbit.required' => 'Tahun terbit wajib diisi.',
            'tahun_terbit.integer' => 'Tahun terbit harus berupa angka.',
            'tahun_terbit.min' => 'Tahun terbit minimal 1900.',
            'tahun_terbit.max' => 'Tahun terbit tidak boleh melebihi tahun depan.',
            'kategori_id.required' => 'Kategori buku wajib dipilih.',
            'kategori_id.exists' => 'Kategori yang dipilih tidak valid.',
            'stok.required' => 'Jumlah stok buku wajib diisi.',
            'stok.integer' => 'Stok harus berupa bilangan bulat.',
            'stok.min' => 'Stok minimal 0 (tidak boleh negatif).',
        ]);

        $buku->update($validated);

        return redirect()
            ->route('buku.index')
            ->with('success', "Buku '{$buku->judul}' berhasil diperbarui!");
    }

    /**
     * Remove the specified book from storage.
     */
    public function destroy(Buku $buku): RedirectResponse
    {
        $judul = $buku->judul;
        $buku->delete();

        return redirect()
            ->route('buku.index')
            ->with('success', "Buku '{$judul}' berhasil dihapus dari katalog!");
    }
}
