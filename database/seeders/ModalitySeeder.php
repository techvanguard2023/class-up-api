<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Modality;

class ModalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modalities = [
            [
                'name' => 'Natação',
                'description' => 'Aulas de natação para todas as idades.',
            ],
            [
                'name' => 'Hidroginástica',
                'description' => 'Aulas de hidroginástica para todas as idades.',
            ],
            [
                'name' => 'Ballet',
                'description' => 'Aulas de ballet para todas as idades.',
            ],
            [
                'name' => 'Dança',
                'description' => 'Aulas de dança para todas as idades.',
            ],
            [
                'name' => 'Futsal',
                'description' => 'Aulas de futsal para todas as idades.',
            ],
            [
                'name' => 'Judô',
                'description' => 'Aulas de judô para todas as idades.',
            ],
            [
                'name' => 'Karatê',
                'description' => 'Aulas de karatê para todas as idades.',
            ],
            [
                'name' => 'Taekwondo',
                'description' => 'Aulas de taekwondo para todas as idades.',
            ],
            [
                'name' => 'Muay Thai',
                'description' => 'Aulas de muay thai para todas as idades.',
            ],
            [
                'name' => 'Boxe',
                'description' => 'Aulas de boxe para todas as idades.',
            ],
            [
                'name' => 'Jiu-Jitsu',
                'description' => 'Aulas de jiu-jitsu para todas as idades.',
            ],
            [
                'name' => 'Capoeira',
                'description' => 'Aulas de capoeira para todas as idades.',
            ],
            [
                'name' => 'Skate',
                'description' => 'Aulas de skate para todas as idades.',
            ],
        ];

        foreach ($modalities as $modality) {
            Modality::updateOrCreate(
            ['name' => $modality['name']],
            ['description' => $modality['description'], 'school_id' => null]
            );
        }
    }
}
