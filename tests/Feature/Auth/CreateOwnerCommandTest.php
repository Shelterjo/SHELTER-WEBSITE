<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\PendingCommand;
use Tests\TestCase;

class CreateOwnerCommandTest extends TestCase
{
    use RefreshDatabase;

    private function command(string $email): PendingCommand
    {
        $pending = $this->artisan('shelter:owner', ['email' => $email]);
        $this->assertInstanceOf(PendingCommand::class, $pending);

        return $pending;
    }

    public function test_creates_the_owner_with_a_strong_password(): void
    {
        $this->command('Owner@Example.test')
            ->expectsQuestion('Password (min. 12 characters, letters and numbers)', 'strong pass 2026')
            ->expectsQuestion('Repeat password', 'strong pass 2026')
            ->assertSuccessful();

        $owner = User::query()->sole();
        $this->assertSame('owner@example.test', $owner->email);
        $this->assertTrue($owner->isOwner());
        $this->assertFalse($owner->hasTwoFactorEnabled());
    }

    public function test_rejects_weak_passwords(): void
    {
        $this->command('owner@example.test')
            ->expectsQuestion('Password (min. 12 characters, letters and numbers)', 'short1')
            ->expectsQuestion('Repeat password', 'short1')
            ->assertFailed();
        $this->assertSame(0, User::query()->count());
    }

    public function test_refuses_a_second_owner(): void
    {
        User::factory()->create();

        $this->command('second@example.test')->assertFailed();
    }
}
