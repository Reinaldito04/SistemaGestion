<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Database\Seeder;
use App\Services\HelpersService;
use Illuminate\Support\Facades\Schema;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();

		User::truncate();
		
		Schema::enableForeignKeyConstraints();

        $now = Carbon::now();

        $data = [
            [
                'id'                => 1,
                'name'              => 'Quereguanl',
                'email'             => 'quereguanl@sistema.net.ve',
                'email_verified_at' => $now,
                'password'          => bcrypt('123456789'),
                'role'          => 'administrador',
            ],
            [
                'id'                => 2,
                'name'              => 'Marcanoaar',
                'email'             => 'marcanoaar@sistema.net.ve',
                'email_verified_at' => $now,
                'password'          => bcrypt('123456789'),
                'role'          => 'supervisor',
            ],
            [
                'id'                => 3,
                'name'              => 'Acecedocs',
                'email'             => 'acecedocs@sistema.net.ve',
                'email_verified_at' => $now,
                'password'          => bcrypt('123456789'),
                'role'          => 'analista',
            ],
            [
                'id'                => 4,
                'name'              => 'SUPER-ADMINISTRADOR',
                'email'             => 'superadmin@sistema.net.ve',
                'email_verified_at' => $now,
                'password'          => bcrypt('123456789'),
                'role'          => 'superadministrador',
            ],
        ];

        $this->command->getOutput()->progressStart(count($data));

        foreach ($data as $key => $a) {

            $m                      = new User;
            $m->id                  = $a['id']                  ?? null;
            $m->name                = $a['name']                ?? null;
            $m->email               = $a['email']               ?? null;
            $m->email_verified_at   = $a['email_verified_at']   ?? null;
            $m->password            = $a['password']            ?? null;
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
