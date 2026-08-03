# Engineering Decision Log

## 2026-08-02 — Stack alignment

Technology Stack Decision v1.0 menjadi baseline. Laravel 13, Livewire 4, Node 24, private storage, PostgreSQL/pgvector, Redis/Horizon, dan modular monolith diperlakukan sebagai target stack.

## 2026-08-02 — Local runtime exception

Audit menemukan PHP Herd 8.4.23 dan belum ada service PostgreSQL/Redis. Composer constraint PHP tetap `^8.3` sementara sebagai kompatibilitas bootstrap lokal; CI/staging ditargetkan PHP 8.5. Upgrade Herd ke PHP 8.5 dan provisioning service menjadi blocker sebelum Functional Alpha.

## 2026-08-02 — No premature package installation

Flux, Laravel AI SDK, Horizon, Telescope/Pulse, PHPWord, Spatie PDF/Browsershot, dan Dusk belum dipasang pada Sprint 0 karena belum dibutuhkan oleh walking skeleton. Pemasangan dilakukan melalui technical spike dengan lock file dan verifikasi kompatibilitas.
