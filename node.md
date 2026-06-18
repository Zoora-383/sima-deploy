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

## 5. Hasil Audit Kode & Bug Report (Juni 2026)

Berikut adalah daftar temuan bug, potensi celah keamanan, dan inkonsistensi yang ditemukan selama audit sistem:

### A. Critical & Logic Bugs [FIXED]
1.  **Race Condition (Sequence Generation):**
    - **Status:** FIXED. Menambahkan `lockForUpdate()` di `SPKService` dan memastikan `unique()` constraint di migration.
2.  **Inconsistent Category Update:**
    - **Status:** FIXED. Mengoreksi pengecekan dari `category` menjadi `category_uuid` di `ItemService`.
3.  **Missing Transaction on Auth Refresh:**
    - **Status:** FIXED. Menambahkan `DB::transaction` pada `AuthService::refresh`.

### B. Database & Schema Issues [UPDATED]
1.  **Missing Index/Unique Constraint:**
    - **Status:** FIXED. Menambahkan `unique()` pada field `uuid` tabel `items`.
2.  **Missing Unique Constraint on Sequence Numbers:**
    - **Status:** FIXED. Menambahkan `unique()` pada field `nomor_pengajuan` di `maintenance_requests`.

### C. Security & API Issues [FIXED]
1.  **Inconsistent Pagination:**
    - **Status:** FIXED. Menambahkan paginasi di `ItemService::getAllItem` agar konsisten dengan `UserService`.
2.  **Fragile S3 Path Extraction:**
    - **Status:** FIXED. Menggunakan `Storage::disk('s3')->url('')` sebagai basis untuk ekstraksi path yang lebih aman di `MaintenanceService` dan `UserService`.
3.  **Silent Failure in Profile Update:**
    - **Status:** FIXED. Menggunakan `updateOrCreate()` pada `UserService::updateUser` untuk menjamin record profil dibuat jika belum ada.

### D. Coding Standards & Maintenance [UPDATED]
1.  **Stale Documentation:**
    - **Status:** FIXED. Memperbarui docblock di `MaintenanceService::updateStatus`.
2.  **Inconsistent Endpoint Naming:**
    - **Status:** PENDING. Memerlukan diskusi desain lebih lanjut untuk standarisasi kebab-case vs plural/singular.

---

## 6. Laporan Security Audit & Penetration Test (Simulasi)

Saya telah melakukan simulasi "Serangan Backend" dengan mengaudit logika kode terhadap OWASP Top 10. Berikut adalah hasil "kebobolan" dan celah yang ditemukan:

### A. Vulnerability: Sensitive Data Exposure (High Risk)
- **Temuan:** Akun Enumeration.
- **Detail:** Pada `AuthService::login`, pesan error membedakan antara "Email incorrect" dan "Password incorrect".
- **Exploit:** Hacker dapat melakukan *brute-force* untuk mengumpulkan daftar email valid pengguna sistem hanya dengan melihat perbedaan pesan error tersebut.
- **Dampak:** Mempermudah serangan *phishing* atau *credential stuffing* karena hacker sudah tahu email mana yang terdaftar.

### B. Vulnerability: Insecure Direct Object Reference (IDOR) (Medium Risk)
- **Temuan:** Global Admin Ownership.
- **Detail:** Pada modul `Items` dan `Maintenance`, pengecekan hanya dilakukan di level Role (`role:admin`). Tidak ada pengecekan kepemilikan aset (Ownership).
- **Exploit:** Seorang Admin dari Departemen A dapat mengubah atau menghapus pengajuan Maintenance milik Departemen B hanya dengan mengetahui UUID-nya.
- **Dampak:** Integritas data antar departemen bisa terganggu jika UUID bocor atau tertebak (lewat logs).

### C. Vulnerability: Broken Access Control (Medium Risk)
- **Temuan:** Self-Deactivation DoS.
- **Detail:** Pada `UserService::updateUserStatus`, tidak ada proteksi bagi Super Admin untuk menonaktifkan akunnya sendiri.
- **Exploit:** Jika hacker berhasil masuk ke satu akun Super Admin, dia bisa menonaktifkan SEMUA akun Super Admin lainnya (termasuk dirinya sendiri) untuk mengunci sistem secara total.
- **Dampak:** *Permanent Denial of Service* pada panel administrasi hingga database diperbaiki manual.

### D. Vulnerability: Unrestricted File Upload (Low-Medium Risk)
- **Temuan:** Logic Bypass via Metadata.
- **Detail:** Validasi file di `ProfileUpdateRequest` hanya menggunakan `mimes:jpeg,png...`. Meskipun cukup kuat, sistem tidak melakukan *re-encoding* gambar.
- **Exploit:** Hacker dapat menyisipkan *malicious payload* (PHP code) di dalam metadata (EXIF) sebuah gambar valid. Jika server web salah konfigurasi dalam mengeksekusi file di folder `avatars`, ini bisa menjadi *Remote Code Execution*.
- **Dampak:** Potensi pengambilalihan server jika konfigurasi S3/Web server tidak ketat.

### E. Vulnerability: Broken Access Control (Horizontal)
- **Temuan:** `updateStatus` Loophole.
- **Detail:** Pada `MaintenanceService::updateStatus`, role `kasi` bisa menyetujui draft ke `pending_pust`. Namun, jika seorang `kasi` mengirimkan status `in_progress` lewat API manual, sistem hanya mengandalkan pengecekan `$roleTransitions`. Jika array ini ada kesalahan satu baris saja, proteksi jebol.

---
*Rekomendasi Utama: Ubah pesan login menjadi "Invalid credentials" dan tambahkan Ownership check pada setiap resource.*


