<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(FillUsers::class);
        $this->call(FillShortenUrls::class);
        $this->call(FillReferersTable::class);
        $this->call(FillRefererUrlRelationsTable::class);
    }
}
