# RPP Vertical Slice

## Alur

```text
Halaman proyek
  -> Generate draft RPP
  -> generated_contents status queued
  -> GenerateRppDraft pada database queue
  -> Gemini menggunakan konteks proyek dan dokumen ready
  -> validasi RppGenerationOutput
  -> generated_contents status ready atau failed
  -> versi pertama dibuat dalam transaksi
  -> halaman proyek menampilkan draft
```

## Data yang dikirim

Saat guru menekan Generate, aplikasi mengirim:

- mata pelajaran, kelas, topik, durasi, dan model pembelajaran;
- capaian dan tujuan pembelajaran;
- konteks profil kelas;
- teks dokumen yang sudah berstatus `ready`.

Dokumen dibatasi maksimal 12.000 karakter per sumber pada konteks generasi. Provider dipilih melalui service internal dan tidak dipanggil langsung dari view atau controller.

## Data yang disimpan

`generated_contents` menyimpan:

- snapshot input terstruktur;
- output RPP terstruktur;
- provider dan model;
- status antrean/pemrosesan;
- waktu mulai dan selesai;
- pesan error apabila gagal.

`generated_content_versions` menyimpan payload versi draft. Snapshot dan payload memakai encrypted cast pada kolom text. API key tetap berada di `ai_provider_settings` dalam bentuk terenkripsi.

## Review dan batas slice

Draft dapat ditinjau dan diedit guru, dan versi awal disimpan saat job berhasil. Slice ini belum mencakup:

- alignment checker berbasis CPL/CPMK/Sub-CPMK;
- approval checklist dan status approved;
- export DOCX/PDF nyata;
- retrieval embedding/pgvector;
- generator LKPD, materi, dan asesmen sebagai paket terpisah;
- OCR untuk dokumen scan.

Fitur tersebut mengikuti UX prototype v1.2 secara bertahap. Approval tetap menjadi prasyarat export; AI tidak menjadi pengambil keputusan akademik.

## Operasional lokal

Jalankan worker database:

```bash
php artisan queue:work database --verbose --tries=1 --timeout=180
```

Setelah menekan Generate RPP, segarkan halaman proyek untuk melihat status `Menunggu antrean`, `Sedang diproses`, `Siap ditinjau`, atau `Gagal`.
