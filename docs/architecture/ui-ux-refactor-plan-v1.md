# UI/UX Refactor Plan v1.0

## 1. Tujuan

Mentransformasikan prototype `index(2).html` menjadi UI/UX Laravel Blade yang:

- mempertahankan hierarki visual dan workflow yang telah dikunci;
- menggunakan data nyata dari database dan status queue;
- tidak membawa data simulasi ke aplikasi produksi;
- tetap kompatibel dengan route, policy, dan fitur backend yang sudah berjalan;
- dapat dikembangkan menuju Functional Alpha tanpa membuat satu Blade monolitik;
- responsif pada desktop dan smartphone;
- memiliki komponen UI bersama yang mudah dipelihara.

Prototype diperlakukan sebagai **design reference dan interaction specification**, bukan sebagai file yang ditempel langsung ke Blade.

## 2. Baseline aplikasi saat ini

| Area | Kondisi saat ini |
|---|---|
| Layout | `resources/views/layouts/app.blade.php` masih berupa header horizontal sederhana |
| CSS | `resources/css/app.css` hanya memuat Tailwind CSS dan theme font |
| JavaScript | `resources/js/app.js` masih kosong |
| Dashboard | Sudah menggunakan data nyata dari `DashboardController` |
| Class profile | CRUD Blade sudah tersedia |
| Teaching project | CRUD Blade sudah tersedia |
| Dokumen sumber | Upload, queue processing, retry, delete, dan extracted text sudah tersedia |
| AI provider | Pengaturan Gemini admin sudah tersedia |
| Generate RPP | Sudah berjalan melalui queue dan structured output |
| Edit RPP | Sudah tersedia dengan version history |
| Approval | Belum tersedia sebagai fitur backend final |
| Alignment checker | Belum tersedia sebagai fitur backend final |
| Export DOCX/PDF | Belum tersedia sebagai fitur backend final |

## 3. Prinsip integrasi

1. Navigasi aplikasi menggunakan route Laravel dan link semantik, bukan screen switching JavaScript.
2. Data tampilan berasal dari controller, model, query, dan status domain.
3. Komponen visual berulang dibuat sebagai Blade component atau partial.
4. Interaksi lokal ringan dapat menggunakan Alpine/Livewire setelah kebutuhan ditetapkan.
5. Setiap halaman harus memiliki state kosong, memproses, berhasil, dan gagal yang jelas.
6. Tidak ada tombol simulasi yang terlihat sebagai aksi nyata.
7. Approval, alignment, dan export tidak boleh ditampilkan seolah-olah sudah tersedia sebelum backend siap.
8. UI candidate/testing dipisahkan dari UI produksi.
9. Teks sumber harus dinormalisasi ke UTF-8 sebelum dipindahkan ke Blade.
10. Perubahan lintas halaman harus dilakukan melalui layout dan komponen bersama, bukan copy-paste markup.

## 4. Pemetaan komponen prototype ke Laravel

