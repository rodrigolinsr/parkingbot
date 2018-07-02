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
            'zainab'        => 'Zainab',
            'doddsy'        => 'Doddsy',
            'richard'       => 'Richard',
            'keziah'        => 'Keziah',
            'sarah'         => 'Sarah',
            'dave.mcgregor' => 'Dave McGregor',
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
