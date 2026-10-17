<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Database\Seeder;

class PeminjamanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bukuCleanCode = Buku::where('isbn', '978-602-291-765-1')->first();
        $bukuCloud = Buku::where('isbn', '978-602-623-289-2')->first();
        $bukuDesignPattern = Buku::where('isbn', '978-623-010-890-4')->first();

        if ($bukuCleanCode) {
            Peminjaman::updateOrCreate(
                ['kode_pinjam' => 'PINJ-20261010-001'],
                [
                    'buku_id' => $bukuCleanCode->id,
                    'nim_peminjam' => '12S24038',
                    'nama_peminjam' => 'Nicolas J Grace Butarbutar',
                    'tanggal_pinjam' => now()->subDays(3)->toDateString(),
                    'tanggal_jatuh_tempo' => now()->addDays(4)->toDateString(),
                    'tanggal_kembali' => null,
                    'status' => 'dipinjam',
                ]
            );
        }

        if ($bukuCloud) {
            Peminjaman::updateOrCreate(
                ['kode_pinjam' => 'PINJ-20261001-002'],
                [
                    'buku_id' => $bukuCloud->id,
                    'nim_peminjam' => '12S24038',
                    'nama_peminjam' => 'Nicolas J Grace Butarbutar',
                    'tanggal_pinjam' => now()->subDays(12)->toDateString(),
                    'tanggal_jatuh_tempo' => now()->subDays(5)->toDateString(),
                    'tanggal_kembali' => now()->subDays(6)->toDateString(),
                    'status' => 'dikembalikan',
                ]
            );
        }

        if ($bukuDesignPattern) {
            Peminjaman::updateOrCreate(
                ['kode_pinjam' => 'PINJ-20261012-003'],
                [
                    'buku_id' => $bukuDesignPattern->id,
                    'nim_peminjam' => '12S24001',
                    'nama_peminjam' => 'Andi Pratama',
                    'tanggal_pinjam' => now()->subDays(1)->toDateString(),
                    'tanggal_jatuh_tempo' => now()->addDays(6)->toDateString(),
                    'tanggal_kembali' => null,
                    'status' => 'dipinjam',
                ]
            );
        }
    }
}