| Komponen prototype | File Blade target | Route | Sumber data utama | Status | Catatan integrasi |
|---|---|---|---|---|---|
| Application shell | `resources/views/layouts/app.blade.php` | Semua route auth | `auth()->user()`, route saat ini, session flash | Existing, perlu refaktor | Menjadi shell sidebar, topbar, mobile nav, breadcrumb, dan content slot |
| Brand dan prototype label | `resources/views/components/app/brand.blade.php` | Semua route auth | Config aplikasi dan environment | New | Label Candidate/Rev hanya untuk mode prototype/testing, bukan produksi |
| Sidebar desktop | `resources/views/components/app/sidebar.blade.php` | Semua route auth | Route aktif, user, admin capability | New | Gunakan link route dan `aria-current`; jangan gunakan button sebagai navigasi utama |
| Mobile navigation drawer | `resources/views/components/app/mobile-nav.blade.php` | Semua route auth | Route aktif | New | Interaksi lokal dapat memakai Alpine; tetap menyediakan link yang dapat diakses keyboard |
| Topbar | `resources/views/components/app/topbar.blade.php` | Semua route auth | Breadcrumb, user, session status | New | Status generasi tidak boleh hard-coded |
| Candidate banner | `resources/views/components/app/candidate-banner.blade.php` | Testing/prototype only | Config flag | New, optional | Tidak ditampilkan pada UI produksi setelah Functional Alpha |
| Toast/flash message | `resources/views/components/ui/flash.blade.php` | Semua route auth | `session('success/error/info')` | Partial existing | Perlu diseragamkan dengan desain notice prototype |
| Onboarding welcome | `resources/views/onboarding/index.blade.php` atau tahap awal create | `/onboarding` bila diperlukan | User profile, class profile | New/optional | Tidak wajib menjadi route terpisah; dapat menjadi tahap pertama wizard |
| Start-here panel | `resources/views/dashboard.blade.php` | `dashboard` | Class profile count, project count, recent project | Existing, perlu refaktor | Tombol utama menuju `teaching-projects.create` |
| Dashboard hero | `resources/views/dashboard.blade.php` | `dashboard` | User dan project summary | Existing, perlu refaktor | Ganti data prototype dengan nilai user aktual |
| Metric cards | `resources/views/components/dashboard/metric-card.blade.php` | `dashboard` | Query dashboard | Partial existing | Tambahkan RPP ready, dokumen processing, dan draft terbaru hanya jika datanya nyata |
| Proyek terbaru | `resources/views/components/teaching-projects/project-card.blade.php` | `dashboard`, `teaching-projects.index` | `TeachingProject` + `ClassProfile` | Existing, perlu komponenisasi | Pertahankan link langsung ke show |
| Project list | `resources/views/teaching-projects/index.blade.php` | `teaching-projects.index` | User-owned `TeachingProject` | Existing, perlu refaktor visual | Jangan menampilkan project contoh hard-coded |
| Class profile list | `resources/views/class-profiles/index.blade.php` | `class-profiles.index` | User-owned `ClassProfile` | Existing, perlu refaktor visual | Gunakan card dan status dari data nyata |
| Class profile form | `resources/views/class-profiles/_form.blade.php` | create/edit class profile | Request validation + `ClassProfile` | Existing | Pertahankan field domain; sesuaikan visual dengan design system |
| Document source summary | `resources/views/teaching-projects/show.blade.php` partial | nested document routes | `ProjectDocument` | Existing, perlu refaktor | Status harus berasal dari enum: queued, processing, ready, failed |
| Global document page | `resources/views/project-documents/index.blade.php` | Route baru bila dibutuhkan | User-owned `ProjectDocument` | Deferred/New | Prototype memiliki menu global; backend saat ini masih berfokus pada dokumen per project |
| Upload modal/form | `resources/views/components/project-documents/upload-form.blade.php` | `teaching-projects.documents.store` | Uploaded file + project ownership | Partial existing | Gunakan form multipart nyata, bukan file input simulasi |
| Processing state | Shared status component | document/project routes | Queue status dan `processing_error` | Partial existing | Tampilkan retry hanya untuk failed; jangan menjanjikan indexing jika belum tersedia |
| Project wizard | `resources/views/teaching-projects/wizard.blade.php` + shared stepper | `teaching-projects.create`, wizard step routes | Class profiles + session-backed validated input | Implemented | Empat tahap input v1.2 menjadi bagian dari workflow produk enam tahap; sumber ready tetap diproses pada workspace project |
| Wizard stepper | `resources/views/components/ui/stepper.blade.php` | create wizard | Current step/session/query parameter | New | Gunakan URL/query atau Livewire state agar refresh tidak menghapus data |
| Wizard context review | `resources/views/teaching-projects/wizard.blade.php` | wizard step 3/4 | Validated project context | Implemented | Review sebelum persist; upload sumber dilakukan setelah project dibuat dan generator hanya memakai dokumen ready |
| Workspace header | `resources/views/teaching-projects/show.blade.php` | `teaching-projects.show` | `TeachingProject`, `ClassProfile` | Existing, perlu refaktor total | Menjadi header workspace prototype dengan status dan actions nyata |
| Workspace status strip | `resources/views/teaching-projects/partials/status-strip.blade.php` | project show | Generated contents, documents, future findings/approval | New | Jangan gunakan angka 0/5 simulasi; tampilkan status yang tersedia saja |
| Workspace tabs | `resources/views/teaching-projects/partials/tabs.blade.php` | project show + `?tab=` | Active tab + route/query | New | Gunakan deep-linkable query parameter atau child routes |
| Workspace overview | `resources/views/teaching-projects/partials/overview.blade.php` | `teaching-projects.show?tab=overview` | Project context, generated contents | New/partial | Menampilkan paket yang benar-benar tersedia dan placeholder eksplisit untuk yang belum ada |
| Context panel | `resources/views/teaching-projects/partials/context-panel.blade.php` | project show | Project, class profile, ready documents | New | Tampilkan source yang benar-benar siap digunakan |
| Package grid | `resources/views/teaching-projects/partials/package-grid.blade.php` | project show | Generated content by type | New/partial | RPP dapat aktif; materi/LKPD/asesmen/rubrik tetap disabled atau diberi status belum tersedia |
| RPP ready card | `resources/views/teaching-projects/partials/rpp-card.blade.php` | project show | `GeneratedContent` | Existing partial, perlu visual refaktor | Pertahankan link ke editor RPP dan status ready/failed/processing |
| RPP editor | `resources/views/teaching-projects/rpp-edit.blade.php` | `teaching-projects.rpp-drafts.edit/update` | `GeneratedContent`, `GeneratedContentVersion` | Implemented Phase 6 | Integrasikan sebagai tab/workspace entry tanpa menghapus version history |
| Artifact editor | `resources/views/teaching-projects/partials/artifacts.blade.php` | `teaching-projects.artifacts.store` + workspace tab | `GeneratedContent` type `lkpd` | Implemented Phase 8 | LKPD masuk queue, memakai versi RPP terbaru, dan menampilkan status nyata |
| Alignment findings | `resources/views/teaching-projects/partials/alignment.blade.php` | `teaching-projects.alignment.run` | `AlignmentFinding` per `GeneratedContentVersion` | Implemented Phase 8 | Checker deterministik; temuan tersimpan dan dibatasi ownership |
| Approval checklist | `resources/views/teaching-projects/partials/approval.blade.php` | `teaching-projects.approvals.store` | `ContentApproval` per versi | Implemented Phase 8 | Approval mengikat versi tertentu; versi baru harus disetujui kembali |
| Export cards | `resources/views/teaching-projects/partials/approval.blade.php` | `teaching-projects.exports.download` | Approved `GeneratedContentVersion` | Implemented Phase 8 | DOCX/PDF hanya tersedia untuk versi terbaru yang approved |
| Help/workflow page | `resources/views/help/index.blade.php` | `help.index` | Static product guidance | Implemented Phase 7 | Panduan workflow nyata dengan batas fitur tersedia dan backlog yang eksplisit |
| State laboratory | Testing-only view/component | Testing only | Static states | Prototype only | Tidak masuk UI produksi kecuali menjadi component state test internal |
| Modal | `resources/views/components/ui/modal.blade.php` | Local interactions | Alpine/Livewire state | New | Gunakan focus management; jangan injeksi HTML mentah dari user input |
| Loading skeleton | `resources/views/components/ui/loading.blade.php` | All async pages | Queue state | New | Selaraskan dengan document/RPP processing status |
| Empty state | `resources/views/components/ui/empty-state.blade.php` | Lists/workspace | Query result | New | Setiap empty state memiliki CTA route yang relevan |
| Error state | `resources/views/components/ui/error-state.blade.php` | Jobs/forms | Validation/error message | New | Tampilkan retry atau tindakan pemulihan yang nyata |

