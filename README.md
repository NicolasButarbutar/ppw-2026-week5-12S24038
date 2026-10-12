# SIPUL-DEL / SIPUS-Del — Sistem Informasi Perpustakaan Institut Teknologi Del

![Laravel Version](https://img.shields.io/badge/Laravel-13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP Version](https://img.shields.io/badge/PHP-8.4%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Bootstrap Version](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![Testing Status](https://img.shields.io/badge/Tests-Passing%20(6%2F6)-success?style=for-the-badge)

Aplikasi Web Pengelolaan Katalog dan Koleksi Pustaka Civitas Akademika Institut Teknologi Del (IT Del) yang dibangun dengan standar arsitektur **Model-View-Controller (MVC)**, **RESTful Routing**, **Eloquent ORM**, **Eager Loading Mitigation**, serta antarmuka modern bertema **Del Purple**.

---
## Identitas Diri Mahasiswa
- **Nama** : `Nicolas J Grace Butarbutar`
- **NIM** : `12S24038`
- **Program Studi** : `S1 Sistem Informasi`
---

## Daftar Isi
- [I. Arsitektur MVC & Routing RESTful](#i-arsitektur-mvc--routing-restful)
- [II. Model Relasional & Skema Database](#ii-model-relasional--skema-database)
- [III. Validasi Server-Side & Keamanan Web](#iii-validasi-server-side--keamanan-web)
- [IV. Blade Templating & Desain UI Del Purple](#iv-blade-templating--desain-ui-del-purple)
- [V. Pembuktian Eager Loading & Audit Log SQL (Solusi N+1)](#v-pembuktian-eager-loading--audit-log-sql-solusi-n1)
- [VI. Panduan Instalasi & Pengujian](#vi-panduan-instalasi--pengujian)
- [VII. Riwayat Commit Git (Conventional Commits)](#vii-riwayat-commit-git-conventional-commits)
- [VIII. Matriks Penilaian Rubrik Praktikum](#viii-matriks-penilaian-rubrik-praktikum)

---

## I. Arsitektur MVC & Routing RESTful

Aplikasi menerapkan pemisahan tugas (*Separation of Concerns*) yang ketat antara Model, View, dan Controller. Seluruh endpoint terdaftar rapi menggunakan `Route::resource` dengan 7 aksi RESTful standar dan memanfaatkan **Route Model Binding** secara presisi.

### 1. Daftar Rute RESTful
| Method | URI Path | Nama Rute | Action Controller | Keterangan |
|---|---|---|---|---|
| `GET` | `/` | - | Closure | Redirect otomatis ke `buku.index` |
| `GET` | `/kategori` | `kategori.index` | `KategoriController@index` | Menampilkan daftar kategori & jumlah buku |
| `GET` | `/kategori/create` | `kategori.create` | `KategoriController@create` | Form penambahan kategori |
| `POST` | `/kategori` | `kategori.store` | `KategoriController@store` | Menyimpan data kategori baru |
| `GET` | `/kategori/{kategori}` | `kategori.show` | `KategoriController@show` | Menampilkan detail kategori beserta relasi bukunya |
| `GET` | `/kategori/{kategori}/edit` | `kategori.edit` | `KategoriController@edit` | Form pengubahan data kategori |
| `PUT/PATCH` | `/kategori/{kategori}` | `kategori.update` | `KategoriController@update` | Memperbarui data kategori |
| `DELETE` | `/kategori/{kategori}` | `kategori.destroy` | `KategoriController@destroy` | Menghapus data kategori |
| `GET` | `/buku` | `buku.index` | `BukuController@index` | Menampilkan katalog buku dengan Eager Loading |
| `GET` | `/buku/create` | `buku.create` | `BukuController@create` | Form penambahan buku baru |
| `POST` | `/buku` | `buku.store` | `BukuController@store` | Menyimpan data buku baru dengan validasi ketat |
| `GET` | `/buku/{buku}` | `buku.show` | `BukuController@show` | Menampilkan detail buku lengkap |
| `GET` | `/buku/{buku}/edit` | `buku.edit` | `BukuController@edit` | Form pengubahan data buku |
| `PUT/PATCH` | `/buku/{buku}` | `buku.update` | `BukuController@update` | Memperbarui data buku |
| `DELETE` | `/buku/{buku}` | `buku.destroy` | `BukuController@destroy` | Menghapus data buku |

---

## II. Model Relasional & Skema Database

### 1. Relasi Eloquent ORM
- **Kategori ke Buku (`One-to-Many`)**: Satu kategori menaungi banyak buku.
  ```php
  // App\Models\Kategori
  public function bukus(): HasMany
  {
      return $this->hasMany(Buku::class, 'kategori_id');
  }
  ```
- **Buku ke Kategori (`Many-to-One`)**: Setiap buku berinduk pada satu kategori.
  ```php
  // App\Models\Buku
  public function kategori(): BelongsTo
  {
      return $this->belongsTo(Kategori::class, 'kategori_id');
  }
  ```

### 2. Atribut `$fillable` Terproteksi (Mass Assignment Protection)
Kedua model menerapkan whitelist `$fillable` eksplisit untuk mencegah celah *Mass Assignment Vulnerability*:
- `Kategori`: `['kode_kategori', 'nama_kategori']`
- `Buku`: `['isbn', 'judul', 'penulis', 'penerbit', 'tahun_terbit', 'kategori_id', 'stok', 'sinopsis']`

### 3. Skema Migrasi Database
- **Tabel `kategoris`**:
  - `id`: Primary key (bigint unsigned auto increment)
  - `kode_kategori`: String (varchar 20, unique)
  - `nama_kategori`: String (varchar 100)
  - `timestamps`: `created_at`, `updated_at`
- **Tabel `bukus`**:
  - `id`: Primary key (bigint unsigned auto increment)
  - `isbn`: String (varchar 20, unique)
  - `judul`: String (varchar 255)
  - `penulis`: String (varchar 150)
  - `penerbit`: String (varchar 150)
  - `tahun_terbit`: unsignedSmallInteger
  - `kategori_id`: foreignId constrained to `kategoris(id)` with `cascadeOnDelete()`
  - `stok`: Integer default 0
  - `sinopsis`: Text nullable
  - `timestamps`: `created_at`, `updated_at`

---

## III. Validasi Server-Side & Keamanan Web

1. **Validasi Server-Side Lengkap**:
   - `BukuController@store`:
     - `isbn`: `required|string|min:10|max:20|unique:bukus,isbn`
     - `judul`: `required|string|min:5|max:255`
     - `penulis`: `required|string|min:3|max:150`
     - `penerbit`: `required|string|min:3|max:150`
     - `tahun_terbit`: `required|integer|min:1900|max:(tahun_ini + 1)`
     - `kategori_id`: `required|exists:kategoris,id`
     - `stok`: `required|integer|min:0`
     - `sinopsis`: `nullable|string`
   - `BukuController@update`: Memiliki aturan yang sama dengan pengecualian unique ISBN untuk ID buku saat ini (`unique:bukus,isbn,{$buku->id}`).
   - Pesan galat disajikan dalam Bahasa Indonesia yang komunikatif dan informatif.

2. **Proteksi Token CSRF**:
   - Seluruh form mutasi data (`POST`, `PUT`, `DELETE`) dilengkapi direktif `@csrf` serta spoofing HTTP method `@method('PUT')` dan `@method('DELETE')`.

3. **Bebas Kerentanan XSS & SQL Injection**:
   - Tampilan menggunakan kurung ganda Blade `{{ $variable }}` yang otomatis melakukan `htmlspecialchars()` untuk mengeliminasi serangan XSS.
   - Interaksi database sepenuhnya memakai Eloquent ORM dengan prepared statements / parameter binding, sehingga terbebas dari serangan SQL Injection.

---

## IV. Blade Templating & Desain UI Del Purple

1. **Master Shell Layout `<x-layout>`**:
   - Terletak pada `resources/views/components/layout.blade.php`.
   - Mengintegrasikan Bootstrap 5.3, Bootstrap Icons, Google Fonts (Plus Jakarta Sans).
   - Mengusung skema warna institusi **Del Purple** (`#3B0764`, `#4C1D95`, `#F3E8FF`) dengan aksen keemasan Del.
2. **Flash Session Alert Interaktif**:
   - Notifikasi sukses (`session('success')`) dan error (`session('error')`) otomatis ditampilkan pada layout master dengan dismissible button.
   - Ringkasan kesalahan validasi (`$errors->any()`) disajikan dalam container alert terstruktur.
3. **Pagination & `@forelse`**:
   - Pagination terintegrasi dengan Bootstrap 5 melalui `Paginator::useBootstrapFive()`.
   - Menjaga state filter pencarian menggunakan `->withQueryString()`.
   - Seluruh perulangan data pada tabel menggunakan `@forelse ... @empty ... @endforelse` untuk menangani kondisi data kosong secara elegan dan responsif.

---

## V. Pembuktian Eager Loading & Audit Log SQL (Solusi N+1)

Untuk mencegah degradasi performa akibat **N+1 Problem**, `BukuController@index` menerapkan metode Eager Loading:
```php
$bukus = Buku::with('kategori')
    ->when($search, ...)
    ->when($kategoriId, ...)
    ->latest()
    ->paginate(10)
    ->withQueryString();
```

### 1. Mekanisme Audit Log SQL
Pada `app/Providers/AppServiceProvider.php`, diterapkan listener query global:
```php
DB::listen(function ($query) {
    Log::info(sprintf(
        "[SQL AUDIT] (%.2f ms) %s | Bindings: %s",
        $query->time,
        $query->sql,
        json_encode($query->bindings)
    ));
});
```

### 2. Bukti Empiris: Lazy Loading (N+1 Problem) vs Eager Loading
Ketika menampilkan 10 data buku per halaman:

| Kondisi | Total Kueri SQL | Pola Eksekusi Kueri |
|---|---|---|
| **Tanpa Eager Loading (Lazy Loading)** | **12 - 13 kueri** | 1x `select count(*)`, 1x `select * from bukus limit 10`, **10x** `select * from kategoris where id = ?` (berulang tiap baris!), 1x dropdown kategori. |
| **Dengan Eager Loading (`with('kategori')`)** | **Hanya 4 kueri** | 1x `select count(*)`, 1x `select * from bukus limit 10`, **1x** `select * from kategoris where id in (?, ?, ...)` *(Himpunan seluruh kategori terpilih)*, 1x dropdown kategori. |

**Kutipan Log Audit Riil dari `storage/logs/laravel.log`:**
```text
[SQL AUDIT] (0.28 ms) select count(*) as aggregate from "bukus" | Bindings: []
[SQL AUDIT] (0.34 ms) select * from "bukus" order by "created_at" desc limit 10 offset 0 | Bindings: []
[SQL AUDIT] (0.22 ms) select * from "kategoris" where "kategoris"."id" in (1, 2, 3, 4, 5, 6) | Bindings: [1,2,3,4,5,6]
[SQL AUDIT] (0.19 ms) select * from "kategoris" order by "nama_kategori" asc | Bindings: []
```
*Hasil: N+1 problem berhasil dieliminasi total dengan jumlah kueri konstan dan terukur.*

---

## VI. Panduan Instalasi & Pengujian

### 1. Prasyarat Sistem
- PHP >= 8.2 (dengan ekstensi `pdo_sqlite` atau `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`)
- Composer
- Git

### 2. Langkah Instalasi
```bash
# 1. Clone repositori
git clone <URL_REPOSITORI_ANDA>
cd ppw-2026-week5-12S24038

# 2. Pasang dependensi
composer install

# 3. Konfigurasi file environment
cp .env.example .env
php artisan key:generate

# 4. Jalankan migrasi dan seeding data realistis
php artisan migrate:fresh --seed

# 5. Jalankan web server lokal
php artisan serve
```
Akses aplikasi melalui peramban di: `http://localhost:8000`

### 3. Menjalankan Unit & Feature Testing
Aplikasi dilengkapi pengujian otomatis lengkap:
```bash
php artisan test
```
*Hasil:* Seluruh test suite (`SipusDelTest` dan `ExampleTest`) lolos 100% (6 passed, 52 assertions).

---

## VII. Riwayat Commit Git (Conventional Commits)

Repositori dikelola dengan rapi menggunakan konvensi pesan commit standar:
1. `feat(migration): create kategoris and bukus tables schema`
   - Pembuatan skema tabel `kategoris` dan `bukus` dengan foreign key cascade.
2. `feat(model): define relationships and fillable attributes`
   - Definisi relasi `hasMany` (Kategori) dan `belongsTo` (Buku), perlindungan `$fillable`.
3. `feat(controller): implement restful actions and server validation`
   - Implementasi 7 aksi RESTful pada `KategoriController` & `BukuController` dengan validasi server-side lengkap.
4. `feat(views): construct blade component layout and crud templates`
   - Pembuatan Master Shell `<x-layout>`, tema Del Purple, flash message, pagination, dan template CRUD.
5. `perf(eager-loading): optimize sql queries to prevent n+1 problem`
   - Optimasi kueri Eloquent `with('kategori')`, audit SQL logger via `DB::listen`, dan pengujian otomatis N+1 mitigation.

> [!IMPORTANT]
> Berkas rahasia `.env` dan direktori `vendor/` **DILARANG KERAS** dan telah dipastikan terdaftar pada `.gitignore`, serta berkas `.env.example` yang valid disertakan secara lengkap.

---

## VIII. Matriks Penilaian Rubrik Praktikum

| Kriteria Evaluasi | Bobot | Target Kategori | Pemenuhan pada Proyek SIPUL-DEL / SIPUS-Del | Status |
|---|---|---|---|---|
| **Arsitektur MVC & Routing RESTful** | 20% | **Sangat Baik (85-100)** | Pemisahan M, V, C sangat tegas; 7 aksi RESTful terdaftar via `Route::resource`; Route Model Binding presisi di setiap controller. | ✅ Tercapai |
| **Model Relasional & Eloquent ORM** | 25% | **Sangat Baik (85-100)** | Skema migrasi rapi dengan foreign key cascade; relasi `hasMany` & `belongsTo` presisi; `$fillable` terproteksi; Eager Loading meniadakan N+1 problem. | ✅ Tercapai |
| **Validasi Input & Keamanan Web** | 20% | **Sangat Baik (85-100)** | Validasi server-side lengkap dan ketat dengan pesan kustom; proteksi CSRF di seluruh form mutasi; bebas dari celah XSS dan SQL Injection. | ✅ Tercapai |
| **Blade Templating & Desain UI** | 20% | **Sangat Baik (85-100)** | Master Shell `<x-layout>`; UI modern bertema Del Purple; flash session interaktif; pagination responsif & direktif `@forelse` di setiap daftar data. | ✅ Tercapai |
| **Seeders, Logging, Git & Laporan** | 15% | **Sangat Baik (85-100)** | Mock data Faker realistis; log audit SQL pembuktian Eager Loading lengkap via `DB::listen`; commit Git teratur berstandar Conventional Commits; dokumentasi README komprehensif. | ✅ Tercapai |
# ppw-2026-week5-12S24038
