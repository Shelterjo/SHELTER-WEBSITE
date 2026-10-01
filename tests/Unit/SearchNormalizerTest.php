<?php

namespace Tests\Unit;

use App\Services\Content\Search\Normalizer;
use PHPUnit\Framework\TestCase;

/** GS-T1: the PHP normaliser gives exactly the outputs of the shared examples file (also run by search.test.ts). */
class SearchNormalizerTest extends TestCase
{
    /** @return array{normalize: list<array{0: string, 1: string}>, matches: list<array{0: string, 1: list<string>, 2: bool}>} */
    private function examples(): array
    {
        /** @var array{normalize: list<array{0: string, 1: string}>, matches: list<array{0: string, 1: list<string>, 2: bool}>} $data */
        $data = json_decode((string) file_get_contents(__DIR__.'/../fixtures/search-normalization.json'), true, flags: JSON_THROW_ON_ERROR);

        return $data;
    }

    public function test_normalize_matches_the_shared_examples(): void
    {
        foreach ($this->examples()['normalize'] as [$input, $expected]) {
            $this->assertSame($expected, Normalizer::normalize($input), "normalize({$input})");
        }
    }

    public function test_matches_follows_the_shared_examples(): void
    {
        foreach ($this->examples()['matches'] as [$query, $terms, $expected]) {
            $this->assertSame($expected, Normalizer::matches($query, $terms), "matches({$query})");
        }
    }

    public function test_one_edit_tolerance_is_optional(): void
    {
        $this->assertTrue(Normalizer::tokensMatch(['spanich'], ['spanish']));
        $this->assertFalse(Normalizer::tokensMatch(['spanich'], ['spanish'], tolerant: false));
        $this->assertTrue(Normalizer::withinOneEdit('سبانش', 'سبانيش'));
        $this->assertFalse(Normalizer::withinOneEdit('latte', 'lote'));
    }
}
