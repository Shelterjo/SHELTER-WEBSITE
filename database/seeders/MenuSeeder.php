<?php

namespace Database\Seeders;

use App\Enums\Availability;
use App\Enums\FactSource;
use App\Enums\FactStatus;
use App\Enums\NameStatus;
use App\Enums\PublishStatus;
use App\Models\Branch;
use App\Models\MenuCategory;
use App\Models\MenuGroup;
use App\Models\MenuSourceRow;
use App\Models\MenuSubcategory;
use App\Models\MenuVersion;
use App\Models\Product;
use App\Services\MasterData\FactRegistry;
use App\Services\Menu\MenuCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Imports the frozen MENU INVENTORY v1.0 (D-135) into menu master data. Idempotent and non-destructive:
 * existing rows (owner edits) are never overwritten. Verifies the file before writing anything.
 */
class MenuSeeder extends Seeder
{
    private const EXPECTED_ROWS = 192;

    private const EXPECTED_ACTIVE = 191;

    public function run(FactRegistry $facts): void
    {
        /** @var array<string, mixed> $meta */
        $meta = require __DIR__.'/data/menu.php';
        $rows = $this->readCsv(base_path($meta['inventory_csv']));
        $this->verify($rows);
        $mapping = collect($this->readCsv(base_path($meta['subcategory_csv'])))->keyBy('product_id');

        DB::transaction(function () use ($meta, $rows, $mapping, $facts): void {
            $version = MenuVersion::query()->firstOrCreate(['code' => $meta['version']['code']], [
                'status' => $meta['version']['status'],
                'effective_from' => $meta['version']['effective_from'],
                'source_file' => $rows[0]['source_file'],
                'source_hash' => $rows[0]['source_hash'],
                'notes' => 'Imported from '.$meta['inventory_csv'].' sha256 '.hash_file('sha256', base_path($meta['inventory_csv'])),
            ]);

            $groups = [];
            foreach ($meta['groups'] as $g) {
                $groups[$g['code']] = MenuGroup::query()->firstOrCreate(['code' => $g['code']], ['name_en' => $g['name_en'], 'name_ar' => $g['name_ar'], 'sort' => $g['sort']]);
            }

            $categories = [];
            foreach ($rows as $row) {
                $code = $row['category_id'];
                if (isset($categories[$code])) {
                    continue;
                }
                $c = $meta['categories'][$code] ?? throw new RuntimeException("No metadata for {$code}.");
                $categories[$code] = MenuCategory::query()->firstOrCreate(['code' => $code], [
                    'source_name' => $row['source_category_name'],
                    'name_en' => $row['source_category_name'], // official source name (D-089, D-130)
                    'name_ar' => $c['name_ar'] ?? null,
                    'name_ar_status' => isset($c['name_ar']) ? "APPROVED ({$c['ref']})" : 'SOURCE-PROVIDED — PENDING OWNER REVIEW (P-01)',
                    'suggested_name_ar' => $c['suggested_ar'] ?? null,
                    'menu_group_id' => isset($c['group']) ? $groups[$c['group']]->id : null,
                    'type' => $c['type'] ?? 'standard',
                    'sort' => $c['sort'],
                    // F-17: the SPRING seasonal section is currently Active (manual override; dates MISSING, M-04).
                    'status' => ($c['type'] ?? 'standard') === 'seasonal' ? PublishStatus::Published->value : PublishStatus::Draft->value,
                ]);
            }

            $subcategories = [];
            $order = 0;
            foreach ($mapping as $m) {
                if ($m['subcategory_id'] === 'PENDING' || isset($subcategories[$m['subcategory_id']])) {
                    continue;
                }
                $subcategories[$m['subcategory_id']] = MenuSubcategory::query()->firstOrCreate(['code' => $m['subcategory_id']], [
                    'menu_category_id' => $categories[$m['category_id']]->id,
                    'name_en' => $m['subcategory_en'],
                    'name_ar' => $m['subcategory_ar'],
                    'status' => MenuSubcategory::STATUS_PROPOSED, // P-04: PENDING OWNER APPROVAL
                    'sort' => ++$order,
                ]);
            }

            $products = [];
            foreach ($rows as $row) {
                $products[$row['product_id']] = $this->product($row, $categories[$row['category_id']], $subcategories[$mapping->get($row['product_id'])['subcategory_id'] ?? ''] ?? null);
            }
            foreach ($rows as $index => $row) {
                $product = $products[$row['product_id']];
                if ($row['merged_into_product_id'] !== '' && $product->merged_into_id === null) {
                    $product->forceFill(['merged_into_id' => $products[$row['merged_into_product_id']]->id])->save();
                }
                $this->sourceRow($row, $product, $version, $index + 1);
                if ($product->prices()->doesntExist()) {
                    $product->prices()->create([
                        'menu_version_id' => $version->id,
                        'price_fils' => (int) $row['price_fils'],
                        'currency' => $row['currency'],
                        'tax_inclusive' => $row['tax_inclusive'] === 'true',
                        'valid_from' => $row['valid_from'],
                        'valid_to' => $row['valid_to'] !== '' ? $row['valid_to'] : null,
                        'source' => 'official_file',
                    ]);
                }
            }

            // D-094 / M-01: per-branch availability is UNKNOWN until the owner confirms it.
            foreach (Branch::query()->get() as $branch) {
                $key = MenuCatalog::availabilityFactKey($branch);
                if ($facts->current($key) === null) {
                    $facts->register($key, 'menu', null, FactStatus::Missing, FactSource::Document, sourceRef: 'D-094', notes: 'MISSING — OWNER INPUT REQUIRED (M-01)');
                }
            }
        });
    }

