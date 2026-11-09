<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PeminjamanApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;
    private Kategori $kategori;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Pustakawan Del',
            'email' => 'pustakawan@del.ac.id',
            'password' => Hash::make('password123'),
        ]);
        $this->token = $this->user->createToken('test')->plainTextToken;

        $this->kategori = Kategori::create([
            'kode_kategori' => 'KAT-TEK',
            'nama_kategori' => 'Teknologi Informasi',
        ]);
    }

    public function test_get_peminjamans_requires_token(): void
    {
        $this->getJson('/api/v1/peminjamans')->assertStatus(401);

        $authRes = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/peminjamans');
        $authRes->assertStatus(200);
        $authRes->assertJsonStructure(['success', 'message', 'data', 'meta']);
    }

    public function test_borrow_book_success_and_decrements_stock(): void
    {
        $buku = Buku::create([
            'isbn' => '978-111',
            'judul' => 'Clean Architecture',
            'penulis' => 'Robert C Martin',
            'penerbit' => 'Prentice Hall',
            'tahun_terbit' => 2018,
            'kategori_id' => $this->kategori->id,
            'stok' => 3,
        ]);

        $res = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/peminjamans', [
                'buku_id' => $buku->id,
                'nim_peminjam' => '12S24038',
                'nama_peminjam' => 'Nicolas J Grace Butarbutar',
                'durasi_hari' => 14,
            ]);

        $res->assertStatus(201);
        $this->assertStringContainsString('/api/v1/peminjamans/', $res->headers->get('Location'));
        $this->assertEquals('dipinjam', $res->json('data.status'));
        $this->assertEquals('12S24038', $res->json('data.nim_peminjam'));

        // Stock decreased to 2
        $this->assertEquals(2, $buku->fresh()->stok);
    }

    public function test_borrow_book_rejected_when_stock_exhausted(): void
    {
        $buku = Buku::create([
            'isbn' => '978-222',
            'judul' => 'Refactoring',
            'penulis' => 'Martin Fowler',
            'penerbit' => 'Addison-Wesley',
            'tahun_terbit' => 2019,
            'kategori_id' => $this->kategori->id,
            'stok' => 0,
        ]);

        $res = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/peminjamans', [
                'buku_id' => $buku->id,
                'nim_peminjam' => '12S24038',
                'nama_peminjam' => 'Nicolas J Grace Butarbutar',
            ]);

        $res->assertStatus(422);
        $res->assertHeader('Content-Type', 'application/problem+json');
        $this->assertEquals(422, $res->json('status'));
        $this->assertEquals(0, $buku->fresh()->stok);
    }

    public function test_borrow_book_rejected_on_duplicate_active_loan(): void
    {
        $buku = Buku::create([
            'isbn' => '978-333',
            'judul' => 'Design Patterns',
            'penulis' => 'GoF',
            'penerbit' => 'Addison-Wesley',
            'tahun_terbit' => 1994,
            'kategori_id' => $this->kategori->id,
            'stok' => 5,
        ]);

        // First loan succeeds
        $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/peminjamans', [
                'buku_id' => $buku->id,
                'nim_peminjam' => '12S24038',
                'nama_peminjam' => 'Nicolas J Grace Butarbutar',
            ])->assertStatus(201);

        $this->assertEquals(4, $buku->fresh()->stok);

        // Second loan for same book and student -> 409 Conflict
        $duplicateRes = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/peminjamans', [
                'buku_id' => $buku->id,
                'nim_peminjam' => '12S24038',
                'nama_peminjam' => 'Nicolas J Grace Butarbutar',
            ]);

        $duplicateRes->assertStatus(409);
        $duplicateRes->assertHeader('Content-Type', 'application/problem+json');
        $this->assertEquals(4, $buku->fresh()->stok); // Stock not decremented
    }

    public function test_return_book_restores_stock_and_prevents_duplicate_return(): void
    {
        $buku = Buku::create([
            'isbn' => '978-444',
            'judul' => 'Pragmatic Programmer',
            'penulis' => 'Hunt & Thomas',
            'penerbit' => 'Addison-Wesley',
            'tahun_terbit' => 2019,
            'kategori_id' => $this->kategori->id,
            'stok' => 1,
        ]);

        $borrowRes = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/peminjamans', [
                'buku_id' => $buku->id,
                'nim_peminjam' => '12S24038',
                'nama_peminjam' => 'Nicolas J Grace',
            ]);
        $loanId = $borrowRes->json('data.id');
        $this->assertEquals(0, $buku->fresh()->stok);

        // Return book -> 200 OK + stock restored
        $returnRes = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->patchJson("/api/v1/peminjamans/{$loanId}/kembalikan");
        $returnRes->assertStatus(200);
        $this->assertEquals('dikembalikan', $returnRes->json('data.status'));
        $this->assertEquals(1, $buku->fresh()->stok);

        // Second return attempt -> 409 Conflict
        $secondReturnRes = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->patchJson("/api/v1/peminjamans/{$loanId}/kembalikan");
        $secondReturnRes->assertStatus(409);
        $this->assertEquals(1, $buku->fresh()->stok);
    }
}
