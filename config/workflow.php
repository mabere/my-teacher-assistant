<?php

return [
    'stale_after_minutes' => max(1, (int) env('WORKFLOW_STALE_AFTER_MINUTES', 10)),
];