## 5. Route mapping

### Route yang sudah tersedia

| Route name | Fungsi | Target refaktor |
|---|---|---|
| `dashboard` | Dashboard guru | Shell + dashboard prototype |
| `class-profiles.index/create/edit` | Profil kelas | Card/list/form design system |
| `teaching-projects.index/create/show/edit` | Proyek pembelajaran | List, wizard, workspace |
| `teaching-projects.documents.store/retry/destroy` | Dokumen sumber | Upload dan state processing |
| `teaching-projects.generate-rpp` | Queue generation | Action utama workspace |
| `teaching-projects.rpp-drafts.edit/update` | Edit dan versi RPP | Editor workspace |
| `admin.ai-provider.*` | Provider settings | Tetap menggunakan admin boundary |

### Route baru yang direncanakan

| Route name | Fungsi | Prioritas |
|---|---|---:|
| `help.index` | Panduan workflow | Tinggi |
| `project-documents.index` | Daftar dokumen lintas proyek | Sedang |
| `teaching-projects.show?tab=` | Deep-link tab workspace | Tinggi |
| `teaching-projects.alignment.run` | Alignment findings | Project ownership + latest ready RPP |
| `teaching-projects.approvals.store` | Approval version | Latest ready RPP + alignment findings |
| `teaching-projects.exports.download` | DOCX/PDF | Latest approved RPP version |

