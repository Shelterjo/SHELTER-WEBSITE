<?php

namespace Tests\Support;

use App\Models\Branch;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * The Owner's way to change a branch's details (BRANCH-010): «Preview» first, then «Publish» what the preview showed —
 * the publish carries the preview's fingerprint, read from the preview page as the browser would send it.
 */
trait PublishesBranchDetails
{
    /**
     * @param  array<string, mixed>  $form
     * @return TestResponse<Response> the publish response
     */
    protected function publishBranchDetails(Branch $branch, array $form): TestResponse
    {
        $url = '/dashboard/data/branches/'.$branch->id.'/details';
        $preview = (string) $this->put($url, $form + ['action' => 'preview'])->assertOk()->getContent();
        preg_match('/name="previewed" value="([a-f0-9]{64})"/', $preview, $m);
        $fingerprint = $m[1] ?? '';
        $this->assertNotSame('', $fingerprint, 'the preview carries its fingerprint');

        return $this->put($url, $form + ['action' => 'publish', 'previewed' => $fingerprint]);
    }
}
