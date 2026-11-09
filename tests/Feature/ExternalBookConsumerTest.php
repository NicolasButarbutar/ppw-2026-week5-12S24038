<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExternalBookConsumerTest extends TestCase
{
    use RefreshDatabase;

    private Kategori $kategori;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kategori = Kategori::create([
            'kode_kategori' => 'KAT-TEK',
            'nama_kategori' => 'Teknologi Informasi',
        ]);
    }

    public function test_referensi_eksternal_success(): void
    {
        $buku = Buku::create([
            'isbn' => '0201633612',
            'judul' => 'Design Patterns',
            'penulis' => 'Erich Gamma',
            'penerbit' => 'Addison-Wesley',
            'tahun_terbit' => 1994,
            'kategori_id' => $this->kategori->id,
            'stok' => 3,
        ]);

        Http::fake([
            'openlibrary.org/*' => Http::response([
                'ISBN:0201633612' => [
                    'url' => 'https://openlibrary.org/books/OL123M',
                    'title' => 'Design Patterns: Elements of Reusable Object-Oriented Software',
                    'subtitle' => 'Elements of Reusable Object-Oriented Software',
                    'number_of_pages' => 395,
                    'publish_date' => '1994',
                    'publishers' => [
                        ['name' => 'Addison-Wesley Professional'],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson("/api/v1/bukus/{$buku->id}/referensi-eksternal");

        $response->assertStatus(200);
        $this->assertEquals('OpenLibrary REST API', $response->json('data.sumber'));
        $this->assertEquals(395, $response->json('data.jumlah_halaman'));
        $this->assertContains('Addison-Wesley Professional', $response->json('data.penerbit'));
    }

    public function test_referensi_eksternal_empty_metadata(): void
    {
        $buku = Buku::create([
            'isbn' => '9999999999999',
            'judul' => 'Buku Fiktif Tanpa Metadata',
            'penulis' => 'Anonim',
            'penerbit' => 'Del Press',
            'tahun_terbit' => 2026,
            'kategori_id' => $this->kategori->id,
            'stok' => 2,
        ]);

        Http::fake([
            'openlibrary.org/*' => Http::response([], 200),
        ]);

        $response = $this->getJson("/api/v1/bukus/{$buku->id}/referensi-eksternal");

        $response->assertStatus(200);
        $this->assertNull($response->json('data.metadata'));
        $this->assertStringContainsString('Data tidak ditemukan', $response->json('data.status_pencarian'));
    }

    public function test_referensi_eksternal_timeout_handled_gracefully(): void
    {
        $buku = Buku::create([
            'isbn' => '0201633612',
            'judul' => 'Design Patterns',
            'penulis' => 'Erich Gamma',
            'penerbit' => 'Addison-Wesley',
            'tahun_terbit' => 1994,
            'kategori_id' => $this->kategori->id,
            'stok' => 3,
        ]);

        Http::fake([
            'openlibrary.org/*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection timeout to OpenLibrary');
            },
        ]);

        $response = $this->getJson("/api/v1/bukus/{$buku->id}/referensi-eksternal");

        $response->assertStatus(504);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $this->assertEquals(504, $response->json('status'));
        $this->assertEquals('Gateway Timeout / Network Error', $response->json('title'));
    }
}
