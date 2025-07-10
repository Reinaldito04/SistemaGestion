<?php

namespace Database\Seeders;

use App\Models\Ier;
use App\Models\File;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class IerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run()
    {
        Schema::disableForeignKeyConstraints();
        
        DB::table('iers_assignables')->truncate(); 

        DB::table('files_assignables')
        ->where('assignable_type', App\Models\Ier::class)
        ->delete();

		Ier::truncate();

		Schema::enableForeignKeyConstraints();

        $now = Carbon::now();

        $data = [
                [
                'id'            => 1,
                'name'          => 'ier_2',
                'display_name'  => 'Ier 2',
                'description'   => 'El Ier 2 pertenece a la planta de Destilación',
                'active'        => true,

                'assignable' => [
                    'id'   => 1,
                  'type' => \App\Models\Plant::class, // ← Aquí va con namespace absoluto
                ],
                'files' => ['IER2 - LISTO'],
            ],
              [
                'id'            => 2,
                'name'          => 'ier_3',
                'display_name'  => 'Ier 3',
                'description'   => 'El Ier 3 pertenece a la planta de Coker',
                'active'        => true,

                'assignable' => [
                    'id'   => 2,
                  'type' => \App\Models\Plant::class, // ← Aquí va con namespace absoluto
                ],
                'files' => ['IER3 - LISTO'],
            ],
                [
                'id'            => 3,
                'name'          => 'ier_4',
                'display_name'  => 'Ier 4',
                'description'   => 'El Ier 4 pertenece a la planta de Hidroprocesos',
                'active'        => true,

                'assignable' => [
                    'id'   => 3,
                  'type' => \App\Models\Plant::class, // ← Aquí va con namespace absoluto
                ],
                'files' => ['IER4 - LISTO'],
            ],
             [
                'id'            => 4,
                'name'          => 'ier_5',
                'display_name'  => 'Ier 5',
                'description'   => 'El Ier 5 pertenece a la planta de Hidrógeno',
                'active'        => true,

                'assignable' => [
                    'id'   => 4,
                  'type' => \App\Models\Plant::class, // ← Aquí va con namespace absoluto
                ],
                'files' => ['IER5 - LISTO'],

            ],
              [
                'id'            => 5,
                'name'          => 'ier_6',
                'display_name'  => 'Ier 6',
                'description'   => 'El Ier 6 pertenece a la planta de Azufre',
                'active'        => true,

                'assignable' => [
                    'id'   => 5,
                  'type' => \App\Models\Plant::class, // ← Aquí va con namespace absoluto
                ],
                'files' => ['IER6 - LISTO'],

            ],
              [
                'id'            => 6,
                'name'          => 'ier_7',
                'display_name'  => 'Ier 7',
                'description'   => 'El Ier 7 pertenece a la planta de Servicios',
                'active'        => true,

                'assignable' => [
                    'id'   => 6,
                  'type' => \App\Models\Plant::class, // ← Aquí va con namespace absoluto
                ],
                  'files' => ['IER7 - LISTO'],
            ],

                [
                'id'            => 7,
                'name'          => 'ier_8',
                'display_name'  => 'Ier 8',
                'description'   => 'El Ier 8 pertenece a la planta de Servicios',
                'active'        => true,

                'assignable' => [
                    'id'   => 6,
                  'type' => \App\Models\Plant::class, // ← Aquí va con namespace absoluto
                ],
                'files' => ['IER8 - LISTO'],
            ],
                   [
                'id'            => 8,
                'name'          => 'ier_9',
                'display_name'  => 'Ier 9',
                'description'   => 'El Ier 9 pertenece a la planta de Servicios',
                'active'        => true,

                'assignable' => [
                    'id'   => 6,
                  'type' => \App\Models\Plant::class, // ← Aquí va con namespace absoluto
                ],
                 'files' => ['IER9 - LISTO'],
            ],
        ];

        $this->command->getOutput()->progressStart(count($data));

        foreach ($data as $key => $a) {

            $m                      = new Ier;
            $m->id                  = $a['id']                  ?? null;
            $m->name                = $a['name']                ?? null;
            $m->display_name        = $a['display_name']        ?? null;
            $m->description         = $a['description']         ?? null;
            $m->active              = $a['active']              ?? null;
            $m->save();

            if (isset($a['assignable'])) {
                    DB::table('iers_assignables')->insert([
                        'ier_id'          => $m->id,
                        'assignable_id'   => $a['assignable']['id'],
                        'assignable_type' => $a['assignable']['type'],
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                }

            if (isset($a['files']) && is_array($a['files'])) {
                foreach ($a['files'] as $fileName) {
                    $file = File::where('name', $fileName)->first();

                    if ($file) {
                        DB::table('files_assignables')->insert([
                            'file_id'         => $file->id,
                            'assignable_id'   => $m->id,
                            'assignable_type' => Ier::class,
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ]);
                    } else {
                        $this->command->warn("⚠ Archivo con nombre '{$fileName}' no encontrado.");
                    }
                }
            }

                
            usleep(50000);

            $this->command->getOutput()->progressAdvance();
        }

        $this->command->getOutput()->progressFinish();
    }
}
