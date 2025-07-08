<?php

namespace Database\Seeders;

use App\Models\ArticleType;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ArticleTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
      public function run()
    {
        Schema::disableForeignKeyConstraints();

		ArticleType::truncate();

		Schema::enableForeignKeyConstraints();

        $now = Carbon::now();

        $data = [
           
            [
                'id' => 1,
                'name' => 'mantenimiento_preventivo',
                'display_name' => 'Mantenimiento Preventivo',
                'description' => 'Actividades planificadas para anticipar y evitar fallas en equipos o sistemas antes de que ocurran.',
                'active' => true,
            ],
            [
                'id' => 3,
                'name' => 'mantenimiento_correctivo',
                'display_name' => 'Mantenimiento Correctivo',
                'description' => 'Intervenciones realizadas para reparar componentes que ya han fallado o presentan averías detectadas.',
                'active' => true,
            ],
            [
                'id' => 5,
                'name' => 'puesta_en_marcha',
                'display_name' => 'Puesta en Marcha',
                'description' => 'Actividades realizadas durante la instalación o activación inicial de equipos para validar lógica, permisivos y funcionalidad.',
                'active' => true,
            ],


        ];

        $this->command->getOutput()->progressStart(count($data));

        foreach ($data as $key => $a) {

            $m                      = new   ArticleType;
            $m->id                  = $a['id']                  ?? null;
            $m->name                = $a['name']                ?? null;
            $m->display_name        = $a['display_name']        ?? null;
            $m->description         = $a['description']         ?? null;
            $m->active              = $a['active']              ?? null;
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
