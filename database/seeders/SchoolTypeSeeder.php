<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolType;

class SchoolTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schoolTypes = [
            ['name' => 'Escola', 'description' => 'Escola'],
            ['name' => 'Colégio', 'description' => 'Colégio'],
            ['name' => 'Centro de Treinamento', 'description' => 'Centro de Treinamento'],
            ['name' => 'Academia', 'description' => 'Academia'],
            ['name' => 'Curso', 'description' => 'Curso'],
            ['name' => 'Instituto', 'description' => 'Instituto'],
            ['name' => 'Seminário', 'description' => 'Seminário'],
            ['name' => 'Outro', 'description' => 'Outro'],
        ];

        foreach ($schoolTypes as $schoolType) {
            SchoolType::create($schoolType);
        }
    }
}
