# Panduan Testing API Maintenance (Postman)

Dokumen ini menjelaskan langkah-langkah untuk melakukan pengujian API Maintenance menggunakan Postman, terutama untuk menangani upload file dan nested array.

## 1. Persiapan Umum
- **Authorization:** Pastikan menyertakan `Bearer Token` pada tab **Auth** atau **Headers** (`Authorization: Bearer <your_token>`).
- **Headers:** Pastikan `Accept: application/json` terpasang.

---

## 2. Pembuatan Maintenance (Store)
- **Method:** `POST`
- **URL:** `{{base_url}}/api/maintenances`
- **Body Type:** `form-data`

### Field Header:
| Key | Value | Tipe |
| :--- | :--- | :--- |
| `item_id` | UUID Aset | Text |
| `title` | Perbaikan AC | Text |
| `priority` | `high` / `medium` / `low` | Text |
| `type` | `korektif` / `preventif` | Text |
| `description` | Deskripsi kerusakan | Text |
| `estimated_day` | 2 | Text |
| `target_completion_expectations` | 2026-06-25 | Text |
| `total_estimated_cost` | 500000 | Text |

### Field Nested Items (Array):
Gunakan indeks `[n]` untuk mengirim data array:
| Key | Value | Tipe |
| :--- | :--- | :--- |
| `items[0][nama_item]` | Jasa Service | Text |
| `items[0][qty]` | 1 | Text |
| `items[0][satuan]` | unit | Text |
| `items[0][estimasi_biaya_satuan]` | 150000 | Text |
| `items[0][file]` | (Pilih Gambar) | **File** |
| `items[1][nama_item]` | Penggantian Kompresor | Text |
| `items[1][qty]` | 1 | Text |
| `items[1][estimasi_biaya_satuan]` | 350000 | Text |

---

## 3. Pembaruan Maintenance (Update)
Karena menggunakan `multipart/form-data` (untuk upload file), Laravel membutuhkan teknik **Method Spoofing**.

- **Method:** `POST` (Bukan PUT)
- **URL:** `{{base_url}}/api/maintenances/{uuid}`
- **Body Type:** `form-data`

### Field Wajib:
| Key | Value | Keterangan |
| :--- | :--- | :--- |
| `_method` | `PUT` | **Penting!** Memberitahu Laravel untuk memproses sebagai PUT |

### Cara Update Items:
- **Update Item Lama:** Sertakan `id` item tersebut.
  - `items[0][id]` = `1`
  - `items[0][nama_item]` = `Nama Baru`
- **Tambah Item Baru:** Jangan sertakan `id`.
  - `items[1][nama_item]` = `Item Baru`
- **Hapus Item:** Cukup jangan sertakan item tersebut dalam list payload `items`.

---

## 4. Update Status (Approval & SPK)
- **Method:** `POST`
- **URL:** `{{base_url}}/api/maintenances/{uuid}/status`
- **Body Type:** `form-data` atau `raw (JSON)`

### Skenario in_progress (Terbit SPK):
| Key | Value |
| :--- | :--- |
| `status` | `in_progress` |
| `note` | Disetujui |
| `tanggal_mulai_efektif` | 2026-06-16 |
| `tanggal_selesai_target` | 2026-06-20 |
| `pagu_anggaran_disetujui` | 500000 |

---

## 5. Tips Debugging
1. Jika mendapatkan error `405 Method Not Allowed` saat Update, pastikan Anda menggunakan **POST** dengan field `_method=PUT`.
2. Jika file tidak terupload, pastikan tipe key pada Postman sudah diubah dari **Text** ke **File** (muncul saat kursor diarahkan ke sisi kanan field key).
3. Untuk melihat perubahan status, cek tabel `approval_logs` atau endpoint Show Detail.
