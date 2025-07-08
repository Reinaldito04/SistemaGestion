<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Carbon;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

		Article::truncate();

		Schema::enableForeignKeyConstraints();

        $now = Carbon::now();

        $data = [
        [
            'id' => 1,
            'name' => 'calibración_de_instrumentos',
            'display_name' => 'Calibración de instrumentos',
            'description' => 'Forzar señal y ajustar el instrumento para evitar disparos, asegurando lectura precisa.',
            'active' => true,
            'article_type_id' => 1, // Preventivo
        ],
        [
            'id' => 2,
            'name' => 'prueba_funcional',
            'display_name' => 'Prueba funcional',
            'description' => 'Validar recorrido de válvula y activación de switches en condiciones normales.',
            'active' => true,
            'article_type_id' => 1, // Preventivo
        ],
        [
            'id' => 3,
            'name' => 'puesta_en_servicio_de_equipos',
            'display_name' => 'Puesta en servicio de equipos',
            'description' => 'Verificación de lógica, permisivos y condiciones iniciales para activación de equipos.',
            'active' => true,
            'article_type_id' => 5, // Puesta en marcha
        ],
        [
            'id' => 4,
            'name' => 'falla_por_fusible_quemado',
            'display_name' => 'Falla por fusible quemado',
            'description' => 'Corrección de avería generada por sobrecarga eléctrica o corto en circuito.',
            'active' => true,
            'article_type_id' => 3, // Correctivo
        ],
        [
            'id' => 5,
            'name' => 'punto_dañado_en_tarjeta',
            'display_name' => 'Punto dañado en tarjeta',
            'description' => 'Reparación de componente electrónico con daño físico o de señal.',
            'active' => true,
            'article_type_id' => 3, // Correctivo
        ],
        [
            'id' => 6,
            'name' => 'falla_del_instrumento',
            'display_name' => 'Falla del instrumento',
            'description' => 'Intervención por mal funcionamiento total o parcial del equipo de medición.',
            'active' => true,
            'article_type_id' => 3, // Correctivo
        ],
        [
            'id' => 7,
            'name' => 'falla_de_continuidad_en_cableado',
            'display_name' => 'Falla de continuidad en cableado',
            'description' => 'Solución de interrupción en el flujo eléctrico o de señal por cables defectuosos.',
            'active' => true,
            'article_type_id' => 3, // Correctivo
        ],
];


        $this->command->getOutput()->progressStart(count($data));

        foreach ($data as $key => $a) {

            $m                      = new Article;
            $m->id                  = $a['id']                  ?? null;
            $m->name                = $a['name']                ?? null;
            $m->display_name        = $a['display_name']        ?? null;
            $m->description         = $a['description']         ?? null;
            $m->active              = $a['active']              ?? null;
            $m->article_type_id     = $a['article_type_id']     ?? null;
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
