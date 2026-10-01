<?php

namespace App\Services\Site;

use App\Enums\ContactKind;
use App\Models\ContactPoint;
use App\Services\MasterData\MasterData;
use App\Support\PhoneNumber;

/**
 * Brand-level call and WhatsApp actions (D-057, D-058, D-059, D-062, D-065). Only approved, public contact points are
 * returned (MasterData::contact); `onBranchCards` additionally requires show_on_branch_cards, so intent numbers
 * (complaints/franchise, catering/B2B) can never reach a branch card, branch page or the footer.
 */
final class ContactActions
{
    public function __construct(private readonly MasterData $data, private readonly Markets $markets) {}

    public function phone(string $locale): ?ContactAction
    {
        $point = $this->point(ContactKind::PhoneMain);

        return $point === null ? null : new ContactAction(PhoneNumber::tel((string) $point->value), $this->display($point, $locale));
    }

    public function whatsapp(string $locale): ?ContactAction
    {
        $point = $this->point(ContactKind::Whatsapp);

        return $point === null ? null : new ContactAction(PhoneNumber::whatsapp((string) $point->value), $this->display($point, $locale));
    }

    /**
     * Any approved, public contact point by intent (contact page — D-059): the number as displayed in this locale and
     * its link (tel:, wa.me or mailto:). Intent numbers are shown only here and on their own pages, never on cards.
     */
    public function intent(ContactKind $kind, string $locale): ?ContactAction
    {
        $point = $this->data->contact($kind);
        if ($point === null) {
            return null;
        }
        $value = (string) $point->value;

        return match ($kind) {
            ContactKind::Email => new ContactAction('mailto:'.$value, $value),
            ContactKind::Whatsapp => new ContactAction(PhoneNumber::whatsapp($value), $this->display($point, $locale)),
            default => new ContactAction(PhoneNumber::tel($value), $this->display($point, $locale)),
        };
    }

    private function point(ContactKind $kind): ?ContactPoint
    {
        $point = $this->data->contact($kind);

        return $point !== null && $point->show_on_branch_cards ? $point : null;
    }

    private function display(ContactPoint $point, string $locale): string
    {
        $countryCode = $this->markets->current()->phone_country_code ?? '';

        return PhoneNumber::display((string) $point->value, $countryCode, $locale);
    }
}
