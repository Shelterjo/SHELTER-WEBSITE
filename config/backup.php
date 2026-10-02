<?php

/*
| Database backups made by the app (DEPLOY-005, OPS-041): `php artisan ops:backup-db`, daily from the scheduler and before
| every deploy (docs/platform/DEPLOYMENT.md §5). They sit next to Cloudways' own server backups, not instead of them.
| An empty variable in .env (VAR=) falls back to the default, like the private storage roots (FINAL-QA QA-001).
*/

$keep = env('BACKUP_KEEP');

return [

    // Private folder, never under public/ (the command refuses one that is). Cloudways: …/private_html/backups.
    'root' => env('BACKUP_ROOT') ?: storage_path('app/private/backups'),

    // How many backups to keep, newest first; older ones are deleted after each successful backup.
    'keep' => is_numeric($keep) ? max(1, (int) $keep) : 14,

    // MySQL/MariaDB dump program (MariaDB also ships it as mariadb-dump). Credentials never go on its command line.
    'mysqldump' => env('BACKUP_MYSQLDUMP') ?: 'mysqldump',

    // Seconds one dump may take before it counts as failed.
    'timeout' => 1800,

];
