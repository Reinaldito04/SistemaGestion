<?php

namespace Database\Seeders;

use App\Models\Plant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Carbon;

class PlantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
  public function run()
    {
        Schema::disableForeignKeyConstraints();

		Plant::truncate();

		Schema::enableForeignKeyConstraints();

        $now = Carbon::now();

        $data = [
            [
                'id'                => 1,
                'name'              => "destilacion",
                'display_name'      => "Destilación",
                'description'       => "Planta de destilación",
                'active'           => true,
                'area_id'          => 1,
            ],
            [
                'id'                => 2,
                'name'              => "coker",
                'display_name'      => "Coker",
                'description'       => "Planta de coker",
                'active'           => true,
                'area_id'          => 1,
            ],
               [
                'id'                => 3,
                'name'              => "hidroprocesos",
                'display_name'      => "Hidroprocesos",
                'description'       => "Planta de hidroprocesos",
                'active'           => true,
                'area_id'          => 2,
            ],
                 [
                'id'                => 4,
                'name'              => "hidrogeno",
                'display_name'      => "Hidrógeno",
                'description'       => "Planta de hidrógeno",
                'active'           => true,
                'area_id'          => 2,
            ],
               [
                'id'                => 5,
                'name'              => "azufre",
                'display_name'      => "Azufre",
                'description'       => "Planta de azufre",
                'active'           => true,
                'area_id'          => 2,
            ],
             [
                'id'                => 6,
                'name'              => "servicios",
                'display_name'      => "Servicios",
                'description'       => "Planta de servicios",
                'active'           => true,
                'area_id'          => 3,
            ],
        ];

        $this->command->getOutput()->progressStart(count($data));

        foreach ($data as $key => $a) {

            $m                      = new Plant;
            $m->id                  = $a['id']                  ?? null;
            $m->name                = $a['name']                ?? null;
            $m->display_name        = $a['display_name']        ?? null;
            $m->description         = $a['description']         ?? null;
            $m->active              = $a['active']              ?? null;
            $m->area_id             = $a['area_id']             ?? null;
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
