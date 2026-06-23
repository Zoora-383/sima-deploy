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
2. **Verifikasi Kasi:** Kasi memeriksa urgensi dan detail pengajuan (Status: `pending_pust`) atau mengembalikan untuk perbaikan (Status: `rejected`).
3. **Persetujuan & Penerbitan SPK Otomatis:**
    - **Kepala Pustakawan** melakukan review final.
    - Saat menyetujui (`status: in_progress`), sistem **secara atomik** akan:
        - Mengubah status Maintenance menjadi `in_progress`.
        - Menerbitkan record **SPK baru** (menggunakan data estimasi biaya dan jadwal dari request).
        - Mencatat audit trail di `approval_logs`.
4. **Penyelesaian & Rekap Otomatis:**
    - Setelah pekerjaan selesai, Admin mengubah status menjadi `done`.
    - **Sistem secara otomatis** menerbitkan record **Rekap Maintenance**.

### C. Alur Keamanan & Audit Trail

- **Middleware JWT:** Setiap request divalidasi integritas tokennya. Jika user dinonaktifkan, akses langsung diputus.
- **Ownership Enforcement:** Role `admin` hanya dapat mengakses, mengelola, dan melihat data (Items/Maintenance) yang mereka buat sendiri.
- **Secure Image Processing:** Semua file gambar yang diunggah melalui proses re-encoding (GD Library) untuk menghapus metadata EXIF berbahaya.
- **Audit Trail (RecordApprovalLog):** Setiap perpindahan status dicatat otomatis di `approval_logs`.

---

## 3. Struktur Peran & Izin (RBAC)

| Role            | Tanggung Jawab Utama                                                                             |
| :-------------- | :----------------------------------------------------------------------------------------------- |
| **Super Admin** | Manajemen User, Role, dan Keamanan Sistem. (Dilindungi dari Self-Deactivation).                  |
| **Admin**       | Operasional harian: Input Item, Pengajuan Maintenance, Update Profil. (Terbatas pada Ownership). |
| **Kasi**        | Verifikator Level 1: Review Item baru dan Review pengajuan Maintenance awal.                     |
| **Kel_Pust**    | Final Approver: Aktivasi Item dan Penerbitan SPK Otomatis.                                       |

---

## 4. Standar Kode & Struktur API

- **RESTful API:** Menggunakan metode HTTP tepat (`GET`, `POST`, `PUT`, `PATCH`, `DELETE`).
- **Secure Upload Trait:** Menggunakan `SecureImageUpload` trait untuk semua proses upload ke AWS S3.
- **Atomic Transaction:** Pembuatan Maintenance, SPK, dan Rekap dibungkus dalam `DB::beginTransaction`.

---

## 5. Hasil Audit Kode & Bug Report (Update Juni 2026)

### A. Critical & Logic Bugs [FIXED]

1.  **Race Condition (Sequence Generation):** FIXED.
2.  **Inconsistent Category Update:** FIXED.
3.  **Missing Transaction on Auth Refresh:** FIXED.
4.  **Status Transition Loophole (Maintenance):** FIXED. Implementasi strict workflow dan otomatisasi SPK.

### B. Database & Schema Issues [UPDATED]

1.  **Missing Index/Unique Constraint:** FIXED.
2.  **Missing Unique Constraint on Sequence Numbers:** FIXED.

### C. Security & API Issues [FIXED]

1.  **Inconsistent Pagination:** FIXED.
2.  **Fragile S3 Path Extraction:** FIXED.
3.  **Silent Failure in Profile Update:** FIXED.

### D. Coding Standards & Maintenance [UPDATED]

1.  **Stale Documentation:** FIXED.
2.  **Inconsistent Endpoint Naming:** PENDING.

---

## 6. Laporan Security Audit & Penetration Test (STATUS: ALL FIXED)

### A. Vulnerability: Sensitive Data Exposure (High Risk)

- **Temuan:** Akun Enumeration pada Login.
- **Status:** **FIXED**. Menggunakan pesan error generik "Invalid credentials".

### B. Vulnerability: Insecure Direct Object Reference (IDOR) (Medium Risk)

- **Temuan:** Global Admin Ownership.
- **Status:** **FIXED**. Implementasi Ownership Check di level Service untuk modul Items dan Maintenance.

### C. Vulnerability: Broken Access Control (Medium Risk)

- **Temuan:** Self-Deactivation DoS.
- **Status:** **FIXED**. Proteksi di `UserService` untuk mencegah Super Admin menonaktifkan diri sendiri atau menghapus Super Admin terakhir.

