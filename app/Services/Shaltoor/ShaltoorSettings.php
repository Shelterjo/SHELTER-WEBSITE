<?php

namespace App\Services\Shaltoor;

use App\Models\User;
use App\Services\Core\Settings;

/**
 * The Owner's controls for Shaltoor (M69 §23): on/off, the welcome message and the quick suggestions in each
 * language. Empty = the default wording. The assistant's rules and data sources are not editable here (no free
 * prompt editing).
 */
final class ShaltoorSettings
{
    public function __construct(private readonly Settings $settings) {}

    public function enabled(): bool
    {
        return $this->settings->get('shaltoor.enabled', true) === true;
    }

    public function welcome(string $locale): string
    {
        $custom = $this->settings->get('shaltoor.welcome.'.$locale);

        return is_string($custom) && trim($custom) !== '' ? $custom : (string) __('shaltoor.welcome', [], $locale);
    }

    /** @return list<string> */
    public function suggestions(string $locale, string $page = 'default'): array
    {
        $custom = $page === 'default' ? $this->settings->get('shaltoor.suggestions.'.$locale) : null;
        if (is_array($custom) && $custom !== []) {
            return array_values(array_filter($custom, fn ($s): bool => is_string($s) && trim($s) !== ''));
        }

        return app(Shaltoor::class)->suggestions($locale, $page);
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, string> errors
     */
    public function save(array $input, User $owner): array
    {
        $errors = [];
        $values = ['enabled' => ! empty($input['enabled'])];
        foreach (['ar', 'en'] as $locale) {
            $welcome = is_string($input['welcome_'.$locale] ?? null) ? trim($input['welcome_'.$locale]) : '';
            if (mb_strlen($welcome) > 400) {
                $errors['welcome_'.$locale] = (string) __('dashboard.pages.errors.too_long', ['max' => 400]);
            }
            $lines = array_values(array_filter(array_map('trim', preg_split('/\R/u', is_string($input['suggestions_'.$locale] ?? null) ? $input['suggestions_'.$locale] : '') ?: [])));
            if (count($lines) > 8 || collect($lines)->contains(fn (string $l): bool => mb_strlen($l) > 40)) {
                $errors['suggestions_'.$locale] = (string) __('dashboard.shaltoor.errors.suggestions');
            }
            $values['welcome.'.$locale] = $welcome === '' ? null : $welcome;
            $values['suggestions.'.$locale] = $lines === [] ? null : $lines;
        }
        if ($errors !== []) {
            return $errors;
        }
        foreach ($values as $key => $value) {
            if ($this->settings->get('shaltoor.'.$key) !== $value) {
                $this->settings->set('shaltoor.'.$key, $value, $owner, 'Shaltoor settings', 'PUBLIC'); // versioned + audited there
            }
        }

        return [];
    }
}
