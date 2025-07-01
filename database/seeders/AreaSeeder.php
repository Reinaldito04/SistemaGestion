<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
      public function run()
    {
        Schema::disableForeignKeyConstraints();

		Area::truncate();

		Schema::enableForeignKeyConstraints();

        $now = Carbon::now();

        $data = [
            [
                'id'                => 1,
                'name'              => 'Area 1',
                'display_name'      => 'Area 1',
                'description'       => 'Esta es el area 1',
                'active'           => true,
            ],
            [
                'id'                => 2,
                'name'              => 'Area 2',
                'display_name'      => 'Area 2',
                'description'       => 'Esta es el area 2',
                'active'           => true,
            ],
            [
                'id'                => 3,
                'name'              => 'Area 3',
                'display_name'      => 'Area 3',
                'description'       => 'Esta es el area 3',
                'active'           => true,
            ],
        ];

        $this->command->getOutput()->progressStart(count($data));

        foreach ($data as $key => $a) {

            $m                      = new   Area;
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
