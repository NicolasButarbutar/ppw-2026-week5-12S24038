<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SipusDelTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_buku_index(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('buku.index'));
    }

    public function test_guest_is_redirected_to_login_on_mutating_routes(): void
    {
        $response = $this->get(route('kategori.create'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('buku.create'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('peminjaman.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('peminjaman.create'));
        $response->assertRedirect(route('login'));
    }

    public function test_kategori_crud_lifecycle(): void
    {
        $librarian = User::factory()->create();
        $this->actingAs($librarian);

        // 1. Create form
        $response = $this->get(route('kategori.create'));
        $response->assertStatus(200);
        $response->assertSee('Formulir Tambah Kategori');

        // 2. Store validation fail
        $response = $this->post(route('kategori.store'), [
            'kode_kategori' => '',
            'nama_kategori' => 'ab',
        ]);
        $response->assertSessionHasErrors(['kode_kategori', 'nama_kategori']);

        // 3. Store success
        $response = $this->post(route('kategori.store'), [
            'kode_kategori' => 'KAT-TEST',
            'nama_kategori' => 'Kategori Uji Coba',
        ]);
        $response->assertRedirect(route('kategori.index'));
        $this->assertDatabaseHas('kategoris', ['kode_kategori' => 'KAT-TEST']);

        $kategori = Kategori::where('kode_kategori', 'KAT-TEST')->first();

        // 4. Index
        $response = $this->get(route('kategori.index'));
        $response->assertStatus(200);
        $response->assertSee('Kategori Uji Coba');

        // 5. Show
        $response = $this->get(route('kategori.show', $kategori));
        $response->assertStatus(200);
        $response->assertSee('Kategori Uji Coba');

        // 6. Edit
        $response = $this->get(route('kategori.edit', $kategori));
        $response->assertStatus(200);
        $response->assertSee('KAT-TEST');

        // 7. Update
        $response = $this->put(route('kategori.update', $kategori), [
            'kode_kategori' => 'KAT-TEST-UPD',
            'nama_kategori' => 'Kategori Diperbarui',
        ]);
        $response->assertRedirect(route('kategori.index'));
        $this->assertDatabaseHas('kategoris', ['kode_kategori' => 'KAT-TEST-UPD']);

        // 8. Destroy
        $kategori->refresh();
        $response = $this->delete(route('kategori.destroy', $kategori));
        $response->assertRedirect(route('kategori.index'));
        $this->assertDatabaseMissing('kategoris', ['id' => $kategori->id]);
    }

    public function test_buku_crud_lifecycle_and_validation(): void
    {
        $librarian = User::factory()->create();
        $this->actingAs($librarian);

        $kategori = Kategori::create([
            'kode_kategori' => 'KAT-RPL',
            'nama_kategori' => 'Rekayasa Perangkat Lunak',
        ]);

        // 1. Create form
        $response = $this->get(route('buku.create'));
        $response->assertStatus(200);
        $response->assertSee('Formulir Tambah Koleksi Buku');

        // 2. Validation error test (title min:5, invalid isbn, negative stock)
        $response = $this->post(route('buku.store'), [
            'isbn' => '',
            'judul' => 'abc', // min:5
            'penulis' => '',
            'penerbit' => '',
            'tahun_terbit' => '1800', // min:1900
            'kategori_id' => 999, // not exists
            'stok' => -5, // min:0
        ]);
        $response->assertSessionHasErrors(['isbn', 'judul', 'penulis', 'penerbit', 'tahun_terbit', 'kategori_id', 'stok']);

        // 3. Store valid buku
        $bukuData = [
            'isbn' => '978-602-1234-56-7',
            'judul' => 'Pemrograman Web Modern dengan Laravel 11',
            'penulis' => 'Dosen Del',
            'penerbit' => 'Del Press',
            'tahun_terbit' => 2026,
            'kategori_id' => $kategori->id,
            'stok' => 15,
            'sinopsis' => 'Buku praktikum komprehensif implementasi MVC dan Eloquent.',
        ];

        $response = $this->post(route('buku.store'), $bukuData);
        $response->assertRedirect(route('buku.index'));
        $this->assertDatabaseHas('bukus', ['isbn' => '978-602-1234-56-7']);

        $buku = Buku::where('isbn', '978-602-1234-56-7')->first();

        // 4. Show buku
        $response = $this->get(route('buku.show', $buku));
        $response->assertStatus(200);
        $response->assertSee('Pemrograman Web Modern dengan Laravel 11');
        $response->assertSee('Rekayasa Perangkat Lunak');

        // 5. Edit buku
        $response = $this->get(route('buku.edit', $buku));
        $response->assertStatus(200);
        $response->assertSee('978-602-1234-56-7');

        // 6. Update buku (with unique isbn check allowing same isbn for this record)
        $response = $this->put(route('buku.update', $buku), [
            'isbn' => '978-602-1234-56-7',
            'judul' => 'Pemrograman Web Modern dengan Laravel 11 (Edisi Revisi)',
            'penulis' => 'Dosen Del & Tim Asisten',
            'penerbit' => 'Del Press',
            'tahun_terbit' => 2026,
            'kategori_id' => $kategori->id,
            'stok' => 20,
            'sinopsis' => 'Edisi revisi terbaru praktikum web.',
        ]);
        $response->assertRedirect(route('buku.index'));
        $this->assertDatabaseHas('bukus', [
            'id' => $buku->id,
            'judul' => 'Pemrograman Web Modern dengan Laravel 11 (Edisi Revisi)',
            'stok' => 20,
        ]);

        // 7. Delete buku
        $buku->refresh();
        $response = $this->delete(route('buku.destroy', $buku));
        $response->assertRedirect(route('buku.index'));
        $this->assertDatabaseMissing('bukus', ['id' => $buku->id]);
    }

    public function test_eager_loading_prevents_n_plus_one_problem(): void
    {
        $kategori = Kategori::create([
            'kode_kategori' => 'KAT-RPL',
            'nama_kategori' => 'Rekayasa Perangkat Lunak',
        ]);

        // Create 15 books
        for ($i = 1; $i <= 15; $i++) {
            Buku::create([
                'isbn' => "978-602-0000-$i",
                'judul' => "Buku Uji Coba Ke-$i",
                'penulis' => "Penulis $i",
                'penerbit' => "Penerbit $i",
                'tahun_terbit' => 2024,
                'kategori_id' => $kategori->id,
                'stok' => $i,
                'sinopsis' => "Sinopsis buku ke-$i",
            ]);
        }

        // Count queries executed when rendering index view
        $queryCount = 0;
        DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        $response = $this->get(route('buku.index'));
        $response->assertStatus(200);

        // Catalog query (pagination count + limit 10 items + eager load kategoris + dropdown kategoris = 4)
        // Analytics Dashboard stats (4 DB::table queries: count, sum, count dipinjam, count dikembalikan = 4)
        // Total query remains strictly constant O(1) regardless of number of books (8 queries total), preventing N+1.
        $this->assertLessThanOrEqual(9, $queryCount, "Eager Loading failed: expected <= 9 queries, got {$queryCount}");
    }

    /*
    |--------------------------------------------------------------------------
    | WEEK 6 TESTS: AUTHENTICATION, SESSIONS, COOKIES, & ACID TRANSACTIONS
    |--------------------------------------------------------------------------
    */

    public function test_librarian_login_success_with_session_regeneration_and_cookie(): void
    {
        $user = User::create([
            'name' => 'Pustakawan Del',
            'email' => 'pustakawan@del.ac.id',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'pustakawan@del.ac.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('buku.index'));
        $this->assertAuthenticatedAs($user);
        $response->assertSessionHas('login_timestamp');
        $response->assertCookie('del_last_user_email', 'pustakawan@del.ac.id');
    }

    public function test_librarian_login_fails_with_invalid_credentials(): void
    {
        $response = $this->from(route('login'))->post(route('login.post'), [
            'email' => 'pustakawan@del.ac.id',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_librarian_logout_invalidates_session_and_clears_auth(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('logout'));
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_acid_book_borrowing_decrements_stock_and_creates_loan(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $kategori = Kategori::create([
            'kode_kategori' => 'KAT-AI',
            'nama_kategori' => 'Artificial Intelligence',
        ]);

        $buku = Buku::create([
            'isbn' => '978-602-999-001-7',
            'judul' => 'Kecerdasan Buatan Modern',
            'penulis' => 'Dosen AI',
            'penerbit' => 'Del Press',
            'tahun_terbit' => 2025,
            'kategori_id' => $kategori->id,
            'stok' => 5,
        ]);

        $response = $this->post(route('peminjaman.store'), [
            'buku_id' => $buku->id,
            'nim_peminjam' => '12S24038',
            'nama_peminjam' => 'Nicolas J Grace Butarbutar',
            'durasi_hari' => 7,
        ]);

        $response->assertRedirect(route('peminjaman.index'));
        $response->assertSessionHas('success');

        // Mutasi atomik: Stok berkurang dari 5 menjadi 4
        $this->assertEquals(4, $buku->fresh()->stok);

        // Catatan peminjaman tersimpan
        $this->assertDatabaseHas('peminjamans', [
            'buku_id' => $buku->id,
            'nim_peminjam' => '12S24038',
            'nama_peminjam' => 'Nicolas J Grace Butarbutar',
            'status' => 'dipinjam',
        ]);
    }

    public function test_acid_book_borrowing_fails_and_rollbacks_when_stock_is_zero(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $kategori = Kategori::create([
            'kode_kategori' => 'KAT-RPL',
            'nama_kategori' => 'Rekayasa Perangkat Lunak',
        ]);

        $buku = Buku::create([
            'isbn' => '978-602-999-000-0',
            'judul' => 'Sistem Terdistribusi Lanjutan',
            'penulis' => 'Chandro Pardede',
            'penerbit' => 'Del Press',
            'tahun_terbit' => 2024,
            'kategori_id' => $kategori->id,
            'stok' => 0, // Stok Habis!
        ]);

        $response = $this->post(route('peminjaman.store'), [
            'buku_id' => $buku->id,
            'nim_peminjam' => '12S24038',
            'nama_peminjam' => 'Nicolas J Grace Butarbutar',
            'durasi_hari' => 7,
        ]);

        // Ditolak dan di-rollback
        $response->assertSessionHas('error');
        $this->assertEquals(0, $buku->fresh()->stok);
        $this->assertDatabaseMissing('peminjamans', [
            'buku_id' => $buku->id,
            'nim_peminjam' => '12S24038',
        ]);
    }

    public function test_acid_book_borrowing_fails_if_same_nim_has_unreturned_book(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $kategori = Kategori::create([
            'kode_kategori' => 'KAT-RPL',
            'nama_kategori' => 'Rekayasa Perangkat Lunak',
        ]);

        $buku = Buku::create([
            'isbn' => '978-602-111-222-3',
            'judul' => 'Clean Architecture',
            'penulis' => 'Robert C. Martin',
            'penerbit' => 'Prentice Hall',
            'tahun_terbit' => 2021,
            'kategori_id' => $kategori->id,
            'stok' => 3,
        ]);

        // Peminjaman pertama berhasil
        $this->post(route('peminjaman.store'), [
            'buku_id' => $buku->id,
            'nim_peminjam' => '12S24038',
            'nama_peminjam' => 'Nicolas J Grace Butarbutar',
        ]);

        $this->assertEquals(2, $buku->fresh()->stok);

        // Peminjaman kedua oleh NIM yang sama untuk buku yang sama (harus ditolak!)
        $response = $this->post(route('peminjaman.store'), [
            'buku_id' => $buku->id,
            'nim_peminjam' => '12S24038',
            'nama_peminjam' => 'Nicolas J Grace Butarbutar',
        ]);

        $response->assertSessionHas('error');
        // Stok tetap 2 (tidak berkurang lagi)
        $this->assertEquals(2, $buku->fresh()->stok);
    }

    public function test_acid_book_return_increments_stock_and_updates_status(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $kategori = Kategori::create([
            'kode_kategori' => 'KAT-RPL',
            'nama_kategori' => 'Rekayasa Perangkat Lunak',
        ]);

        $buku = Buku::create([
            'isbn' => '978-602-111-222-4',
            'judul' => 'Refactoring',
            'penulis' => 'Martin Fowler',
            'penerbit' => 'Addison-Wesley',
            'tahun_terbit' => 2020,
            'kategori_id' => $kategori->id,
            'stok' => 1,
        ]);

        $peminjaman = Peminjaman::create([
            'kode_pinjam' => 'PINJ-TEST-001',
            'buku_id' => $buku->id,
            'nim_peminjam' => '12S24038',
            'nama_peminjam' => 'Nicolas J Grace Butarbutar',
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
            'status' => 'dipinjam',
        ]);

        $response = $this->post(route('peminjaman.kembali', $peminjaman->id));
        $response->assertSessionHas('success');

        // Mutasi atomik: Stok buku bertambah dari 1 menjadi 2
        $this->assertEquals(2, $buku->fresh()->stok);

        // Status peminjaman diperbarui
        $peminjaman->refresh();
        $this->assertEquals('dikembalikan', $peminjaman->status);
        $this->assertNotNull($peminjaman->tanggal_kembali);
    }
}
