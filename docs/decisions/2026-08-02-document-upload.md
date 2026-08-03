# Document Upload Baseline

The next walking-skeleton slice now accepts PDF and DOCX source files for a teaching project.

- files are stored on the `private` disk under a project-scoped path;
- original name, MIME type, extension, size, checksum, and processing status are persisted;
- unsupported formats are rejected;
- extraction, chunking, embeddings, and queue processing remain deferred;
- authorization is checked both on the request and controller boundary;
- deletion removes the private object and database record.

This deliberately stops before Poppler/PHPWord integration so SQLite/PHP 8.4 local development remains self-contained.
