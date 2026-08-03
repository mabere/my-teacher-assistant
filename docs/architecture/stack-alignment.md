# Stack Alignment Audit — Technology Stack Decision v1.0

Tanggal audit: 2 Agustus 2026

| Area | Baseline keputusan | Kondisi repository | Keputusan Sprint 0 |
|---|---|---|---|
| PHP | 8.5.x | Herd CLI 8.4.23 | CI/staging 8.5; local upgrade dicatat CR-001 |
| Laravel | 13.x | 13.17.0 | Selaras |
| Node | 24.x LTS | 24.13.0 | Selaras |
| Composer/npm | Composer 2, npm | Composer 2.9.4, npm 11.6.2 | Selaras |
| Frontend | Blade/Livewire 4/Tailwind 4.2+/Flux/Vite | Livewire 4.3.1, Tailwind 4.1.11, Vite 8; Flux belum ada | Flux dan Tailwind upgrade ditunda sampai dependency review |
| Auth | Session, policies, starter-kit capabilities | Auth baseline manual, policies ownership | Selaras untuk one-role walking skeleton |
| Database | PostgreSQL 18 + pgvector | pgsql extension ada; service belum tersedia | `.env.example` dikunci pgsql; local test memakai SQLite |
| Queue/cache | Redis 8, PhpRedis, Horizon | PhpRedis extension ada; Redis/Horizon belum tersedia | Config dikunci; Horizon masuk infrastructure spike |
| Session | Database-backed | `.env.example` memakai database | Selaras |
| Storage | Private local/S3 | private disk ditambahkan, public link tidak digunakan untuk dokumen | Selaras |
| Documents | Poppler/PHPWord, no OCR | Belum dipasang | Deferred technical spike |
| Export | PHPWord + Chromium/Browsershot | Belum dipasang | Deferred technical spike |
| Testing | PHPUnit/Pest, Dusk | PHPUnit 12.5, 9 feature tests | Selaras; Dusk deferred |
| Observability | Telescope/Horizon/Pulse | Belum dipasang | Deferred until service/infrastructure exists |
| Architecture | Modular monolith | Laravel single app | Selaras |

## Rationale

Sprint 0 hanya memasang keputusan yang diperlukan untuk auth, ownership, persistence, testing, dan private storage. Paket yang membutuhkan service eksternal, binary OS, browser headless, atau provider AI tidak dipasang secara spekulatif; masing-masing menjadi technical spike dengan lock-file review.

## Compatibility exceptions

Composer masih menyatakan PHP `^8.3` karena mesin Herd aktif masih PHP 8.4.23. Mengubah constraint menjadi `^8.5` sebelum runtime lokal tersedia akan membuat bootstrap lokal gagal. Exception ini bukan perubahan baseline; upgrade runtime dan Composer compatibility review wajib dilakukan sebelum Functional Alpha.

Tailwind lock saat ini berada di 4.1.x, sedangkan baseline menargetkan 4.2+. Upgrade dilakukan bersama `npm install` dan review lock file, bukan dengan mengedit `package.json` saja.
