<?php

/*
| AI provider layer (M69 §24–§27, M70 §49–§54). One switchable provider behind App\Services\Ai\AiGateway; the key is
| server-side only (never in HTML, JS or the repository). Nothing is connected until the Owner authorizes a provider:
| AI_PROVIDER=none keeps both assistants on their structured, rule-based answers (they work fully without AI).
| Each assistant has its own profile: its own data scopes, prompt and limits (Shaltoor = public data only).
*/

return [
    'provider' => env('AI_PROVIDER', 'none'), // none · anthropic

    'anthropic' => [
        'key' => env('AI_ANTHROPIC_KEY'),
        'model' => env('AI_ANTHROPIC_MODEL', 'claude-haiku-4-5-20251001'),
        'endpoint' => 'https://api.anthropic.com/v1/messages',
        'version' => '2023-06-01',
    ],

    'timeout_seconds' => (int) env('AI_TIMEOUT', 12),

    // Cost control: calls per profile per day across the whole site; past it the assistants answer without AI.
    'daily_limits' => [
        'shaltoor' => (int) env('AI_SHALTOOR_DAILY_LIMIT', 300),
        'owner' => (int) env('AI_OWNER_DAILY_LIMIT', 60),
    ],

    // Tool / data scopes per profile (M70 §51). A profile reads only what its scopes allow; nothing publishes.
    'scopes' => [
        'shaltoor' => ['PUBLIC_MASTER_DATA', 'PUBLIC_MENU', 'PUBLIC_HOURS', 'PUBLIC_CONTACTS', 'PUBLIC_EVENTS', 'PUBLIC_PAGES'],
        'owner' => ['READ_ANALYTICS', 'READ_SEO', 'READ_SITE_HEALTH', 'READ_MASTER_DATA', 'READ_SEARCH', 'READ_SHALTOOR_INSIGHTS', 'READ_FORMS_AGGREGATES'],
    ],
];
