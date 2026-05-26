# Code Audit Report: SimaApi-App

**Rating: 6.5/10**
*Sistem sudah memiliki struktur yang baik (Controller-Service pattern), tapi masih banyak "lubang" di logika bisnis, konsistensi database, dan celah keamanan akses.*

---

## 1. Daftar Kesalahan Logika & Bug (Found 7+)

| Lokasi | Masalah | Dampak |
| :--- | :--- | :--- |
| `UserService.php` | `generateUniqueUsername` ngecek ke `UserProfile`, padahal kolom `username` ada di tabel `users`. | **Fatal Bug:** Query bakal crash karena kolom tidak ditemukan. |
| `UserService.php` | `addAccount` pake `$currentUser->email` buat generate username user baru. | **Bug:** User baru dapet username pake nama adminnya (e.g., `admin-2`). |
| `Models/User.php` | Kolom `username` tidak ada di `#[Fillable]`. | **Bug:** Username tidak akan tersimpan ke database saat create. |
| `Models/UserProfile.php` | Typo di `Fillable`: `full_name` vs `fullname` (di migrasi `fullname`). | **Bug:** Nama lengkap tidak akan tersimpan. |
| `Models/Role.php` | Relasi `belongsToMany(User::class)`. | **Architectural Error:** Di migrasi `users` pake `role_id` (One-to-Many), tapi di model diset Many-to-Many. |
| `UserController.php` | `storeProfile` manggil `$this->userService->addMyProfile` tapi return `UserResource`. | **Inconsistency:** Kadang return profile, kadang return user lengkap. |
| `AuthService.php` | Catch exception tapi tidak melakukan apa-apa di `refresh`. | **Logic Error:** Kode tetap jalan meski gagal, bisa bikin response kosong. |

---

## 2. Celah Keamanan (Security Vulnerabilities)

### A. Lack of RBAC (Role-Based Access Control)
*   **Temuan:** Di `api.php`, route `/roles` dan `/users` (POST/DELETE) hanya dilindungi `JwtCheckMiddleware`.
*   **Serangan:** User dengan role `staff` bisa nembak API `POST /api/users` dan bikin akun `super-admin` baru jika dia tau endpoint-nya.
*   **Solusi:** Tambahkan Middleware khusus Role (e.g., `RoleMiddleware:super-admin`).

### B. IDOR (Insecure Direct Object Reference)
*   **Temuan:** `UserService::getProfile(int $userId)` dan `deleteAccount(int $userId)`.
*   **Serangan:** Jika lu nambahin param `$id` di URL nantinya, user A bisa hapus akun user B cuma dengan ganti ID di URL.
*   **Solusi:** Selalu validasi ownership atau gunakan Policy.

### C. Mass Assignment Vulnerability
*   **Temuan:** `User::create($data)` di `UserService`.
*   **Serangan:** Jika hacker nambahin key `role_id` pas registrasi (kalo ada self-regis), dia bisa lgsg jadi admin.
*   **Solusi:** Selalu gunakan `$request->validated()` dan definisikan secara eksplisit kolom yang boleh diisi.

---

## 3. Rekomendasi "Professional Grade"

1.  **Gunakan UUID Secara Konsisten:**
    *   Di `UserController::show()`, lu masih pake `auth()->id()` (integer). Professional API harusnya pake UUID di semua layer publik.
2.  **Global Exception Handling:**
    *   Jangan banyak `try-catch` di Controller. Pindahin ke `app/Exceptions/Handler.php` (atau `bootstrap/app.php` di Laravel 11) biar Controller bersih.
3.  **Implementasikan API Versioning:**
    *   Gunakan prefix `/api/v1/...` di route.
4.  **Database Indexing:**
    *   Pastiin kolom `uuid`, `email`, dan `username` di-index (sudah dilakukan di migrasi, bagus!).
5.  **Audit Trail:**
    *   Buat sistem log siapa yang create/delete user (bukan cuma log error, tapi log aktivitas).

---

## 4. Rencana Kerja Besok (Roadmap)

1.  **Fix Models:** Benerin `Fillable` dan Relasi (Role & User).
2.  **Fix UserService:** Benerin logika `username` dan parameter email.
3.  **Security Update:** Bikin `CheckRole` middleware.
4.  **Refactor Response:** Pake Trait `ApiResponse` di Base Controller biar format JSON seragam (sesuai `GEMINI.md`).

---
*Note: File ini dibuat sebagai panduan kerja. Jangan lupa hapus `try-catch` yang berlebihan agar kode lebih "Laravel Way".*
