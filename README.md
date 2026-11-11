# Friendster — Internal Social Media (Laravel + Blade)

Friendster adalah aplikasi media sosial internal MVP yang dibangun dengan **Laravel 13 + Blade + SQLite**, mengimplementasikan seluruh indikator kompetensi UJK Junior Web Programmer (PRD ConnectSpace v1.0).

> **Stack:** Laravel 13 · PHP 8.5 · Blade · SQLite · Bootstrap 5 (CDN) · bcrypt password hashing · Eloquent ORM (prepared statements otomatis)

---

## ✨ Fitur Lengkap (semua 15 modul in-scope PRD)

| # | Modul | Fitur | Status |
|---|---|---|---|
| 1 | Authentication | Register | ✅ |
| 2 | Authentication | Auto-login setelah registrasi | ✅ |
| 3 | Authentication | Login & Logout | ✅ |
| 4 | Authorization | Route Protection (auth & guest middleware) | ✅ |
| 5 | Profile | Edit Nama | ✅ |
| 6 | Profile | Edit Bio | ✅ |
| 7 | Profile | Upload Foto Profil | ✅ |
| 8 | Security | Change Password (verifikasi password lama) | ✅ |
| 9 | Feed | Menampilkan seluruh postingan (desc) | ✅ |
| 10 | Post | Create Post (text &/or image) | ✅ |
| 11 | Post | Upload Gambar Post | ✅ |
| 12 | Post | Delete Own Post (cascade) | ✅ |
| 13 | Interaction | Like & Unlike (UNIQUE constraint) | ✅ |
| 14 | Interaction | Create Comment | ✅ |
| 15 | Interaction | Upload Gambar Komentar | ✅ |

---

## 🧪 Hasil Pengujian Acceptance Criteria

Semua **24/24 test scenario** dari PRD section 8 telah lulus (diuji otomatis via Python `requests`):

```
[PASS] T 1  Register new email -> account created
[PASS] T 2  Register duplicate email -> rejected
[PASS] T 3  Register with password < 6 chars -> rejected
[PASS] T 4  Password mismatch -> rejected
[PASS] T 5  After register -> auto-login to home
[PASS] T 6  Login valid credentials -> success
[PASS] T 7  Login wrong password -> rejected
[PASS] T 8  Access home without login -> redirect to /login
[PASS] T 9  Access /login while authed -> redirect to /
[PASS] T 9b Access /register while authed -> redirect to /
[PASS] T 10 Logout -> session destroyed
[PASS] T 11 Edit profile -> data updated
[PASS] T 12 Change password (wrong old) -> rejected
[PASS] T 12b Change password (correct old) -> success
[PASS] T 13 Create text post -> created
[PASS] T 14 Create image post -> created
[PASS] T 15 Empty post -> rejected
[PASS] T 16 New post appears at top of feed
[PASS] T 17 Delete own post -> success
[PASS] T 18 Delete other's post -> rejected (server verifies ownership)
[PASS] T 19 Like -> total +1
[PASS] T 20 Like again -> unlike (total -1)
[PASS] T 21 Create comment -> shown
[PASS] T 22 Create comment with image -> image shown
[PASS] T 23 Cascade delete (likes & comments gone)
[PASS] T 24 SQL injection -> blocked (Eloquent prepared statements)
```

---

## 🚀 Cara Menjalankan

### Prasyarat
- **PHP 8.3+** dengan extension: `pdo_sqlite`, `mbstring`, `openssl`, `tokenizer`, `xml`, `fileinfo`, `curl`, `zip`
- **Composer** 2.x

### Langkah Singkat

```bash
cd friendster

# 1. Install dependency PHP
composer install --no-dev --optimize-autoloader
# (atau: composer install)

# 2. Salin env (jika belum)
cp .env.example .env

# 3. Generate app key
php artisan key:generate

# 4. Konfigurasi .env (default sudah SQLite, tidak perlu diubah)
#    DB_CONNECTION=sqlite

# 5. Jalankan migrasi + seeder (3 user demo + 3 post + 1 like + 2 comment)
php artisan migrate:fresh --seed

# 6. Buat symlink storage (untuk upload gambar)
php artisan storage:link

# 7. Jalankan dev server
php artisan serve
```

Akses aplikasi di **http://localhost:8000**

### Akun Demo (password: `password`)

| Nama | Email |
|---|---|
| Andi Pratama | andi@friendster.test |
| Bunga Lestari | bunga@friendster.test |
| Citra Dewi | citra@friendster.test |

---

## 🗄️ Skema Database

Empat tabel utama (sesuai PRD section 5):

