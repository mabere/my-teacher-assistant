# My Teacher Assistant

Laravel modular monolith untuk AI Teaching Assistant guru. Repository ini berada pada baseline Functional Alpha awal dengan vertical slice RPP yang sudah berjalan.

## Baseline yang dikunci

- PHP 8.5.x target CI/staging; Herd lokal saat audit menggunakan PHP 8.4.23.
- Laravel 13.x, Blade, Livewire 4.x, Tailwind CSS 4.x, Vite.
- PostgreSQL 18 + pgvector untuk staging/production.
- Redis 8 + PhpRedis + Horizon untuk queue/cache di staging/production.
- Session berbasis database dan private filesystem; dokumen tidak dipublikasikan.
- Structured output AI di belakang contract aplikasi; provider utama saat ini Gemini.
- PHPWord/DOCX, Dompdf/PDF, Poppler, dan Browsershot/Chromium masuk pada technical slice masing-masing.
- Modular monolith; bukan SPA, microservices, autonomous agents, atau vector database terpisah.

## Status implementasi

Sudah berjalan:

- Auth berbasis session dan ownership policy per user.
- CRUD profil kelas dan proyek pembelajaran.
- Project wizard bertahap dengan validasi ownership, konteks kelas, dan review sebelum persist.
- Workspace proyek dengan tabs/deep links, context panel, status sumber, dan status RPP nyata.
- Upload PDF/DOCX ke disk private.
- Ekstraksi teks dokumen melalui queue database, status pemrosesan, retry, dan error state.
- Generate draft RPP melalui job queue dan provider Gemini dengan structured output tervalidasi.
- Penyimpanan snapshot input, payload output, status, model/provider, dan versi draft.
- Edit draft RPP, dashboard berbasis data akun, dan shell navigasi responsif.
- Halaman Panduan workflow pada route authenticated `help.index`.
- Pengaturan provider Gemini oleh admin, termasuk API key terenkripsi dan kontrol aktif/nonaktif.
- Alignment checker, approval berbasis versi, export approved RPP DOCX/PDF, dan generator LKPD melalui queue.

Hardening yang baru diintegrasikan:

- Payload yang menggunakan encrypted cast disimpan pada kolom text agar kompatibel dengan PostgreSQL JSON/JSONB policy dan tetap privat.
- Status generated content dan pembuatan versi pertama disimpan dalam satu transaksi.
- Penghapusan proyek ikut menghapus file sumber pada private storage.
- Provider Gemini yang dinonaktifkan tidak lagi fallback diam-diam ke environment key.
- Dashboard tidak lagi menampilkan persentase kesiapan sintetis; status diganti menjadi kesiapan kualitatif berbasis data nyata.
- Encoding teks antarmuka yang rusak dinormalisasi.
- Workflow queue memiliki status, timestamp mulai/selesai, pesan kegagalan, indikator proses macet, pemulihan manual, dan command recovery otomatis.

## Batas Functional Alpha berikutnya

Retrieval berbasis embedding/pgvector, OCR, generator artifact selain LKPD, dan collaboration masih menjadi slice berikutnya. AI tetap menghasilkan draf; pemeriksaan, revisi, persetujuan, dan keputusan akademik tetap berada pada guru.

PostgreSQL/pgvector, Redis/Horizon, dan deployment Linux merupakan target parity staging/production. Audit lokal saat ini menggunakan SQLite dan queue database agar vertical slice dapat diverifikasi tanpa mengubah infrastruktur mesin pengembang.

## Setup lokal

1. Pastikan PHP Herd, Composer 2, Node 24, dan npm tersedia.
2. Salin `.env.example` menjadi `.env`, lalu sesuaikan key dan service lokal.
3. Jalankan `composer install`, `php artisan key:generate`, dan `php artisan migrate`.
4. Jalankan `npm ci` lalu `npm run build`.
5. Jalankan worker jika ingin memproses dokumen atau RPP:

```bash
php artisan queue:work database --verbose --tries=1 --timeout=180
```


Untuk mengaktifkan pemeriksaan proses macet otomatis, jalankan scheduler Laravel:

```bash
php artisan schedule:work
```

Ambang recovery dapat diatur melalui `WORKFLOW_STALE_AFTER_MINUTES` dan default-nya 10 menit.
## Quality commands

```bash
composer lint:check
composer types:check
composer test
npm run build
php artisan route:list
php artisan view:cache
```

## Alur domain saat ini

```text
Login
  -> profil kelas
  -> project wizard
  -> proyek pembelajaran
  -> sumber privat
  -> ekstraksi queue
  -> draft RPP terstruktur
  -> review dan edit guru
```

## Dokumentasi engineering

- `docs/architecture/overview.md`
- `docs/architecture/rpp-vertical-slice.md`
- `docs/adr/ADR-001.md` sampai `docs/adr/ADR-012.md`
- `docs/decisions/decision-log.md`
- `docs/change-requests/README.md`
