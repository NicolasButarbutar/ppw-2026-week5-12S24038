# Panduan Penempatan Screenshot Postman (Minggu 9)

Seluruh file tangkapan layar (*screenshot*) Postman Desktop tersimpan di direktori `docs/screenshots/` ini dan otomatis tertaut pada `README.md` utama repositori:

| No | Nama File Gambar | Skenario Pengujian Wajib (Poin 5.3.5 & 6.3) | Status |
|---|---|---|---|
| 1 | `01-auth-login.png` | **Skenario 1**: Autentikasi Login Pustakawan & Bearer Token Sanctum (`POST /api/v1/auth/login`) | ✅ Selesai |
| 2 | `02-katalog-buku-hateoas.png` | **Skenario 2**: Katalog Buku Publik Terpaginasi & HATEOAS Dinamis (`GET /api/v1/bukus`) | ✅ Selesai |
| 3 | `03-peminjaman-acid-success.png` | **Skenario 3**: Transaksi Peminjaman Buku ACID Sukses 201 Created (`POST /api/v1/peminjamans`) | ✅ Selesai |
| 4 | `04-error-rfc7807-conflict.png` | **Skenario 4**: Error Handling RFC 7807 (409 Conflict Duplikasi Peminjaman Aktif) | ✅ Selesai |
| 5 | `05-pengembalian-buku.png` | **Skenario 5**: Pengembalian Buku & Pemulihan Kuota Stok Atomik (`PATCH /api/v1/peminjamans/{id}/kembalikan`) | ✅ Selesai |
| 6 | `06-external-openlibrary.png` | **Skenario 6**: Integrasi Eksternal OpenLibrary REST API (`GET /api/v1/bukus/{id}/referensi-eksternal`) | ✅ Selesai |
| 7 | `07-soap-service-clearance.png` | **Skenario 7**: SOAP Web Service Legacy Bebas Pustaka (`POST /soap/sipus`) | ✅ Selesai |

> **Tips:** Anda dapat menggunakan tombol shortcut `Windows + Shift + S` untuk memotong screenshot dari jendela Postman, lalu menyimpannya langsung ke folder ini.
