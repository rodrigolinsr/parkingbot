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
            'zainab'        => 'Zainab (17)',
            'doddsy'        => 'Doddsy (21)',
            'richard'       => 'Richard (23)',
            'alex'          => 'Alex (61)',
            'sarah'         => 'Sarah (89)',
            'dave.mcgregor' => 'Dave McGregor (20)',
            'spare1'        => 'Spare 1 (60)',
            'emmap'         => 'Emma Parsons (Agrigate) (73)',
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
