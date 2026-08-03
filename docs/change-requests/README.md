# Change Requests

Gunakan dokumen ini untuk perubahan terhadap Technology Stack Decision v1.0 atau Functional Alpha scope.

## Format minimum

- ID dan tanggal
- keputusan yang terdampak
- alasan dan bukti
- dampak teknis/produk
- alternatif yang dipertimbangkan
- keputusan dan approver
- rencana rollback

Tidak ada perubahan major dependency, arsitektur, atau scope locked tanpa catatan change request/ADR.

## Open items

- CR-001: Upgrade runtime Herd lokal dari PHP 8.4.23 ke PHP 8.5.x.
- CR-002: Provision PostgreSQL 18 + pgvector dan Redis 8 untuk staging/integration test.
- CR-003: Composer compatibility review sebelum mengubah constraint PHP menjadi `^8.5`.
