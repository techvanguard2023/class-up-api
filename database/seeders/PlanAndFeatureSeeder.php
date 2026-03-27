<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanAndFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create features
        $features = [
            [
                'name' => 'gerenciar_estudantes',
                'description' => 'Adicionar, editar e gerenciar informações de estudantes na plataforma',
            ],
            [
                'name' => 'gerenciar_turmas',
                'description' => 'Criar e gerenciar turmas, séries e grupos de estudantes',
            ],
            [
                'name' => 'gerenciar_notas',
                'description' => 'Lançar, editar e acompanhar notas e avaliações dos estudantes',
            ],
            [
                'name' => 'rastrear_frequencia',
                'description' => 'Registrar e acompanhar a frequência e presença dos estudantes',
            ],
            [
                'name' => 'gerar_certificados',
                'description' => 'Gerar certificados digitais customizados para estudantes',
            ],
            [
                'name' => 'relatorios_basicos',
                'description' => 'Acessar relatórios básicos de desempenho e frequência',
            ],
            [
                'name' => 'relatorios_avancados',
                'description' => 'Acessar relatórios avançados com análises detalhadas e gráficos',
            ],
            [
                'name' => 'acesso_api',
                'description' => 'Integrar com sistemas externos através de API REST',
            ],
            [
                'name' => 'suporte_prioritario',
                'description' => 'Suporte técnico prioritário via email e chat',
            ],
        ];

        $featureObjects = [];
        foreach ($features as $feature) {
            $featureObjects[$feature['name']] = Feature::firstOrCreate(
            ['name' => $feature['name']],
                $feature
            );
        }

        // Create plans
        $plans = [
            [
                'name' => 'Gratuito',
                'description' => 'Ideal para escolas pequenas começarem',
                'price' => 0.00,
                'stripe_price_id' => 'price_1TFHORRl5DyR8YOYf2EcqLCJ',
                'billing_cycle' => 'monthly',
                'color' => 'gray',
                'active' => true,
                'features' => [
                    'gerenciar_estudantes' => 10, // Limit to 10 students
                    'gerenciar_turmas' => 1,
                    'gerenciar_notas' => null, // Unlimited
                    'rastrear_frequencia' => null,
                    'gerar_certificados' => null,
                    'relatorios_basicos' => null,
                ],
            ],
            [
                'name' => 'Bronze',
                'description' => 'Perfeito para pequenas e médias escolas',
                'price' => 79.90,
                'stripe_price_id' => 'price_1TFHOsRl5DyR8YOY8fDgrECt',
                'billing_cycle' => 'monthly',
                'color' => 'amber',
                'active' => true,
                'features' => [
                    'gerenciar_estudantes' => 50, // 50 students
                    'gerenciar_turmas' => 5,
                    'gerenciar_notas' => null,
                    'rastrear_frequencia' => null,
                    'gerar_certificados' => null,
                    'relatorios_basicos' => null,
                ],
            ],
            [
                'name' => 'Prata',
                'description' => 'Para escolas médias com recursos avançados',
                'price' => 119.90,
                'stripe_price_id' => 'price_1TFHPJRl5DyR8YOYk8XgTvwW',
                'billing_cycle' => 'monthly',
                'color' => 'slate',
                'active' => true,
                'features' => [
                    'gerenciar_estudantes' => 150, // 150 students
                    'gerenciar_turmas' => 10,
                    'gerenciar_notas' => null,
                    'rastrear_frequencia' => null,
                    'gerar_certificados' => null,
                    'relatorios_basicos' => null,
                    'relatorios_avancados' => null,
                    'acesso_api' => null,
                ],
            ],
            [
                'name' => 'Ouro',
                'description' => 'Plano premium para grandes instituições educacionais',
                'price' => 199.90,
                'stripe_price_id' => 'price_1TFHPcRl5DyR8YOYUve7P51T',
                'billing_cycle' => 'monthly',
                'color' => 'yellow',
                'active' => true,
                'features' => [
                    'gerenciar_estudantes' => null, // Unlimited
                    'gerenciar_turmas' => null,
                    'gerenciar_notas' => null,
                    'rastrear_frequencia' => null,
                    'gerar_certificados' => null,
                    'relatorios_basicos' => null,
                    'relatorios_avancados' => null,
                    'acesso_api' => null,
                    'suporte_prioritario' => null,
                ],
            ],
        ];

        foreach ($plans as $planData) {
            $featuresData = $planData['features'];
            unset($planData['features']);

            $plan = Plan::firstOrCreate(
            ['name' => $planData['name']],
                $planData
            );

            // Attach features to plan
            $pivotData = [];
            foreach ($featuresData as $featureName => $limit) {
                $pivotData[$featureObjects[$featureName]->id] = ['limit' => $limit];
            }

            $plan->features()->sync($pivotData);
        }
    }
}