```
users (id, name, email, password, avatar, bio, created_at, updated_at)
  ├── posts (id, user_id FK→users, content, image, created_at, updated_at) ON DELETE CASCADE
  │     ├── likes (id, user_id FK→users, post_id FK→posts, created_at)
  │     │     UNIQUE(user_id, post_id)  -- cegah duplikasi like
  │     │     ON DELETE CASCADE untuk kedua FK
  │     └── comments (id, post_id FK→posts, user_id FK→users, comment, image, created_at)
  │           ON DELETE CASCADE untuk kedua FK
  └── (relasi many-to-many users↔posts melalui likes)
```

Relasi Eloquent:
- `User::posts()`, `User::likes()`, `User::comments()` → HasMany
- `Post::user()` → BelongsTo; `Post::likes()`, `Post::comments()` → HasMany
- `Like::user()`, `Like::post()` → BelongsTo
- `Comment::user()`, `Comment::post()` → BelongsTo

---

## 🛡️ Aspek Keamanan yang Diterapkan

| PRD | Implementasi |
|---|---|
| Password hashing (bcrypt) | `Hash::make()` / `password` cast di User model |
| SQL Injection protection | Eloquent ORM (prepared statements otomatis) |
| Middleware / route guard | `auth` & `guest` middleware + `redirectTo()` |
| Verifikasi kepemilikan saat delete | `$post->user_id === Auth::id()` di `PostController::destroy` |
| UNIQUE constraint cegah duplikasi like | `$table->unique(['user_id', 'post_id'])` + `try/catch` di controller |
| Cascade delete | FK `cascadeOnDelete()` di migrasi |
| Validasi file gambar | `image`, `mimes`, `max` rules; `fileinfo` extension |
| CSRF protection | `@csrf` di semua form (default Laravel) |

---

## 📂 Struktur Folder Penting

```
friendster/
├── app/
│   ├── Http/Controllers/
│   │   ├── AuthController.php        (register, login, logout, auto-login)
│   │   ├── ProfileController.php     (edit profil + change password)
│   │   ├── PostController.php        (feed, create, delete + cascade cleanup)
│   │   ├── LikeController.php        (toggle like/unlike)
│   │   └── CommentController.php     (store comment + optional image)
│   └── Models/
│       ├── User.php                  (+ avatar, bio, relasi)
│       ├── Post.php                  (isLikedBy, imageUrl)
│       ├── Like.php
│       └── Comment.php
├── database/
│   ├── migrations/                   (4 tabel + sessions/cache/jobs)
│   └── seeders/DatabaseSeeder.php   (data demo)
├── resources/views/
│   ├── layouts/app.blade.php         (navbar + Bootstrap 5 + alerts)
│   ├── auth/                         (login.blade.php, register.blade.php)
│   ├── profile/                      (edit.blade.php, password.blade.php)
│   ├── posts/partials/               (post.blade.php, comment.blade.php)
│   ├── errors/                       (404, 419)
│   └── home.blade.php                (feed + create post form)
├── routes/web.php                    (route protection: guest & auth groups)
└── bootstrap/app.php                 (redirectTo config)
```

---

## 🧭 Alur Penggunaan Singkat

1. Buka http://localhost:8000 → otomatis redirect ke `/login` (jika tamu)
2. Klik "Daftar di sini" → isi form register → **auto-login** ke Home
3. Di Home: buat post (text/gambar/duanya), like post orang lain, komentar dengan gambar
4. Klik avatar di navbar → "Profil Saya" untuk edit nama/bio/avatar
5. Klik avatar → "Ubah Password" untuk ganti password (verifikasi password lama)
6. Klik tombol 🗑 di post sendiri untuk hapus (cascade otomatis hapus likes & comments)

---

## 📝 Catatan Implementasi (Assumptions & Open Questions dari PRD section 10)

| Pertanyaan | Keputusan |
|---|---|
| Comment wajib teks atau boleh hanya gambar? | Boleh salah satu (text/gambar/duanya). Validasi minimal salah satu. |
| Image storage lokal atau URL? | Lokal di `storage/app/public/{avatars,posts,comments}/`. URL eksternal juga didukung (dicek via `filter_var`). |
| Pagination feed? | Ya, 10 post per halaman (Laravel pagination). |
| Hak hapus komentar? | Tidak diimplementasikan (sesuai scope out PRD section 2.2). |
| Edit post? | Tidak (sesuai scope out). |
| Session expiration? | Default Laravel (120 menit inactivity). |
| Image restrictions? | JPG/JPEG/PNG/WEBP/GIF, max 2MB avatar, 4MB post/comment image. |

---

## 🧪 Menjalankan Test

Test otomatis (acceptance criteria) ada di `../scripts/test_friendster.py` (memerlukan `pip install requests`). Jalankan saat server berjalan:

```bash
php artisan serve &
python3 ../scripts/test_friendster.py
```

---

Dibuat untuk UJK Junior Web Programmer — implementasi Laravel + Blade sesuai PRD ConnectSpace v1.0.
