<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoapTest extends TestCase
{
    use RefreshDatabase;

    public function test_wsdl_endpoint_returns_xml_contract(): void
    {
        $response = $this->get('/soap/sipus.wsdl');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/xml; charset=utf-8');
        $this->assertStringContainsString('LibrarySoapService', $response->getContent());
        $this->assertStringContainsString('CheckClearanceStatus', $response->getContent());
    }

    public function test_soap_clearance_status_cleared(): void
    {
        $xmlRequest = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:m="http://sipus.del.ac.id/soap/library">
    <soap:Body>
        <m:CheckClearanceStatusRequest>
            <m:nim_peminjam>12S24038</m:nim_peminjam>
        </m:CheckClearanceStatusRequest>
    </soap:Body>
</soap:Envelope>
XML;

        $response = $this->call('POST', '/soap/sipus', [], [], [], [
            'CONTENT_TYPE' => 'text/xml; charset=utf-8',
        ], $xmlRequest);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/xml; charset=utf-8');
        $this->assertStringContainsString('<m:is_cleared>true</m:is_cleared>', $response->getContent());
        $this->assertStringContainsString('<m:active_loans>0</m:active_loans>', $response->getContent());
    }

    public function test_soap_clearance_status_has_active_loan(): void
    {
        $kategori = Kategori::create(['kode_kategori' => 'KAT-TEK', 'nama_kategori' => 'Teknologi']);
        $buku = Buku::create([
            'isbn' => '978-602-03-8888-8',
            'judul' => 'Laravel SOA',
            'penulis' => 'Penulis',
            'penerbit' => 'Penerbit',
            'tahun_terbit' => 2026,
            'kategori_id' => $kategori->id,
            'stok' => 5,
        ]);
        Peminjaman::create([
            'kode_pinjam' => 'PINJ-SOAP-01',
            'buku_id' => $buku->id,
            'nim_peminjam' => '12S24038',
            'nama_peminjam' => 'Nicolas J Grace Butarbutar',
            'tanggal_pinjam' => now(),
            'tanggal_jatuh_tempo' => now()->addDays(7),
            'status' => 'dipinjam',
        ]);

        $xmlRequest = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:m="http://sipus.del.ac.id/soap/library">
    <soap:Body>
        <m:CheckClearanceStatusRequest>
            <m:nim_peminjam>12S24038</m:nim_peminjam>
        </m:CheckClearanceStatusRequest>
    </soap:Body>
</soap:Envelope>
XML;

        $response = $this->call('POST', '/soap/sipus', [], [], [], [
            'CONTENT_TYPE' => 'text/xml; charset=utf-8',
        ], $xmlRequest);

        $response->assertStatus(200);
        $this->assertStringContainsString('<m:is_cleared>false</m:is_cleared>', $response->getContent());
        $this->assertStringContainsString('<m:active_loans>1</m:active_loans>', $response->getContent());
    }

    public function test_soap_fault_on_empty_nim(): void
    {
        $xmlRequest = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:m="http://sipus.del.ac.id/soap/library">
    <soap:Body>
        <m:CheckClearanceStatusRequest>
            <m:nim_peminjam></m:nim_peminjam>
        </m:CheckClearanceStatusRequest>
    </soap:Body>
</soap:Envelope>
XML;

        $response = $this->call('POST', '/soap/sipus', [], [], [], [
            'CONTENT_TYPE' => 'text/xml; charset=utf-8',
        ], $xmlRequest);

        $response->assertStatus(500);
        $this->assertStringContainsString('<soap:Fault>', $response->getContent());
        $this->assertStringContainsString('soap:Client', $response->getContent());
    }
}
