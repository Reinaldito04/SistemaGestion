<?php

namespace Database\Seeders;

use App\Models\Sector;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

            Sector::truncate();

            Schema::enableForeignKeyConstraints();

            $now = Carbon::now();

            $data = [
                [
                    'id'                => 1,
                    'name'              => "Sector 1100",
                    'display_name'      => "sector_1100",
                    'description'       => "Este es el sector 1100",
                    'active'            => true,
                    'plant_id'          => 1,
                ],
                [
                    'id'                => 2,
                    'name'              => "Sector 1200",
                    'display_name'      => "sector_1200",
                    'description'       => "Este es el sector 1200",
                    'active'            => true,
                    'plant_id'          => 1,
                ],
                [
                    'id'                => 3,
                    'name'              => "Sector 1300",
                    'display_name'      => "sector_1300",
                    'description'       => "Este es el sector 1300",
                    'active'            => true,
                    'plant_id'          => 2,
                ],
                [
                    'id'                => 4,
                    'name'              => "Sector 1400",
                    'display_name'      => "sector_1400",
                    'description'       => "Este es el sector 1400",
                    'active'            => true,
                    'plant_id'          => 2,
                ],
                [
                    'id'                => 5,
                    'name'              => "Sector 1500",
                    'display_name'      => "sector_1500",
                    'description'       => "Este es el sector 1500",
                    'active'            => true,
                    'plant_id'          => 2,
                ],
                [
                    'id'                => 6,
                    'name'              => "Sector 1600",
                    'display_name'      => "sector_1600",
                    'description'       => "Este es el sector 1600",
                    'active'            => true,
                    'plant_id'          => 2,
                ],

                [
                    'id'                => 7,  // ← Empezamos desde 7
                    'name'              => "Sector 2100",
                    'display_name'      => "sector_2100",
                    'description'       => "Este es el sector 2100",
                    'active'           => true,
                    'plant_id'          => 3,
                ],
       
                [
                    'id'                => 8,
                    'name'              => "Sector 2300",
                    'display_name'      => "sector_2300",
                    'description'       => "Este es el sector 2300",
                    'active'           => true,
                    'plant_id'          => 3,
                ],
                [
                    'id'                => 9,
                    'name'              => "Sector 2400",
                    'display_name'      => "sector_2400",
                    'description'       => "Este es el sector 2400",
                    'active'           => true,
                    'plant_id'          => 3,
                ],
                [
                    'id'                => 10,  // ← Este sería tu último ID existente (según lo indicado)
                    'name'              => "Sector 2500",
                    'display_name'      => "sector_2500",
                    'description'       => "Este es el sector 2500",
                    'active'           => true,
                    'plant_id'          => 4,
                ],
                [
                    'id'                => 11,
                    'name'              => "Sector 2600",
                    'display_name'      => "sector_2600",
                    'description'       => "Este es el sector 2600",
                    'active'           => true,
                    'plant_id'          => 4,
                ],    
                [
                    'id'                => 12,
                    'name'              => "Sector 3100",
                    'display_name'      => "sector_3100",
                    'description'       => "Este es el sector 3100",
                    'active'           => true,
                    'plant_id'          => 5,
                ],
                [
                    'id'                => 13,
                    'name'              => "Sector 3200",
                    'display_name'      => "sector_3200",
                    'description'       => "Este es el sector 3200",
                    'active'           => true,
                    'plant_id'          => 5,
                ],
                [
                    'id'                => 14,
                    'name'              => "Sector 3300",
                    'display_name'      => "sector_3300",
                    'description'       => "Este es el sector 3300",
                    'active'           => true,
                    'plant_id'          => 5,
                ],
                [
                    'id'                => 15,
                    'name'              => "Sector 3400",
                    'display_name'      => "sector_3400",
                    'description'       => "Este es el sector 3400",
                    'active'           => true,
                    'plant_id'          => 5,
                ],
                [
                    'id'                => 16,
                    'name'              => "Sector 3500",
                    'display_name'      => "sector_3500",
                    'description'       => "Este es el sector 3500",
                    'active'           => true,
                    'plant_id'          => 5,
                ],
                [
                    'id'                => 17,
                    'name'              => "Sector 3600",
                    'display_name'      => "sector_3600",
                    'description'       => "Este es el sector 3600",
                    'active'           => true,
                    'plant_id'          => 5,
                ],
                [
                    'id'                => 18,
                    'name'              => "Sector 3700",
                    'display_name'      => "sector_3700",
                    'description'       => "Este es el sector 3700",
                    'active'           => true,
                    'plant_id'          => 5,
                ],
                [
                    'id'                => 19,
                    'name'              => "Sector 3800",
                    'display_name'      => "sector_3800",
                    'description'       => "Este es el sector 3800",
                    'active'            => true,
                    'plant_id'          => 5,
                ],
                [
                    'id'                => 20,
                    'name'              => "Sector 4100",
                    'display_name'      => "sector_4100",
                    'description'       => "Este es el sector 4100",
                    'active'            => true,
                    'plant_id'          => 5,
                ],
                [
                    'id'                => 21,
                    'name'              => "Sector 5100",
                    'display_name'      => "sector_5100",
                    'description'       => "Este es el sector 5100",
                    'active'            => true,
                    'plant_id'          => 6,
                ],
                [
                    'id'                => 22,
                    'name'              => "Sector 5200",
                    'display_name'      => "sector_5200",
                    'description'       => "Este es el sector 5200",
                    'active'            => true,
                    'plant_id'          => 6,
                ],
                [
                    'id'                => 23,
                    'name'              => "Sector 5300",
                    'display_name'      => "sector_5300",
                    'description'       => "Este es el sector 5300",
                    'active'            => true,
                    'plant_id'          => 6,
                ],
                [
                    'id'                => 24,
                    'name'              => "Sector 5400",
                    'display_name'      => "sector_5400",
                    'description'       => "Este es el sector 5400",
                    'active'            => true,
                    'plant_id'          => 6,
                ],
                [
                    'id'                => 25,
                    'name'              => "Sector 6100",
                    'display_name'      => "sector_6100",
                    'description'       => "Este es el sector 6100",
                    'active'            => true,
                    'plant_id'          => 6,
                ],
                [
                    'id'                => 26,
                    'name'              => "Sector 6200",
                    'display_name'      => "sector_6200",
                    'description'       => "Este es el sector 6200",
                    'active'            => true,
                    'plant_id'          => 6,
                ],
                [
                    'id'                => 27,
                    'name'              => "Sector 6300",
                    'display_name'      => "sector_6300",
                    'description'       => "Este es el sector 6300",
                    'active'            => true,
                    'plant_id'          => 6,
                ],
                [
                    'id'                => 28,
                    'name'              => "Sector 6400",
                    'display_name'      => "sector_6400",
                    'description'       => "Este es el sector 6400",
                    'active'            => true,
                    'plant_id'          => 6,
                ],
                [
                    'id'                => 29,
                    'name'              => "Sector 6500",
                    'display_name'      => "sector_6500",
                    'description'       => "Este es el sector 6500",
                    'active'            => true,
                    'plant_id'          => 6,
                ],
                [
                    'id'                => 30,
                    'name'              => "Sector 6600",
                    'display_name'      => "sector_6600",
                    'description'       => "Este es el sector 6600",
                    'active'            => true,
                    'plant_id'          => 6,
                ],
                [
                    'id'                => 31,
                    'name'              => "Sector 6700",
                    'display_name'      => "sector_6700",
                    'description'       => "Este es el sector 6700",
                    'active'            => true,
                    'plant_id'          => 6,
                ],
                [
                    'id'                => 32,
                    'name'              => "Sector 6800",
                    'display_name'      => "sector_6800",
                    'description'       => "Este es el sector 6800",
                    'active'            => true,
                    'plant_id'          => 6,
                ],
                [
                    'id'                => 33,
                    'name'              => "Sector 6900",
                    'display_name'      => "sector_6900",
                    'description'       => "Este es el sector 6900",
                    'active'            => true,
                    'plant_id'          => 6,
                ],
                [
                    'id'                => 34,
                    'name'              => "Sector 7100",
                    'display_name'      => "sector_7100",
                    'description'       => "Este es el sector 7100",
                    'active'            => true,
                    'plant_id'          => 6,
                ],
                [
                    'id'                => 35,
                    'name'              => "Sector 7200",
                    'display_name'      => "sector_7200",
                    'description'       => "Este es el sector 7200",
                    'active'            => true,
                    'plant_id'          => 6,
                ],
                [
                    'id'                => 36,
                    'name'              => "Sector 7300",
                    'display_name'      => "sector_7300",
                    'description'       => "Este es el sector 7300",
                    'active'            => true,
                    'plant_id'          => 6,
                ],
                [
                    'id'                => 37,
                    'name'              => "Sector 8100",
                    'display_name'      => "sector_8100",
                    'description'       => "Este es el sector 8100",
                    'active'            => true,
                    'plant_id'          => 6,
                ],
                [
                    'id'                => 38,
                    'name'              => "Sector 8200",
                    'display_name'      => "sector_8200",
                    'description'       => "Este es el sector 8200",
                    'active'            => true,
                    'plant_id'          => 6,
                ],
                [
                    'id'                => 39,
                    'name'              => "Sector 8300",
                    'display_name'      => "sector_8300",
                    'description'       => "Este es el sector 8300",
                    'active'            => true,
                    'plant_id'          => 6,
                ],
                [
                    'id'                => 40,
                    'name'              => "Sector 8400",
                    'display_name'      => "sector_8400",
                    'description'       => "Este es el sector 8400",
                    'active'            => true,
                    'plant_id'          => 6,
                ],
                [
                    'id'                => 41,
                    'name'              => "Sector 8500",
                    'display_name'      => "sector_8500",
                    'description'       => "Este es el sector 8500",
                    'active'            => true,
                    'plant_id'          => 6,
                ],
                [
                    'id'                => 42,
                    'name'              => "Sector 8600",
                    'display_name'      => "sector_8600",
                    'description'       => "Este es el sector 8600",
                    'active'            => true,
                    'plant_id'          => 6,
                ],
                [
                    'id'                => 43,
                    'name'              => "Sector 8700",
                    'display_name'      => "sector_8700",
                    'description'       => "Este es el sector 8700",
                    'active'            => true,
                    'plant_id'          => 6,
                ],
                [
                    'id'                => 44,
                    'name'              => "Sector 8800",
                    'display_name'      => "sector_8800",
                    'description'       => "Este es el sector 8800",
                    'active'            => true,
                    'plant_id'          => 6,
                ],
                [
                    'id'                => 45,
                    'name'              => "Sector 8900",
                    'display_name'      => "sector_8900",
                    'description'       => "Este es el sector 8900",
                    'active'            => true,
                    'plant_id'          => 6,
                ],

];

            




            $this->command->getOutput()->progressStart(count($data));

            foreach ($data as $key => $a) {

                $m                      = new Sector;
                $m->id                  = $a['id']                  ?? null;
                $m->name                = $a['name']                ?? null;
                $m->display_name        = $a['display_name']        ?? null;
                $m->description         = $a['description']         ?? null;
                $m->active              = $a['active']              ?? null;
                $m->plant_id             = $a['plant_id']             ?? null;
                $m->save();


                if (isset($a['role'])) {
                    $m->addRole($a['role']);
                }

                usleep(50000);

                $this->command->getOutput()->progressAdvance();
            }

            $this->command->getOutput()->progressFinish();
    }

}

