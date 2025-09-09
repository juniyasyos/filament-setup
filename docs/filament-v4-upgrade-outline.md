# Outline Upgrade ke Filament v4

Dokumen ini merangkum rencana migrasi proyek ke Filament v4, keputusan paket, langkah-langkah eksekusi, serta checklist validasi. Harap tinjau dan beri persetujuan sebelum perubahan pada kode/composer dilakukan.

## Tujuan
- Migrasi penuh ke Filament v4 dengan stabil dan aman.
- Hapus/replace plugin yang tidak kompatibel atau stagnan (tidak ada perkembangan berbulan-bulan).
- Untuk paket yang belum kompatibel dengan v4 namun masih dibutuhkan, lakukan fork/clone ke `packages/` dan upgrade manual agar bisa dipakai di lingkungan dev.

## Batasan (Non-Goals)
- Tidak mengubah fitur bisnis di luar penyesuaian akibat breaking changes Filament v4.
- Tidak melakukan redesign besar UI (kecuali penggantian tema dari plugin yang dihapus).

## Dampak
- Perubahan constraints di `composer.json` dan possible penambahan repository type `path` untuk paket yang di-fork.
- Pembersihan file aset plugin tema yang dihapus (CSS/Blade/config terkait `hasnayeen/themes`).
- Penyesuaian kode Filament (resources, pages, tables/forms) mengikuti API v4.

## Inventaris Paket Terkait Filament (saat ini)
Diambil dari `composer.json`:

- filament/filament: `^4.0` (sudah v4)
- filament/spatie-laravel-media-library-plugin: `^3.2` (target: `^4.0`)
- filament/spatie-laravel-settings-plugin: `^3.2` (target: `^4.0`)
- bezhansalleh/filament-shield: `^3.3` (target: `^4.0`)
- dutchcodingcompany/filament-socialite: `^2.3` (target: `^3.0`)
- jeffgreco13/filament-breezy: `^2.4` (target: `^3.0`)
- pxlrbt/filament-excel: `^2.3` (target: `^3.0`)
- rupadana/filament-api-service: `^3.4.4` (target: `^4.0`)
- stechstudio/filament-impersonate: `^3.15` (target: `^4.0`)
- hasnayeen/themes: `*` (INCOMPATIBLE dengan Filament v4 — hapus)

Catatan: Target versi di atas mengacu pada rekomendasi output tool upgrade Filament. Jika sebuah paket belum merilis versi kompatibel, kita akan fork ke `packages/` dan melakukan penyesuaian manual.

## Keputusan Paket
- Hapus: `hasnayeen/themes` (incompatible v4, stagnan). Solusi pengganti: tema kustom via Tailwind (extend dari tema default Filament v4) dan/atau preset CSS internal.
- Upgrade langsung (bila tersedia versi kompatibel):
  - `bezhansalleh/filament-shield` → `^4.0`
  - `filament/spatie-laravel-media-library-plugin` → `^4.0`
  - `filament/spatie-laravel-settings-plugin` → `^4.0`
  - `dutchcodingcompany/filament-socialite` → `^3.0`
  - `jeffgreco13/filament-breezy` → `^3.0`
  - `pxlrbt/filament-excel` → `^3.0`
  - `rupadana/filament-api-service` → `^4.0`
  - `stechstudio/filament-impersonate` → `^4.0`
- Fork ke `packages/` jika upgrade langsung gagal karena belum kompatibel:
  - Buat struktur: `packages/<vendor>/<package>`.
  - Salin/clone source package, update constraints dan API ke v4, uji lokal.
  - Tambah repository `path` di `composer.json` root dan pakai versi fork.

## Langkah Eksekusi (Rencana Teknis)
1) Persiapan
- Pastikan branch bersih, commit terakhir up-to-date. Buat branch `chore/upgrade-filament-v4`.
- Backup `.env` dan file config terkait.

2) Bersihkan plugin incompatible
- Hapus `hasnayeen/themes` dari `composer.json` dan config terkait (`config/themes.php` bila tidak lagi dipakai).
- Hapus aset tema yang spesifik plugin bila sudah tidak relevan (`public/css/hasnayeen/...`, `public/vendor/themes/...`) setelah verifikasi pemakaian.

3) Update constraints composer
- Set versi target sesuai daftar di atas.
- Tambahkan repositori `path` untuk fork bila dibutuhkan, contoh:
```json
"repositories": [
  { "type": "path", "url": "packages/*/*", "options": { "symlink": true } }
]
```

4) Composer operations
- Jalankan: `composer require <paket-target> -W --no-update` untuk semua paket di atas.
- Lalu: `composer update -W`.
- Jika gagal karena ketidakcocokan: fork paket bermasalah ke `packages/`, bump constraints dan perbaiki kode, lalu ulangi `composer update -W`.

5) Penyesuaian kode ke API v4
- Review perubahan pada `app/Filament/**` (resources/pages):
  - Table/Forms API, Actions, BulkActions, Filters yang berubah.
  - Auth view/pages (Breezy v3), Socialite buttons.
  - Panel provider (`App\Providers\Filament\AdminPanelProvider`) untuk tema kustom v4.

6) Frontend/Theme
- Tetapkan tema kustom berbasis Tailwind untuk menggantikan `hasnayeen/themes`.
- Update `tailwind.config.js` (content paths Filament v4) bila perlu.
- Publish assets Filament v4 jika diperlukan.

7) Validasi & QA
- Jalankan test: `php artisan test`.
- Smoke test panel admin: login, navigasi resources, create/edit/delete, upload media, export/import, impersonate, social login.
- Verifikasi izin (Shield) & settings.

8) Dokumentasi & Cleanup
- Catat breaking changes residual dan cara adaptasinya.
- Hapus aset/konfigurasi yang tidak lagi dipakai.

## Rencana Rollback
- Simpan branch/commit sebelum upgrade. Jika perlu, revert ke commit terakhir stabil.
- Jika menggunakan fork di `packages/`, nonaktifkan repositori `path` dan kembali ke versi vendor setelah paket upstream kompatibel.

## Checklist Eksekusi
- [ ] Hapus `hasnayeen/themes` dan jejaknya.
- [ ] Bump semua paket Filament ke versi target.
- [ ] Tambah repositori `path` untuk fork (jika diperlukan).
- [ ] `composer update -W` sukses.
- [ ] Perbaiki compile/build frontend bila ada.
- [ ] Sesuaikan kode Filament v4 (resources/pages/actions/forms/tables).
- [ ] QA fungsionalitas inti (CRUD, upload, export/import, impersonate, social login).
- [ ] Dokumentasi perubahan & cleanup.

## Catatan File Terkait di Repo
- `composer.json` (ubah constraints & repositories)
- `config/filament.php`, `app/Providers/Filament/AdminPanelProvider.php`
- `config/themes.php` (evaluasi, kemungkinan dihapus)
- Aset tema: `public/css/hasnayeen/**`, `public/vendor/themes/**` (evaluasi pemakaian)
- Kode Filament: `app/Filament/**`

---
Jika outline ini sudah OK, saya lanjut eksekusi perubahan composer dan pembersihan plugin tema, lalu penyesuaian kode bertahap beserta forking paket yang belum kompatibel.
