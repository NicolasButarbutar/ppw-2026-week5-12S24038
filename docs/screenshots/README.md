# Panduan Penempatan Screenshot Postman (Minggu 9)

Simpan file tangkapan layar (*screenshot*) Postman Desktop Anda ke dalam folder `docs/screenshots/` ini dengan nama file berikut agar otomatis muncul pada `README.md` utama repositori:

| No | Nama File Gambar | Skenario Pengujian Wajib (Poin 5.3.5) |
|---|---|---|
| 1 | `01-soap-clearance.png` | **(a) Uji SOAP XML & `<soap:Fault>`** (Request `POST /soap/sipus` berhasil dan gagal) |
| 2 | `02-login-bearer-token.png` | **(b) Login Bearer Token & Profil** (`POST /api/v1/auth/login` & `GET /api/v1/auth/me`) |
| 3 | `03-get-katalog-hateoas.png` | **(c) GET Katalog Terpaginasi & HATEOAS** (`GET /api/v1/bukus?tersedia=1`) |
| 4 | `04-post-buku-location.png` | **(d) POST Buku 201 Created + Header Location** (`POST /api/v1/bukus`) |
| 5 | `05-put-vs-patch.png` | **(e) Bukti Nyata Perbedaan PUT vs PATCH** (`PUT /api/v1/bukus/1` vs `PATCH /api/v1/bukus/1`) |
| 6 | `06-peminjaman-acid.png` | **(f) Peminjaman Sukses & Penolakan Stok Habis (422 / 409)** (`POST /api/v1/peminjamans`) |
| 7 | `07-github-consumer.png` | **(g) Bukti Latihan GitHub REST API & OpenLibrary** (`PATCH /user` token sensor & `GET /referensi-eksternal`) |

> **Tips:** Anda dapat menggunakan tombol shortcut `Windows + Shift + S` untuk memotong screenshot dari jendela Postman, lalu menyimpannya langsung ke folder ini.