### D. Vulnerability: Unrestricted File Upload (Low-Medium Risk)

- **Temuan:** Logic Bypass via Metadata (EXIF Payload).
- **Status:** **FIXED**. Implementasi `SecureImageUpload` Trait dengan re-encoding gambar (GD) untuk menghapus metadata.

### E. Vulnerability: Broken Access Control (Horizontal)

- **Temuan:** `updateStatus` Loophole.
- **Status:** **FIXED**. Validasi strict transisi status berdasarkan role dan otomatisasi penerbitan SPK.

---

## 7. Rencana Penyelesaian Masalah (Juni 2026)

### A. Validasi Status Item pada Pengajuan Maintenance

- **Masalah:** Item berstatus `draft` saat ini bisa diajukan untuk pemeliharaan (maintenance). Seharusnya, hanya item yang berstatus `active` yang dapat dilakukan maintenance.
- **Rencana Solusi:**
    1. **Validator Request (`MaintenanceStoreRequest.php`):**
       Ubah validasi `item_id` agar hanya menerima item yang memiliki status `active`.

        ```php
        use Illuminate\Validation\Rule;

        // Di rules()
        'item_id' => [
            'required',
            'uuid',
            Rule::exists('items', 'uuid')->where('status', 'active')
        ],
        ```

    2. **Service Layer (`MaintenanceService.php`):**
       Tambahkan pengecekan manual di `addMaintenance` untuk memastikan keamanan data:
        ```php
        $item = Item::where('uuid', $data['item_id'])->firstOrFail();
        if ($item->status !== 'active') {
            throw new \InvalidArgumentException("Hanya item dengan status active yang dapat diajukan untuk pemeliharaan.");
        }
        ```

### B. Perbaikan Logika Resubmit setelah Rejected/Revision (Multi-Stage Routing)

- **Masalah:**
  Pada alur persetujuan Item dan Maintenance: `Admin (Draft) -> Kasi (Pending Kasi) -> Kepala Pustakawan (Pending Pust) -> Active/In Progress`.
  Jika Kepala Pustakawan (`kel_pust`) melakukan penolakan/revisi, data akan kembali ke Admin (status `revision` untuk Item atau `rejected` untuk Maintenance).
  Saat Admin mengirim ulang (resubmit), status secara otomatis kembali ke `pending_kasi` (memerlukan persetujuan Kasi lagi). Padahal, Kasi sudah menyetujui sebelumnya dan tidak melakukan penolakan. Seharusnya, data langsung dikirim ke `pending_pust`.
- **Rencana Solusi:**
    1. **Item Status Flow (`ItemService.php`):**
       Di dalam metode `updateStatus`, lakukan pengecekan riwayat log persetujuan (`approval_logs`) ketika status asal adalah `revision` dan pengubah status adalah `admin`:

        ```php
        if ($currentUser->role->name === 'admin' && $statusFrom === 'revision') {
            $lastRevisionLog = $item->approvalLogs()
                ->where('status_to', 'revision')
                ->orderByDesc('id')
                ->first();

            $rejectedByKelPust = false;
            if ($lastRevisionLog && $lastRevisionLog->user && $lastRevisionLog->user->role) {
                if ($lastRevisionLog->user->role->name === 'kel_pust') {
                    $rejectedByKelPust = true;
                }
            }

            if ($rejectedByKelPust) {
                $roleTransitions['admin']['revision'] = ['pending_pust'];
            } else {
                $roleTransitions['admin']['revision'] = ['pending_kasi'];
            }
        }
        ```

    2. **Maintenance Status Flow (`MaintenanceService.php`):**
       Terapkan logika yang sama di dalam metode `updateStatus` ketika status asal adalah `rejected` dan pengubah status adalah `admin`:

        ```php
        if ($currentUser->role->name === 'admin' && $statusFrom === 'rejected') {
            $lastRejectedLog = $maintenance->approvalLogs()
                ->where('status_to', 'rejected')
                ->orderByDesc('id')
                ->first();

            $rejectedByKelPust = false;
            if ($lastRejectedLog && $lastRejectedLog->user && $lastRejectedLog->user->role) {
                if ($lastRejectedLog->user->role->name === 'kel_pust') {
                    $rejectedByKelPust = true;
                }
            }

            if ($rejectedByKelPust) {
                $roleTransitions['admin']['rejected'] = ['pending_pust'];
            } else {
                $roleTransitions['admin']['rejected'] = ['pending_kasi'];
            }
        }
        ```

---

_Terakhir diperbarui: 23 Juni 2026_
