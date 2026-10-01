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
