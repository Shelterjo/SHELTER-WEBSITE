<?php

namespace App\Models;

use App\Enums\ContactKind;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Contact by intent (D-057, D-059). value is stored in E.164 for phones (+962…); display formats per D-065.
 *
 * @property int $id
 * @property string $scope
 * @property int|null $branch_id
 * @property ContactKind $kind
 * @property string|null $value
 * @property string|null $label_ar
 * @property string|null $label_en
 * @property bool $is_public
 * @property bool $show_on_branch_cards
 * @property int $sort
 */
class ContactPoint extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['kind' => ContactKind::class, 'is_public' => 'boolean', 'show_on_branch_cards' => 'boolean', 'sort' => 'integer'];
    }

    /** @return BelongsTo<Branch, $this> */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function factKey(): string
    {
        return $this->scope === 'branch' && $this->branch_id !== null
            ? "contact.branch.{$this->branch_id}.{$this->kind->value}"
            : "contact.{$this->kind->value}";
    }
}
