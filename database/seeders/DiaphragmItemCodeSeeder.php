<?php

namespace Database\Seeders;

use App\Models\DiaphragmItemCode;
use Illuminate\Database\Seeder;

class DiaphragmItemCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $itemCodes = [
            [
                'item_code' => 'DFB8402000',
                'item_name' => null,
                'strength_min' => 0.40,
                'measurement_1_type' => 'data_recording',
                'measurement_1_min' => null,
                'measurement_1_max' => null,
                'circumference_diff_type' => 'data_recording',
                'circumference_diff_max' => null,
            ],
            [
                'item_code' => 'DFB8402001',
                'item_name' => null,
                'strength_min' => 0.40,
                'measurement_1_type' => 'data_recording',
                'measurement_1_min' => null,
                'measurement_1_max' => null,
                'circumference_diff_type' => 'data_recording',
                'circumference_diff_max' => null,
            ],
            [
                'item_code' => 'DFB660220P',
                'item_name' => null,
                'strength_min' => 0.30,
                'measurement_1_type' => 'data_recording',
                'measurement_1_min' => null,
                'measurement_1_max' => null,
                'circumference_diff_type' => 'data_recording',
                'circumference_diff_max' => null,
            ],
            [
                'item_code' => 'DFB660023P',
                'item_name' => null,
                'strength_min' => 0.30,
                'measurement_1_type' => 'range',
                'measurement_1_min' => 7.890,
                'measurement_1_max' => 7.950,
                'circumference_diff_type' => 'max_limit',
                'circumference_diff_max' => 0.30,
            ],
            [
                'item_code' => 'DFB660024P',
                'item_name' => null,
                'strength_min' => 0.30,
                'measurement_1_type' => 'range',
                'measurement_1_min' => 7.890,
                'measurement_1_max' => 7.950,
                'circumference_diff_type' => 'max_limit',
                'circumference_diff_max' => 0.30,
            ],
            [
                'item_code' => 'DFB660050P',
                'item_name' => null,
                'strength_min' => 0.30,
                'measurement_1_type' => 'range',
                'measurement_1_min' => 8.250,
                'measurement_1_max' => 8.390,
                'circumference_diff_type' => 'max_limit',
                'circumference_diff_max' => 0.30,
            ],
            [
                'item_code' => 'DFB6602000',
                'item_name' => null,
                'strength_min' => 0.30,
                'measurement_1_type' => 'data_recording',
                'measurement_1_min' => null,
                'measurement_1_max' => null,
                'circumference_diff_type' => 'data_recording',
                'circumference_diff_max' => null,
            ],
            [
                'item_code' => 'DFB6602001',
                'item_name' => null,
                'strength_min' => 0.30,
                'measurement_1_type' => 'data_recording',
                'measurement_1_min' => null,
                'measurement_1_max' => null,
                'circumference_diff_type' => 'data_recording',
                'circumference_diff_max' => null,
            ],
            [
                'item_code' => 'DFB5902000',
                'item_name' => null,
                'strength_min' => 0.30,
                'measurement_1_type' => 'data_recording',
                'measurement_1_min' => null,
                'measurement_1_max' => null,
                'circumference_diff_type' => 'data_recording',
                'circumference_diff_max' => null,
            ],
            [
                'item_code' => 'DFB5902001',
                'item_name' => null,
                'strength_min' => 0.30,
                'measurement_1_type' => 'data_recording',
                'measurement_1_min' => null,
                'measurement_1_max' => null,
                'circumference_diff_type' => 'data_recording',
                'circumference_diff_max' => null,
            ],
            [
                'item_code' => 'DFB4805004',
                'item_name' => null,
                'strength_min' => 0.30,
                'measurement_1_type' => 'data_recording',
                'measurement_1_min' => null,
                'measurement_1_max' => null,
                'circumference_diff_type' => 'data_recording',
                'circumference_diff_max' => null,
            ],
            [
                'item_code' => 'DFB6602201',
                'item_name' => null,
                'strength_min' => 0.30,
                'measurement_1_type' => 'data_recording',
                'measurement_1_min' => null,
                'measurement_1_max' => null,
                'circumference_diff_type' => 'data_recording',
                'circumference_diff_max' => null,
            ],
            [
                'item_code' => 'DFB660240P',
                'item_name' => null,
                'strength_min' => 0.30,
                'measurement_1_type' => 'range',
                'measurement_1_min' => 7.890,
                'measurement_1_max' => 7.950,
                'circumference_diff_type' => 'max_limit',
                'circumference_diff_max' => 0.30,
            ],
            [
                'item_code' => 'DFB660221P',
                'item_name' => null,
                'strength_min' => 0.30,
                'measurement_1_type' => 'data_recording',
                'measurement_1_min' => null,
                'measurement_1_max' => null,
                'circumference_diff_type' => 'data_recording',
                'circumference_diff_max' => null,
            ],
            [
                'item_code' => 'DFB480300P',
                'item_name' => null,
                'strength_min' => 0.15,
                'measurement_1_type' => 'range',
                'measurement_1_min' => 7.280,
                'measurement_1_max' => 7.590,
                'circumference_diff_type' => 'data_recording',
                'circumference_diff_max' => null,
            ],
        ];

        foreach ($itemCodes as $itemCode) {
            DiaphragmItemCode::updateOrCreate(
                ['item_code' => $itemCode['item_code']],
                $itemCode
            );
        }

        $this->command->info('Diaphragm item codes seeded successfully!');
    }
}
