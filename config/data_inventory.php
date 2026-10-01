<?php

/*
| Data classification inventory (docs/platform/DATA-CLASSIFICATION.md, M35 §53).
| Every table in the schema MUST be listed (tests/Feature/Core/DataInventoryTest fails otherwise — DC-T01).
| 'class' is the table default; 'columns' lists columns classified differently. 'per_row' = class column on the row.
| Classes: PUBLIC · INTERNAL · CONFIDENTIAL · SENSITIVE. The class drives access, storage, export and analytics rules.
*/

return [
    // Platform core
    'users' => ['class' => 'SENSITIVE', 'columns' => ['name' => 'CONFIDENTIAL', 'email' => 'CONFIDENTIAL']],
    'sessions' => ['class' => 'CONFIDENTIAL'],
    'audit_logs' => ['class' => 'CONFIDENTIAL'],
    'content_versions' => ['class' => 'INTERNAL'],
    'settings' => ['class' => 'INTERNAL', 'per_row' => 'classification'],
    'feature_flags' => ['class' => 'INTERNAL'],
    'signals' => ['class' => 'INTERNAL'],
    'reference_sequences' => ['class' => 'INTERNAL'],
    'scheduled_job_runs' => ['class' => 'INTERNAL'],

    // Content (CONTENT-SOURCE-OF-TRUTH): public once published; drafts and AI-origin text stay internal.
    'pages' => ['class' => 'PUBLIC', 'per_row' => 'status'],
    'page_sections' => ['class' => 'PUBLIC'],

    // Master data (public once approved; unapproved values are NULL + a pending fact)
    'markets' => ['class' => 'PUBLIC'],
    'countries' => ['class' => 'PUBLIC'],
    'cities' => ['class' => 'PUBLIC'],
    'branches' => ['class' => 'PUBLIC'],
    'branch_hours' => ['class' => 'PUBLIC'],
    'hours_exceptions' => ['class' => 'PUBLIC', 'columns' => ['created_by' => 'INTERNAL']],
    'branch_attributes' => ['class' => 'PUBLIC'],
    'contact_points' => ['class' => 'PUBLIC', 'per_row' => 'is_public'],
    'social_links' => ['class' => 'PUBLIC'],
    'facts' => ['class' => 'PUBLIC', 'columns' => [
        'source_type' => 'INTERNAL', 'source_ref' => 'INTERNAL', 'decision_ref' => 'INTERNAL', 'evidence' => 'INTERNAL',
        'notes' => 'INTERNAL', 'approved_by' => 'INTERNAL', 'verified_by' => 'INTERNAL', 'blocked_phrases' => 'INTERNAL',
    ]],
    'external_references' => ['class' => 'INTERNAL'],

    // Menu (published values public; drafts and lineage internal)
    'menu_versions' => ['class' => 'INTERNAL'],
    'menu_groups' => ['class' => 'PUBLIC'],
    'menu_categories' => ['class' => 'PUBLIC', 'columns' => ['suggested_name_ar' => 'INTERNAL', 'name_ar_status' => 'INTERNAL', 'source_name' => 'INTERNAL']],
    'menu_subcategories' => ['class' => 'PUBLIC'],
    'products' => ['class' => 'PUBLIC', 'columns' => [
        'suggested_name_ar' => 'INTERNAL', 'name_en_status' => 'INTERNAL', 'name_ar_status' => 'INTERNAL', 'name_en_decision' => 'INTERNAL',
        'name_ar_decision' => 'INTERNAL', 'data_quality_status' => 'INTERNAL', 'data_quality_flags' => 'INTERNAL', 'is_featured' => 'INTERNAL',
    ]],
    'menu_source_rows' => ['class' => 'INTERNAL'],
    'product_prices' => ['class' => 'PUBLIC', 'columns' => ['created_by' => 'INTERNAL', 'source' => 'INTERNAL']],
    'product_branch_overrides' => ['class' => 'PUBLIC', 'columns' => ['reason' => 'INTERNAL', 'updated_by' => 'INTERNAL']],

    // Framework tables (job payloads carry IDs only, never PII)
    'migrations' => ['class' => 'INTERNAL'],
    'cache' => ['class' => 'INTERNAL'],
    'cache_locks' => ['class' => 'INTERNAL'],
    'jobs' => ['class' => 'INTERNAL'],
    'job_batches' => ['class' => 'INTERNAL'],
    'failed_jobs' => ['class' => 'INTERNAL'],
];
