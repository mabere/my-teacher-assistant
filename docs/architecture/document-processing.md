# Document Processing Pipeline

## Processing contract

```text
pending -> processing -> ready
                    \\-> failed
```

Uploads are private and tracked by checksum, storage path, processing status,
error message, extracted text, metadata, and completion timestamp.

## Adapters

- `DocxTextExtractor` reads DOCX with PHPWord 1.4.
- `PdfTextExtractor` uses `spatie/pdf-to-text` 1.55, which wraps the
  `pdftotext` executable.
- `DocumentTextExtractorRegistry` selects the adapter by extension.

Both `.docx` and `.pdf` uploads are dispatched to `ProcessProjectDocument`.
If the PDF binary is unavailable, the job records a clear `failed` status;
the document no longer remains silently pending.

## PDF binary configuration

Installing `spatie/pdf-to-text` is not sufficient by itself. The machine must
also have `pdftotext` available. If it is installed outside the common paths,
set this in `.env`:

```dotenv
PDFTOTEXT_BINARY="C:\\Program Files\\xpdf-tools-win\\bin64\\pdftotext.exe"
```

The application reads this value through `config/documents.php`.

Verify the executable before retrying a PDF:

```powershell
pdftotext -v
```

Then retry the failed document from the project page or run the queue worker:

```powershell
php artisan queue:work database --tries=2 --timeout=120
```

Scanned/image-only PDFs require OCR and are outside this text extraction
spike.
