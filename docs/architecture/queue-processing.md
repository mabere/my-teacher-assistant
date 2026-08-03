# Queue Processing Local Baseline

The local environment uses SQLite with the database queue. This is an accepted
development deviation from the production stack decision, which targets Redis
and Horizon.

Start the worker from the project root:

```powershell
php artisan queue:work database --tries=2 --timeout=120
```

Inspect failed jobs:

```powershell
php artisan queue:failed
php artisan queue:retry all
```

Document status transitions are persisted independently of the worker:

```text
pending -> processing -> ready
                    \-> failed
```

The project page shows the status and processing error. A failed document can
be sent back to `pending` through the retry action. DOCX retry dispatches the
job; PDF retry remains pending until Poppler is installed.

Production readiness still requires Redis, Horizon, worker supervision, and
alerting for failed document jobs.
