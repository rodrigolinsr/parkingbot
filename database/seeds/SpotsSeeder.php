<?php

use Illuminate\Database\Seeder;

class SpotsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * return void
     */
    public function run()
    {
        $spots = [
            'zainab'  => 'Zainab',
            'doddsy'  => 'Dave',
            'richard' => 'Richard',
            'keziah'  => 'Keziah',
            'sarah'   => 'Sarah',
            'hannah'  => 'Hannah',
        ];

        \Parking\Models\Spot::query()->truncate();

        foreach ($spots as $user => $description) {
            \Parking\Models\Spot::create([
                'description' => $description,
                'owner_user'  => is_string($user) ? $user : null,
            ]);
        }
    }
}
