# SimaApi-App - Project Documentation & System Flow
---

## 1. Ringkasan Proyek (Overview)

SimaApi-App adalah sistem manajemen aset dan workflow pemeliharaan (maintenance) yang dirancang dengan standar keamanan tinggi dan audit trail yang lengkap.

### Core Tech Stack:
- **Backend:** Laravel 13.x (PHP 8.x)
- **Authentication:** JWT (JSON Web Token) dengan validasi akun aktif (`is_active`).
- **Identifier:** UUID sebagai Public Identifier (menggantikan ID integer).
- **Architecture:** Controller -> Service -> Model (Clean Architecture).

---

## 2. Alur Kerja Aplikasi (Application Flow)

Sistem ini memiliki tiga modul utama yang saling terintegrasi: **User Management**, **Item Management**, dan **Maintenance Workflow**.

### A. Alur Inventaris (Item Management)
Setiap item/aset yang didaftarkan harus melalui proses validasi kualitas sebelum aktif:
1. **Admin (Requester):** Membuat data item (Status: `draft`).
2. **Kasi (Approver 1):** Memvalidasi item dan meneruskannya ke Kepala Pustakawan (Status: `pending_pust`) atau mengembalikannya ke Admin (Status: `revision`).
3. **Kepala Pustakawan (Final Approver):** Memberikan persetujuan akhir (Status: `active`). Hanya item berstatus `active` yang dapat digunakan dalam proses operasional/maintenance.

### B. Alur Pemeliharaan & SPK (Maintenance & SPK Flow)
Ini adalah alur paling kritis yang mengintegrasikan pengajuan perbaikan dengan penerbitan Surat Perintah Kerja (SPK):
1. **Pengajuan:** Admin mengajukan request maintenance untuk item yang rusak (Status: `draft`).
2. **Verifikasi Kasi:** Kasi memeriksa urgensi dan detail pengajuan (Status: `pending_pust`).
3. **Persetujuan & Penerbitan SPK Otomatis:**
   - **Kepala Pustakawan** melakukan review final.
   - Saat menyetujui (`status: in_progress`), Kepala Pustakawan **wajib** menginput estimasi biaya dan jadwal pengerjaan.
   - **Sistem secara atomik** akan:
     - Mengubah status Maintenance menjadi `in_progress`.
     - Menerbitkan record **SPK baru** yang terhubung ke pengajuan tersebut.
     - Mencatat audit trail di `approval_logs`.
4. **Penyelesaian:** Setelah pekerjaan selesai, Admin/Teknisi mengubah status menjadi `done`.

### C. Alur Keamanan & Audit Trail
- **Middleware JWT:** Setiap request divalidasi integritas tokennya. Jika user dinonaktifkan oleh Super Admin, akses akan langsung diputus (`403 Forbidden`).
- **Audit Trail (RecordApprovalLog):** Setiap perpindahan status (misal: `pending_kasi` -> `pending_pust`) dicatat secara otomatis dalam tabel `approval_logs` beserta catatan (*note*) dan aktor yang melakukannya.

---

## 3. Struktur Peran & Izin (RBAC)

| Role | Tanggung Jawab Utama |
| :--- | :--- |
| **Super Admin** | Manajemen User, Role, dan Reset Password sistem secara keseluruhan. |
| **Admin** | Operasional harian: Input Item, Pengajuan Maintenance, Update Profil. |
| **Kasi** | Verifikator Level 1: Review Item baru dan Review pengajuan Maintenance awal. |
| **Kel_Pust** | Final Approver: Aktivasi Item dan Penentu Anggaran/Jadwal SPK Maintenance. |

---

## 4. Standar Kode & Struktur API

- **RESTful API:** Menggunakan metode HTTP yang tepat (`GET`, `POST`, `PUT`, `PATCH`, `DELETE`).
- **Resource Mapping:** Semua response dibungkus menggunakan `Eloquent Resources` untuk menyembunyikan detail database.
- **Atomic Transaction:** Pembuatan Maintenance dan SPK dibungkus dalam `DB::beginTransaction` untuk mencegah data korup jika terjadi error di tengah proses.

---
*Terakhir diperbarui: Juni 2026*
