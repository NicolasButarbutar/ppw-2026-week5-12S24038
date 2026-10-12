<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Database\Seeder;

class BukuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoriMap = Kategori::pluck('id', 'kode_kategori');

        $books = [
            [
                'isbn' => '978-602-291-765-1',
                'judul' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
                'penulis' => 'Robert C. Martin',
                'penerbit' => 'Prentice Hall',
                'tahun_terbit' => 2018,
                'kode_kategori' => 'KAT-RPL',
                'stok' => 8,
                'sinopsis' => 'Buku pegangan fundamental bagi para pengembang perangkat lunak untuk menulis kode yang bersih, mudah dipelihara, modular, dan terstruktur dengan baik.',
            ],
            [
                'isbn' => '978-602-623-289-2',
                'judul' => 'Arsitektur Perangkat Lunak Modern Berbasis Cloud & Microservices',
                'penulis' => 'Chandro Pardede, M.Sc.',
                'penerbit' => 'Del Press',
                'tahun_terbit' => 2024,
                'kode_kategori' => 'KAT-RPL',
                'stok' => 12,
                'sinopsis' => 'Pembahasan komprehensif mengenai perancangan sistem terdistribusi, komunikasi antar-layanan (event-driven), dan penerapan design pattern modern pada arsitektur web.',
            ],
            [
                'isbn' => '978-013-449-416-6',
                'judul' => 'Clean Architecture: A Craftsman\'s Guide to Software Structure',
                'penulis' => 'Robert C. Martin',
                'penerbit' => 'Prentice Hall',
                'tahun_terbit' => 2019,
                'kode_kategori' => 'KAT-RPL',
                'stok' => 6,
                'sinopsis' => 'Panduan mendalam mengenai pemisahan lapisan aplikasi, aturan dependensi, dan pembentukan batas arsitektur dalam ekosistem pemrograman modern.',
            ],
            [
                'isbn' => '978-623-010-890-4',
                'judul' => 'Desain Pola Pemrograman (Design Patterns) Terapan PHP & Laravel',
                'penulis' => 'Budi Raharjo',
                'penerbit' => 'Informatika Bandung',
                'tahun_terbit' => 2023,
                'kode_kategori' => 'KAT-RPL',
                'stok' => 10,
                'sinopsis' => 'Kajian praktis implementasi Creational, Structural, dan Behavioral Design Patterns menggunakan framework Laravel dan ekosistem PHP modern.',
            ],
            [
                'isbn' => '978-602-038-721-1',
                'judul' => 'Sistem Informasi Manajemen: Perspektif Strategis & Operasional',
                'penulis' => 'Kenneth C. Laudon & Jane P. Laudon',
                'penerbit' => 'Pearson Education / Erlangga',
                'tahun_terbit' => 2021,
                'kode_kategori' => 'KAT-SI',
                'stok' => 15,
                'sinopsis' => 'Kajian strategis mengenai peranan teknologi informasi dalam meningkatkan keunggulan bersaing organisasi dan transformasi digital perusahaan.',
            ],
            [
                'isbn' => '978-623-713-145-8',
                'judul' => 'Analisis dan Perancangan Sistem Informasi Berorientasi Objek dengan UML',
                'penulis' => 'Rosa A.S. & M. Shalahuddin',
                'penerbit' => 'Informatika Bandung',
                'tahun_terbit' => 2022,
                'kode_kategori' => 'KAT-SI',
                'stok' => 9,
                'sinopsis' => 'Metodologi rekayasa kebutuhan sistem, perancangan diagram Use Case, Activity, Sequence, serta Class Diagram dalam siklus SDLC.',
            ],
            [
                'isbn' => '978-602-151-499-3',
                'judul' => 'Rekayasa Basis Data Relasional & Manajemen SQL Tingkat Lanjut',
                'penulis' => 'Abdul Kadir',
                'penerbit' => 'Andi Offset',
                'tahun_terbit' => 2020,
                'kode_kategori' => 'KAT-SI',
                'stok' => 14,
                'sinopsis' => 'Teknik normalisasi database, optimasi kueri kompleks, perancangan indeks, integritas referensial data, dan transaksi ACID.',
            ],
            [
                'isbn' => '978-013-359-414-0',
                'judul' => 'Computer Networks: Principles, Protocols, and Architectures',
                'penulis' => 'Andrew S. Tanenbaum & David J. Wetherall',
                'penerbit' => 'Pearson Education',
                'tahun_terbit' => 2021,
                'kode_kategori' => 'KAT-JKN',
                'stok' => 7,
                'sinopsis' => 'Buku standar dunia mengenai protokol jaringan komputer, layer OSI/TCP-IP, algoritma routing, dan infrastruktur transmisi data.',
            ],
            [
                'isbn' => '978-623-010-123-3',
                'judul' => 'Keamanan Jaringan & Ethical Hacking Praktis',
                'penulis' => 'Onno W. Purbo',
                'penerbit' => 'Andi Offset',
                'tahun_terbit' => 2022,
                'kode_kategori' => 'KAT-JKN',
                'stok' => 11,
                'sinopsis' => 'Eksplorasi teknik pengujian penetrasi (penetration testing), audit keamanan server, proteksi firewall, enkripsi data, dan penanganan insiden siber.',
            ],
            [
                'isbn' => '978-149-195-884-1',
                'judul' => 'Practical Packet Analysis: Using Wireshark to Solve Real-World Network Problems',
                'penulis' => 'Chris Sanders',
                'penerbit' => 'O\'Reilly Media',
                'tahun_terbit' => 2020,
                'kode_kategori' => 'KAT-JKN',
                'stok' => 5,
                'sinopsis' => 'Panduan visual mendalam untuk membedah paket lalu lintas jaringan, troubleshooting koneksi, dan mendeteksi anomali transmisi.',
            ],
            [
                'isbn' => '978-026-203-561-3',
                'judul' => 'Deep Learning: Fundamentals and Applications',
                'penulis' => 'Ian Goodfellow, Yoshua Bengio, & Aaron Courville',
                'penerbit' => 'MIT Press',
                'tahun_terbit' => 2018,
                'kode_kategori' => 'KAT-AI',
                'stok' => 8,
                'sinopsis' => 'Buku teks komprehensif mengenai teori dasar neural networks, backpropagation, CNN, RNN, generative models, dan matematika machine learning.',
            ],
            [
                'isbn' => '978-149-203-264-9',
                'judul' => 'Hands-On Machine Learning with Scikit-Learn, Keras, and TensorFlow',
                'penulis' => 'Aurélien Géron',
                'penerbit' => 'O\'Reilly Media',
                'tahun_terbit' => 2023,
                'kode_kategori' => 'KAT-AI',
                'stok' => 12,
                'sinopsis' => 'Panduan terapan langkah-demi-langkah membangun pipeline data science, pemodelan prediktif, evaluasi model, dan deployment model AI.',
            ],
            [
                'isbn' => '978-602-623-245-8',
                'judul' => 'Dasar Pemrograman Python untuk Sains Data & Big Data Analytics',
                'penulis' => 'Jubilee Enterprise',
                'penerbit' => 'Elex Media Komputindo',
                'tahun_terbit' => 2022,
                'kode_kategori' => 'KAT-AI',
                'stok' => 16,
                'sinopsis' => 'Pengenalan pustaka Python populer seperti NumPy, Pandas, Matplotlib, dan Seaborn untuk analisis dan visualisasi data skala besar.',
            ],
            [
                'isbn' => '978-012-811-905-1',
                'judul' => 'Computer Organization and Embedded Systems Architecture',
                'penulis' => 'Carl Hamacher, Zvonko Vranesic, & Safwat Zaky',
                'penerbit' => 'McGraw-Hill',
                'tahun_terbit' => 2020,
                'kode_kategori' => 'KAT-TKOM',
                'stok' => 6,
                'sinopsis' => 'Studi struktur internal prosesor, arsitektur memori hirarkis, bus data, instruksi assembly RISC-V, dan antarmuka perangkat I/O.',
            ],
            [
                'isbn' => '978-623-713-102-1',
                'judul' => 'Pemrograman Mikrokontroler & IoT Berbasis ESP32 dan Arduino',
                'penulis' => 'M. Syahwil',
                'penerbit' => 'Informatika Bandung',
                'tahun_terbit' => 2023,
                'kode_kategori' => 'KAT-TKOM',
                'stok' => 13,
                'sinopsis' => 'Panduan merakit dan memprogram perangkat sensor cerdas, komunikasi MQTT, konektivitas WiFi/Bluetooth, dan otomasi industri berbasis IoT.',
            ],
            [
                'isbn' => '978-144-934-037-7',
                'judul' => 'Designing Embedded Hardware: Create Compact, Robust Electronic Systems',
                'penulis' => 'John Catsoulis',
                'penerbit' => 'O\'Reilly Media',
                'tahun_terbit' => 2019,
                'kode_kategori' => 'KAT-TKOM',
                'stok' => 5,
                'sinopsis' => 'Dasar perancangan rangkaian sirkuit terpadu, manajemen daya, interfacing SPI/I2C/UART, dan debugging embedded firmware.',
            ],
            [
                'isbn' => '978-007-338-309-5',
                'judul' => 'Discrete Mathematics and Its Applications',
                'penulis' => 'Kenneth H. Rosen',
                'penerbit' => 'McGraw-Hill Education',
                'tahun_terbit' => 2019,
                'kode_kategori' => 'KAT-HUM',
                'stok' => 18,
                'sinopsis' => 'Pondasi matematika diskrit untuk ilmu komputer mencakup logika proposisional, teori himpunan, relasi fungsi, kombinatorika, dan teori graf.',
            ],
            [
                'isbn' => '978-602-446-218-5',
                'judul' => 'Etika Profesi dan Aspek Hukum Teknologi Informasi',
                'penulis' => 'Prof. Dr. Richardus Eko Indrajit',
                'penerbit' => 'Andi Offset',
                'tahun_terbit' => 2021,
                'kode_kategori' => 'KAT-HUM',
                'stok' => 10,
                'sinopsis' => 'Kode etik profesional TI, hak kekayaan intelektual (HAKI), privasi data konsumen, serta regulasi UU ITE di era transformasi digital.',
            ],
            [
                'isbn' => '978-623-010-445-6',
                'judul' => 'Metodologi Penelitian Bidang Ilmu Komputer & Sistem Informasi',
                'penulis' => 'Dr. Romi Satria Wahono, M.Eng.',
                'penerbit' => 'Gava Media',
                'tahun_terbit' => 2022,
                'kode_kategori' => 'KAT-HUM',
                'stok' => 15,
                'sinopsis' => 'Teknik perumusan masalah penelitian, studi literatur terstruktur, metode kuantitatif/kualitatif, dan penulisan karya ilmiah bereputasi.',
            ],
            [
                'isbn' => '978-602-064-211-2',
                'judul' => 'Komunikasi Interpersonal & Kepemimpinan Tim Teknologi',
                'penulis' => 'Dale Carnegie Institute',
                'penerbit' => 'Gramedia Pustaka Utama',
                'tahun_terbit' => 2023,
                'kode_kategori' => 'KAT-HUM',
                'stok' => 12,
                'sinopsis' => 'Pengembangan soft skills, komunikasi asertif, resolusi konflik, dan manajemen kolaborasi tim dalam proyek teknologi berkecepatan tinggi.',
            ],
            [
                'isbn' => '978-149-204-079-8',
                'judul' => 'Building Microservices: Designing Fine-Grained Systems',
                'penulis' => 'Sam Newman',
                'penerbit' => 'O\'Reilly Media',
                'tahun_terbit' => 2022,
                'kode_kategori' => 'KAT-RPL',
                'stok' => 7,
                'sinopsis' => 'Strategi dekomposisi monolit, integrasi data terdistribusi, CI/CD pipeline, service mesh, dan monitoring layanan berskala enterprise.',
            ],
            [
                'isbn' => '978-013-595-705-9',
                'judul' => 'The Pragmatic Programmer: Your Journey to Mastery (20th Anniversary Edition)',
                'penulis' => 'David Thomas & Andrew Hunt',
                'penerbit' => 'Addison-Wesley Professional',
                'tahun_terbit' => 2020,
                'kode_kategori' => 'KAT-RPL',
                'stok' => 11,
                'sinopsis' => 'Filosofi pragmatis dalam rekayasa perangkat lunak, otomatisasi alur kerja, proteksi bug dini, dan mentalitas continuous improvement.',
            ],
        ];

        foreach ($books as $item) {
            $catId = $kategoriMap[$item['kode_kategori']] ?? null;
            if ($catId) {
                Buku::updateOrCreate(
                    ['isbn' => $item['isbn']],
                    [
                        'judul' => $item['judul'],
                        'penulis' => $item['penulis'],
                        'penerbit' => $item['penerbit'],
                        'tahun_terbit' => $item['tahun_terbit'],
                        'kategori_id' => $catId,
                        'stok' => $item['stok'],
                        'sinopsis' => $item['sinopsis'],
                    ]
                );
            }
        }
    }
}
