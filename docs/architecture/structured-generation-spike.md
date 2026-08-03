# Technical Spike: Structured RPP Generation

## Tujuan

Membuktikan bahwa provider Gemini dapat menerima konteks proyek pembelajaran
dan mengembalikan draf RPP dalam struktur JSON yang dapat divalidasi aplikasi
sebelum kelak disimpan sebagai draft.

Spike ini belum membangun editor, approval, atau persistence hasil. Ketiganya
tetap berada pada vertical slice Functional Alpha berikutnya.

## Implementasi

- Kontrak aplikasi tetap `RppGenerator`.
- `GeminiRppGenerator` meminta `responseMimeType: application/json` dan
  `responseSchema`.
- `RppGenerationOutput::fromArray()` menolak field wajib yang hilang atau tipe
  data yang salah.
- Active admin provider setting menjadi sumber konfigurasi utama; environment
  tetap menjadi fallback.
- Command `ai:spike-rpp` membangun input dari fixture atau `TeachingProject`.
- Dokumen `ProjectDocument` berstatus `ready` diteruskan sebagai sumber dengan
  batas panjang per dokumen.
- Mode default command adalah dry-run dan tidak mengirim request eksternal.
- Hasil live tidak disimpan ke database.

## Model

Model aktif diubah dari `gemini-2.5-flash` menjadi `gemini-3.6-flash` melalui
migration `2026_08_02_000009_update_default_gemini_model.php`. Perubahan ini
diperlukan karena `gemini-2.5-flash` tidak lagi tersedia untuk pengguna baru.

## Perintah

```bash
php artisan ai:spike-rpp
php artisan ai:spike-rpp --project=2
php artisan ai:spike-rpp --live --json
php artisan ai:spike-rpp --project=2 --live --json
```

Perintah project-backed live hanya digunakan setelah pengiriman konteks proyek
ke provider eksternal disetujui.

## Hasil validasi lokal

Berhasil:

- fixture dry-run;
- project-backed dry-run untuk project `2`;
- 2 dokumen berstatus `ready` terdeteksi sebagai sumber konteks;
- structured JSON HTTP fake dinormalisasi;
- output malformed ditolak;
- admin database setting mengalahkan fallback environment;
- `composer lint`;
- `composer types:check`;
- 31 test dan 96 assertion.

## Hasil smoke test live

Fixture sintetis berhasil diproses oleh `gemini-3.6-flash`:

```text
Structured output valid
Durasi: 68853 ms
Tujuan pembelajaran: 2
Aktivitas inti: 4
Asesmen formatif: 2
```

Hasil hanya berada di memory dan belum disimpan sebagai draft. Tidak ada
project content atau dokumen pengguna yang dikirim dalam smoke test ini.

## Kriteria penerimaan

Spike dianggap berhasil pada sisi aplikasi apabila:

1. input fixture dan input proyek dibentuk menjadi `RppGenerationInput`;
2. dokumen siap diteruskan sebagai sumber konteks;
3. response JSON valid dinormalisasi menjadi `RppGenerationOutput`;
4. response JSON tidak valid ditolak;
5. mode dry-run tidak membuat request HTTP;
6. kegagalan provider dikembalikan sebagai error yang dapat ditindaklanjuti;
7. tidak ada persistence sebelum vertical slice disiapkan.

## Langkah berikutnya

Uji satu proyek nyata setelah pengiriman konteks disetujui:

```bash
php artisan ai:spike-rpp --project=2 --live --json
```

Jika berhasil, lanjutkan ke **RPP vertical slice**:

```text
Proyek
→ Generate RPP
→ Simpan draft terstruktur
→ Edit per bagian
→ Simpan versi
→ Approval guru
→ Export DOCX/PDF
```

Official references:

- [Gemini models](https://ai.google.dev/gemini-api/docs/models)
- [Gemini deprecations](https://ai.google.dev/gemini-api/docs/deprecations?hl=en)
- [Gemini Generate Content](https://ai.google.dev/api/generate-content?hl=en)
