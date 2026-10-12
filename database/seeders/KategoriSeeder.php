<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoris = [
            [
                'kode_kategori' => 'KAT-RPL',
                'nama_kategori' => 'Rekayasa Perangkat Lunak',
            ],
            [
                'kode_kategori' => 'KAT-SI',
                'nama_kategori' => 'Sistem Informasi & Bisnis Digital',
            ],
            [
                'kode_kategori' => 'KAT-JKN',
                'nama_kategori' => 'Jaringan Komputer & Cyber Security',
            ],
            [
                'kode_kategori' => 'KAT-AI',
                'nama_kategori' => 'Kecerdasan Buatan & Sains Data',
            ],
            [
                'kode_kategori' => 'KAT-TKOM',
                'nama_kategori' => 'Teknik Komputer & Embedded Systems',
            ],
            [
                'kode_kategori' => 'KAT-HUM',
                'nama_kategori' => 'Sains Dasar & Humaniora',
            ],
        ];

        foreach ($kategoris as $kategori) {
            Kategori::updateOrCreate(
                ['kode_kategori' => $kategori['kode_kategori']],
                ['nama_kategori' => $kategori['nama_kategori']]
            );
        }
    }
}