## 6. Data contract UI

### Dashboard

Data minimum:

- `classProfileCount`;
- `teachingProjectCount`;
- `recentProjects`;
- optional: latest RPP status per project;
- optional: documents currently processing.

### Project workspace

Data minimum:

- `teachingProject` dengan `classProfile`;
- `documents`;
- `latestRppDraft`;
- `generatedContents` grouped by `content_type`;
- `activeTab`;
- latest `alignmentFindings` and summary;
- latest version-bound `approval` and artifact drafts.

### RPP editor

Data minimum:

- `teachingProject`;
- ready `GeneratedContent`;
- `GeneratedContentVersion` ordered descending;
- current payload;
- ownership authorization.

## 7. Design system migration

Token visual prototype yang perlu dipindahkan ke shared CSS/theme:

- background dan surface colors;
- primary indigo;
- success, warning, danger, info;
- border radius `sm`, `md`, `lg`;
- shadow small dan large;
- spacing cards/panels;
- sidebar width;
- responsive breakpoints;
- typography hierarchy;
- status chip/badge.

Implementasi target:

- Tailwind utility untuk layout dan spacing;
- `resources/css/app.css` sebagai sumber token bersama;
- Blade components untuk card, badge, button, notice, state;
- SVG/icon component untuk ikon;
- tidak menggunakan inline style berulang;
- tidak menyalin CSS 675 baris ke satu view.

## 8. JavaScript boundary

Prototype memiliki JavaScript untuk:

- screen switching;
- modal;
- wizard state;
- generate simulation;
- tab switching;
- approval simulation;
- local toast;
- local state.

Pada Laravel:

| Prototype behavior | Target behavior |
|---|---|
| Screen switch | Route atau query tab |
| Wizard state | Server-side session, Livewire, atau persisted draft |
| Generate simulation | POST + queue job + status refresh |
| Local toast | Session flash / Livewire notification |
| Approval simulation | Controller + policy + database version |
| Local findings | Persisted alignment findings |
| Fake upload | Multipart form + private storage |
| `contenteditable` | Validated form/editor + sanitization |
| InnerHTML templates | Blade/Livewire rendering |

## 9. Urutan implementasi

### Phase 0 — Lock baseline

- tetapkan nama versi UI/UX;
- normalkan encoding;
- konfirmasi route dan data contract;
- pisahkan copy prototype dari copy production.

### Phase 1 — Shared application shell

- refaktor `layouts.app`;
- sidebar desktop;
- mobile drawer/bottom navigation;
- topbar dan breadcrumb;
- shared flash, badge, card, button, state components;
- responsive review.

### Phase 2 — Dashboard dan navigation

- terapkan start-here panel;
- metric cards nyata;
- project cards nyata;
- empty state;
- mobile layout.

### Phase 3 — CRUD pages

- project index;
- class profile index/form;
- document section/list;
- shared card and form styling.

### Phase 4 — Project wizard

- adaptasi empat tahap input server-validated dari prototype v1.2 sebagai bagian workflow produk enam tahap;
- persist data antar-tahap;
- review context;
- validasi ownership pada profil kelas dan project; readiness dokumen tetap menjadi kontrak pada workspace/generation slice.