    /** @param array<string, string> $row */
    private function product(array $row, MenuCategory $category, ?MenuSubcategory $subcategory): Product
    {
        $retired = $row['approval_status'] === 'RETIRED';
        $arStatus = NameStatus::fromInventory($row['name_ar_status']);

        return Product::query()->firstOrCreate(['code' => $row['product_id']], [
            'menu_category_id' => $category->id,
            'menu_subcategory_id' => $subcategory?->id,
            'status' => $retired ? Product::STATUS_RETIRED : Product::STATUS_ACTIVE,
            'normalized_name_en' => $row['normalized_name_en'] ?: null,
            'normalized_name_ar' => $row['normalized_name_ar'] ?: null,
            'display_name_en' => $row['display_name_en'] ?: null,
            'display_name_ar' => $arStatus === NameStatus::Approved ? ($row['display_name_ar'] ?: null) : null,
            'name_en_status' => $row['name_en_status'],
            'name_en_decision' => $row['name_en_decision'] ?: null,
            'name_ar_status' => $row['name_ar_status'],
            'name_ar_decision' => $row['name_ar_decision'] ?: null,
            'suggested_name_ar' => $row['suggested_name_ar'] ?: null,
            'availability' => Availability::Available, // on the official menu MV-2026-10-01 (D-117 for SPRING)
            'publish_status' => PublishStatus::Draft,
            'size_info_status' => $row['size_info_status'] ?: null,
            'show_addons' => false, // D-119
            'is_seasonal' => $row['category_id'] === 'CAT-009',
            'data_quality_status' => $row['data_quality_status'] ?: null,
            'data_quality_flags' => $row['data_quality_flags'] ?: null,
            'sort' => (int) substr($row['product_id'], 4), // inventory order; manual sort_order is set in the dashboard (D-148)
        ]);
    }

    /** @param array<string, string> $row */
    private function sourceRow(array $row, Product $product, MenuVersion $version, int $position): void
    {
        // SRC-00001…SRC-00192 follow inventory order (F-02). The "#" column restarts on Sheet2, so it is not a key (D-120).
        $code = sprintf('SRC-%05d', $position);
        if (MenuSourceRow::query()->where('code', $code)->exists()) {
            return;
        }
        MenuSourceRow::query()->create([
            'code' => $code,
            'product_id' => $product->id,
            'menu_version_id' => $version->id,
            'source_sheet' => $row['source_sheet'],
            'source_row' => (int) $row['source_row'],
            'source_sequence_number' => (int) $row['source_sequence_number'],
            'source_lineage' => $row['source_lineage'],
            'source_category_name' => $row['source_category_name'],
            'source_name_en' => $row['source_name_en'],
            'source_name_ar' => $row['source_name_ar'] !== '' ? $row['source_name_ar'] : null,
            'source_price' => $row['source_price'],
        ]);
    }

    /** @param list<array<string, string>> $rows */
    private function verify(array $rows): void
    {
        $codes = array_column($rows, 'product_id');
        $sequences = array_map(fn (array $r): string => $r['source_sheet'].'#'.$r['source_sequence_number'], $rows);
        $active = count(array_filter($rows, fn (array $r): bool => $r['approval_status'] !== 'RETIRED'));
        $problems = array_filter([
            count($rows) !== self::EXPECTED_ROWS ? 'row count '.count($rows) : null,
            count(array_unique($codes)) !== count($codes) ? 'duplicate product IDs' : null,
            count(array_unique($sequences)) !== count($sequences) ? 'duplicate source sequence numbers' : null,
            $active !== self::EXPECTED_ACTIVE ? "active count {$active}" : null,
            array_filter($rows, fn (array $r): bool => ! ctype_digit($r['price_fils'])) !== [] ? 'non-integer price_fils' : null,
        ]);
        if ($problems !== []) {
            throw new RuntimeException('Menu inventory verification failed: '.implode('; ', $problems));
        }
    }

    /** @return list<array<string, string>> */
    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new RuntimeException("Cannot read {$path}.");
        }
        $header = fgetcsv($handle, escape: '\\');
        if ($header === false) {
            throw new RuntimeException("Empty file {$path}.");
        }
        $header = array_map(fn (?string $h): string => trim((string) preg_replace('/^\xEF\xBB\xBF/', '', (string) $h)), $header);
        $rows = [];
        while (($line = fgetcsv($handle, escape: '\\')) !== false) {
            if ($line === [null]) {
                continue;
            }
            $rows[] = array_combine($header, array_map(fn (?string $v): string => (string) $v, $line));
        }
        fclose($handle);

        return $rows;
    }
}
