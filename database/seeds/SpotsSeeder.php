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
            'alex'          => 'Alex',
            'sarah'         => 'Sarah',
            'dave.mcgregor' => 'Dave McGregor',
            'spare1'        => 'Spare 1',
        ];

        \Parking\Models\Spot::query()->truncate();

        foreach ($spots as $user => $description) {
            \Parking\Models\Spot::create([
                'description' => $description,
                'owner_user'  => is_string($user) ? $user : null,
            ]);

            // Spare spots are always free
            if (starts_with($user, 'spare')) {
                /** @var \Parking\Models\Spot $spot */
                $spot = \Parking\Models\Spot::where('owner_user', $user)->first();

                $attributes = [
                    'date_from' => \Carbon\Carbon::create(2016, 1, 1),
                    'date_to'   => \Carbon\Carbon::create(2100, 12, 31),
                ];

                $spot->freeSpots()->updateOrCreate($attributes, $attributes);
            }
        }
    }
}