### Phase 5 — Project workspace

- workspace header;
- status implementasi: header, status strip, tabs/deep links, context panel, package grid, dan status RPP sudah terhubung ke data nyata;
- status strip;
- tabs/deep links;
- context panel;
- package grid;
- RPP status and action.

### Phase 6 — RPP editor integration

- integrate existing editor into visual system;
- retain version history;
- preserve manual edit behavior;
- add clear return path to workspace.

Status implementasi: **selesai** - editor memiliki header workspace, context panel, status versi aktif, riwayat versi terurut, dan return path ke tab RPP.

### Phase 7 — Help page and testing support

- migrate workflow guide;
- keep evaluation-only content isolated;
- ensure user-facing help does not claim unavailable features.

Status implementasi: **selesai** - route `help.index`, panduan workflow, batas fitur, dan regression test sudah tersedia.

### Phase 8 — Future functional slices

- deterministic alignment checker with persisted findings;
- version-bound teacher approval checklist;
- approved RPP export to DOCX/PDF;
- first additional artifact generator: LKPD via Gemini queue.

Status implementasi: **selesai** - Phase 8 vertical slice tersedia; retrieval, OCR, artifact generator lain, dan collaboration tetap backlog.

## 10. Acceptance criteria UI/UX v1.0

### Navigation

- desktop sidebar and mobile navigation work on every auth page;
- active route is visually and semantically marked;
- no primary navigation relies on JavaScript only;
- keyboard focus is visible.

### Dashboard

- all numbers and project cards use database values;
- no synthetic project/status appears;
- first CTA leads to a real route;
- empty state is actionable.

### Workspace

- project context is real;
- document status is real;
- RPP generation status is real;
- RPP editor/version history remains reachable;
- unavailable future features are clearly labelled, not fake-active.

### Responsive

- desktop sidebar works at wide viewport;
- mobile navigation does not hide important actions;
- forms and tables remain usable at narrow widths;
- safe-area spacing is respected on mobile.

### Technical quality

- `php artisan view:cache` passes;
- `npm run build` passes;
- route and ownership tests pass;
- no raw user input is injected into HTML;
- no API key or private document content is exposed in the view;
- no prototype simulation state is persisted as production data.

## 11. Scope exclusions for this refactor

UI/UX refactor v1.0 tidak membangun:

- alignment engine;
- approval persistence;
- DOCX/PDF export;
- materi/LKPD/asesmen generator;
- student mode;
- billing;
- native mobile app;
- multi-school tenancy;
- real-time collaboration.

Fitur tersebut tetap mengikuti backlog Functional Alpha dan tidak boleh masuk melalui perubahan UI tanpa change request.

## 12. Definition of Done

Refaktor UI/UX v1.0 dianggap selesai apabila:

1. Shared application shell sudah dipakai semua halaman auth.
2. Dashboard memakai data nyata dan mengikuti hierarchy prototype.
3. Project list, class profile, dan document section memakai design system bersama.
4. Project wizard memiliki state yang tidak hilang saat validasi atau refresh yang didukung.
5. Project workspace memiliki header, status, tabs, context panel, dan RPP entry.
6. Existing RPP editor dan version history tetap berfungsi.
7. Help page tersedia pada route nyata.
8. Tidak ada data simulasi yang tampil sebagai data produksi.
9. Desktop dan mobile telah diperiksa.
10. View cache, frontend build, lint, static analysis, dan feature tests lulus.

## 13. Change control

Perubahan berikut memerlukan change request terpisah:

- mengganti workflow utama enam langkah;
- mengubah domain status RPP;
- mengaktifkan approval sebelum backend tersedia;
- menambahkan artifact baru ke MVP;
- mengubah source-of-truth data dari database ke local JavaScript;
- menambahkan dependency frontend besar;
- mengubah route ownership atau policy.

Status dokumen: **Phases 1-8 and the RPP vertical slice are implemented; retrieval/OCR and additional artifact slices remain pending**.
