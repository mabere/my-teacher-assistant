# Architecture Overview

## Baseline

`my-teacher-assistant` menggunakan Laravel modular monolith dengan Blade/Livewire. PostgreSQL + pgvector, Redis + Horizon, private object storage, dan structured AI output adalah baseline target yang dikunci dalam Technology Stack Decision v1.0.

Aplikasi tidak mengizinkan Livewire atau controller memanggil provider AI secara langsung. Fitur AI melewati contract/service internal, sedangkan pekerjaan lama berjalan melalui job queue. Resource domain dibatasi oleh ownership user dan policy.

## Dependency direction

```text
Blade/Livewire
  -> application controllers/actions
  -> domain services, data objects, and policies
  -> internal contracts
  -> infrastructure adapters
```

## Functional map saat ini

```text
authenticated user
  -> class_profiles
  -> teaching_projects
  -> project_documents (private storage)
  -> ProcessProjectDocument
  -> generated_contents
  -> GenerateRppDraft
  -> generated_content_versions
```

Dashboard mengambil count dan status dari relasi milik user. Dokumen sumber dibatasi oleh project ownership dan hanya teks berstatus ready yang dapat masuk ke konteks generate. RPP disimpan sebagai structured payload agar dapat diedit, divalidasi, dan diberi versi.

## Data and privacy boundaries

- API key provider disimpan terenkripsi pada `ai_provider_settings`.
- Snapshot input dan payload generated content menggunakan encrypted cast dan kolom text.
- File sumber memakai disk `private`; file dibersihkan saat dokumen atau proyek dihapus.
- Retrieval dan konteks generasi harus tetap scoped ke project/user.
- Export tidak boleh tersedia sebelum approval guru menjadi kontrak backend yang nyata.

## Functional Alpha boundary

Sudah tersedia: auth, policy, CRUD profil/proyek, upload dan ekstraksi PDF/DOCX, queue status/retry, generator RPP Gemini, structured validation, draft edit/version, dashboard real-data, dan panduan workflow authenticated.

Belum menjadi kontrak production: retrieval embedding/pgvector, OCR, generator artifact selain LKPD, dan agent workflow. Alignment, approval versi, export DOCX/PDF approved, dan LKPD sudah menjadi vertical slice Phase 8. Prototype v1.2 menjadi referensi UX untuk slice tersebut, bukan sumber implementasi monolitik.

## Runtime parity

Target CI/staging/production adalah PHP 8.5, Node 24, PostgreSQL 18 + pgvector, Redis 8 + PhpRedis + Horizon, private S3-compatible storage, Poppler, PHPWord, Dompdf, dan Chromium untuk export.

Herd lokal pada audit ini memakai PHP 8.4.23, SQLite, dan database queue. Perbedaan ini dicatat sebagai pekerjaan parity infrastruktur berikutnya; tidak mengubah keputusan arsitektur.

## Verification baseline

Perubahan backend harus melewati Pint, PHPStan, PHPUnit, migration check, dan view cache. Perubahan frontend harus melewati Vite build. Integrasi AI menggunakan HTTP fake dalam test biasa; live provider test tetap eksplisit dan tidak menjadi bagian suite default.
