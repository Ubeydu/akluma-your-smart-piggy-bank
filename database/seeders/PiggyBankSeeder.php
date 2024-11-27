<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PiggyBank;

class PiggyBankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 10 PiggyBank records using the factory
        PiggyBank::factory()->count(3)->create();
    }
}
