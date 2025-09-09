# Dev Packages (Local Forks)

Dokumen ini menjelaskan cara menyiapkan package lokal di `packages/` untuk dikembangkan manual ketika package upstream belum kompatibel dengan Filament v4 atau lambat rilis.

## Konsep
- Kita menggunakan Composer repository tipe `path` ke `packages/*/*` (sudah ditambahkan di `composer.json`).
- Setiap package lokal memakai nama yang sama dengan upstream agar mudah drop-in replace.
- Beri field `version` stabil (mis. `4.0.0`) pada `composer.json` di package lokal supaya Composer dapat memilihnya tanpa VCS.

## Menyiapkan Dev Package dari yang sudah terpasang
1) Pastikan package sudah terpasang di `vendor/` (via `composer require vendor/package`).
2) Jalankan alat bantu:

```bash
bash tools/prepare-dev-package.sh vendor/package 4.0.0
```

- Script ini akan menyalin dari `vendor/vendor/package` ke `packages/vendor/package`, membersihkan `vendor/`, `composer.lock`, dan mengatur `version` pada `composer.json` package lokal.
- Jika Anda lupa memberi versi, tambahkan manual pada `packages/vendor/package/composer.json`.

3) Jika package perlu di-upgrade ke Filament v4, buka `packages/vendor/package/composer.json` dan:
- Naikkan constraint `filament/*` ke `^4.0`.
- Sesuaikan kode sumber mengikuti API v4.

4) Jalankan di root project:

```bash
composer update -W
```

Composer akan menggunakan package lokal menggantikan dari Packagist.

## Menyiapkan dari nol (tanpa salinan vendor)
Buat folder `packages/vendor/package` dan file `composer.json` minimal:

```json
{
  "name": "vendor/package",
  "description": "Local dev fork",
  "type": "library",
  "version": "4.0.0",
  "autoload": { "psr-4": { "Vendor\\\Package\\\": "src/" } },
  "require": {
    "php": ">=8.2",
    "filament/filament": "^4.0"
  }
}
```

Tambahkan source code ke `src/` dan pastikan namespace sesuai.

## Praktik Baik
- Commit perubahan di bawah `packages/` secara terpisah untuk memudahkan diff.
- Hindari menambahkan `vendor/` di dalam `packages/*/*` ke repo; biarkan Composer yang mengelola dependency.
- Bila upstream sudah rilis versi kompatibel, hapus folder `packages/vendor/package` dan kembalikan `composer.json` root agar mengarah ke upstream lagi, lalu `composer update -W`.

## Troubleshooting
- Composer tidak memilih package lokal: pastikan `composer.json` package lokal memiliki `name` yang sama dengan upstream dan field `version` stabil sesuai range yang diminta di root project.
- Konflik versi Filament: pastikan constraint `filament/*` di package lokal sudah `^4.0`.

