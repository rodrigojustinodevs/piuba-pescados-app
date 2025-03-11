<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class TankTypeSeeder extends Seeder
{
    public function run()
    {
        DB::table('tank_types')->insert([
            ['id' => (string) Str::uuid(), 'name' => 'Tanques de Terra', 'description' => 'Construídos no solo com impermeabilização para evitar vazamentos.'],
            ['id' => (string) Str::uuid(), 'name' => 'Tanques Redondos', 'description' => 'Formato circular, construídos em concreto ou metal para melhorar o fluxo de água.'],
            ['id' => (string) Str::uuid(), 'name' => 'Tanques de Plástico', 'description' => 'Feitos de plástico durável, usados em sistemas menores ou para reprodução.'],
            ['id' => (string) Str::uuid(), 'name' => 'Tanques de Concreto', 'description' => 'Construídos com paredes de concreto, duráveis e fáceis de manter.'],
            ['id' => (string) Str::uuid(), 'name' => 'Tanques de Membrana', 'description' => 'Utilizam lonas de PVC ou materiais similares, fáceis de instalar.'],
            ['id' => (string) Str::uuid(), 'name' => 'Tanques de Recirculação', 'description' => 'Sistemas tecnológicos que reutilizam a água com filtragem avançada.'],
            ['id' => (string) Str::uuid(), 'name' => 'Tanques Flutuantes', 'description' => 'Estruturas flutuantes utilizadas em lagos, rios ou oceanos.'],
            ['id' => (string) Str::uuid(), 'name' => 'Sistemas de Tanques Interligados', 'description' => 'Conjunto de tanques conectados por canais para controle da movimentação da água.']
        ]);
    }
}
