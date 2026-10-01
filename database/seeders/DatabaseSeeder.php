<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seeds only owner-approved master data. No demo users or content: the owner account is created
     * on the server with `php artisan shelter:owner`.
     */
    public function run(): void
    {
        $this->call(MasterDataSeeder::class);
    }
}
