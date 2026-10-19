<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $icone = '/assets/jornada-modulo.svg';
        $cursos = [
            [
                'titulo' => 'Visão geral da Jornada de Venda Digital',
                'url' => 'https://example.invalid/jornada-de-venda-digital/visao-geral',
                'icone' => $icone,
                'url_antiga' => 'https://cursos.alura.com.br/course/microsservicos-padroes-projeto',
            ],
            [

                'titulo' => 'Público e problema',
                'url' => 'https://example.invalid/jornada-de-venda-digital/publico-e-problema',
                'icone' => $icone,
                'url_antiga' => 'https://cursos.alura.com.br/course/fundamentos-microsservicos-aprofundando-conceitos',
            ],
            [

                'titulo' => 'Proposta de valor',
                'url' => 'https://example.invalid/jornada-de-venda-digital/proposta-de-valor',
                'icone' => $icone,
                'url_antiga' => 'https://cursos.alura.com.br/course/desenvolvimento-software-integracao-continua',
            ],
            [

                'titulo' => 'Construção da oferta digital',
                'url' => 'https://example.invalid/jornada-de-venda-digital/oferta-digital',
                'icone' => $icone,
                'url_antiga' => 'https://cursos.alura.com.br/course/entrega-continua-confiabilidade-qualidade',
            ],
            [

                'titulo' => 'Conteúdo para atrair clientes',
                'url' => 'https://example.invalid/jornada-de-venda-digital/conteudo',
                'icone' => $icone,
                'url_antiga' => 'https://cursos.alura.com.br/course/the-twelve-factor-app',
            ],
            [

                'titulo' => 'Captação de leads',
                'url' => 'https://example.invalid/jornada-de-venda-digital/leads',
                'icone' => $icone,
                'url_antiga' => 'https://cursos.alura.com.br/course/docker-e-docker-compose',
            ],
            [

                'titulo' => 'Qualificação e relacionamento',
                'url' => 'https://example.invalid/jornada-de-venda-digital/relacionamento',
                'icone' => $icone,
                'url_antiga' => 'https://cursos.alura.com.br/course/kubernetes-pods-services-configmap',
            ],
            [

                'titulo' => 'Checkout e conversão',
                'url' => 'https://example.invalid/jornada-de-venda-digital/checkout',
                'icone' => $icone,
                'url_antiga' => 'https://cursos.alura.com.br/course/nginx-servidor-web-proxy-reverso-api-gateway',
            ],
            [

                'titulo' => 'Métricas e próximos passos',
                'url' => 'https://example.invalid/jornada-de-venda-digital/metricas',
                'icone' => $icone,
                'url_antiga' => 'https://cursos.alura.com.br/course/nginx-parte-2-performance-fastcgi-https',
            ],
        ];

        foreach ($cursos as $curso) {
            $urlAntiga = $curso['url_antiga'];
            unset($curso['url_antiga']);

            $existente = DB::table('courses')->where('url', $curso['url'])->first();
            if ($existente !== null) {
                DB::table('courses')->where('id', $existente->id)->update($curso);
                continue;
            }

            $antigo = DB::table('courses')->where('url', $urlAntiga)->first();
            if ($antigo !== null) {
                DB::table('courses')->where('id', $antigo->id)->update($curso);
                continue;
            }

            DB::table('courses')->insertOrIgnore($curso);
        }
    }
}
