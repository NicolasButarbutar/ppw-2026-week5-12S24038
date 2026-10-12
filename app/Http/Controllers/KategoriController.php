<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KategoriController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $kategoris = Kategori::withCount('bukus')
            ->when($search, function ($query, $search) {
                $query->where('nama_kategori', 'like', "%{$search}%")
                      ->orWhere('kode_kategori', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('kategori.index', compact('kategoris', 'search'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): View
    {
        return view('kategori.create');
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_kategori' => 'required|string|max:20|unique:kategoris,kode_kategori',
            'nama_kategori' => 'required|string|min:3|max:100',
        ], [
            'kode_kategori.required' => 'Kode kategori wajib diisi.',
            'kode_kategori.unique' => 'Kode kategori sudah terdaftar.',
            'kode_kategori.max' => 'Kode kategori maksimal 20 karakter.',
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.min' => 'Nama kategori minimal 3 karakter.',
            'nama_kategori.max' => 'Nama kategori maksimal 100 karakter.',
        ]);

        $kategori = Kategori::create($validated);

        return redirect()
            ->route('kategori.index')
            ->with('success', "Kategori '{$kategori->nama_kategori}' berhasil ditambahkan!");
    }

    /**
     * Display the specified category with related books.
     */
    public function show(Kategori $kategori): View
    {
        $kategori->load(['bukus' => function ($query) {
            $query->latest();
        }]);

        return view('kategori.show', compact('kategori'));
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Kategori $kategori): View
    {
        return view('kategori.edit', compact('kategori'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Kategori $kategori): RedirectResponse
    {
        $validated = $request->validate([
            'kode_kategori' => 'required|string|max:20|unique:kategoris,kode_kategori,' . $kategori->id,
            'nama_kategori' => 'required|string|min:3|max:100',
        ], [
            'kode_kategori.required' => 'Kode kategori wajib diisi.',
            'kode_kategori.unique' => 'Kode kategori sudah terdaftar pada kategori lain.',
            'kode_kategori.max' => 'Kode kategori maksimal 20 karakter.',
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.min' => 'Nama kategori minimal 3 karakter.',
            'nama_kategori.max' => 'Nama kategori maksimal 100 karakter.',
        ]);

        $kategori->update($validated);

        return redirect()
            ->route('kategori.index')
            ->with('success', "Kategori '{$kategori->nama_kategori}' berhasil diperbarui!");
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Kategori $kategori): RedirectResponse
    {
        $nama = $kategori->nama_kategori;
        $kategori->delete();

        return redirect()
            ->route('kategori.index')
            ->with('success', "Kategori '{$nama}' beserta buku terkait berhasil dihapus!");
    }
}
