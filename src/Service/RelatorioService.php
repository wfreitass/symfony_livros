<?php

namespace App\Service;

use App\Contract\RelatorioRepositoryInterface;
use App\Contract\RelatorioServiceInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class RelatorioService implements RelatorioServiceInterface
{
    public function __construct(
        private readonly RelatorioRepositoryInterface $relatorioRepository,
        private readonly ChartBuilderInterface $chartBuilder
    ) {}

    /**
     * Retorna a estrutura agrupada por Autor para a tabela e o PDF.
     */
    public function getDadosRelatorioAgrupadosPorAutor(): array
    {
        $rows = $this->relatorioRepository->findDadosViewRelatorio();

        $autoresAgrupados = [];

        foreach ($rows as $row) {
            $autorId = $row['autor_id'];
            $livroId = $row['livro_id'];

            if (!isset($autoresAgrupados[$autorId])) {
                $autoresAgrupados[$autorId] = [
                    'id' => $autorId,
                    'nome' => $row['autor_nome'],
                    'livros' => [],
                ];
            }

            if (!isset($autoresAgrupados[$autorId]['livros'][$livroId])) {
                $autoresAgrupados[$autorId]['livros'][$livroId] = [
                    'id' => $livroId,
                    'titulo' => $row['livro_titulo'],
                    'editora' => $row['livro_editora'],
                    'edicao' => $row['livro_edicao'],
                    'anoPublicacao' => $row['livro_ano_publicacao'],
                    'valor' => $row['livro_valor'],
                    'assuntos' => [],
                ];
            }

            if (!empty($row['assunto_descricao']) && !in_array($row['assunto_descricao'], $autoresAgrupados[$autorId]['livros'][$livroId]['assuntos'], true)) {
                $autoresAgrupados[$autorId]['livros'][$livroId]['assuntos'][] = $row['assunto_descricao'];
            }
        }

        return $autoresAgrupados;
    }

    /**
     * Retorna métricas consolidadas diretamente da VIEW para alimentar o Chart.js.
     */
    public function getMetricasGraficos(): array
    {
        $dadosAutores = $this->relatorioRepository->findMetricasObrasPorAutor();
        $dadosAssuntos = $this->relatorioRepository->findMetricasObrasPorAssunto();

        return [
            'autores' => [
                'labels' => array_column($dadosAutores, 'autor_nome'),
                'quantidades' => array_map('intval', array_column($dadosAutores, 'total_livros')),
                'valores' => array_map('floatval', array_column($dadosAutores, 'valor_total')),
            ],
            'assuntos' => [
                'labels' => array_column($dadosAssuntos, 'assunto_descricao'),
                'quantidades' => array_map('intval', array_column($dadosAssuntos, 'total_livros')),
            ],
        ];
    }

    /**
     * Calcula métricas totais a partir dos dados agrupados,
     * desduplicando livros com múltiplos autores para refletir a contagem real
     * e o valor financeiro exato do acervo.
     *
     * @param array<int, array{id: int, nome: string, livros: array<int, array{id?: int, valor: string|float}>}> $dados
     * @return array{totalAutores: int, totalObras: int, valorTotal: float}
     */
    public function calcularTotais(array $dados): array
    {
        $totalAutores = count($dados);
        $livrosUnicos = [];

        foreach ($dados as $autor) {
            foreach ($autor['livros'] as $key => $livro) {
                $livroId = $livro['id'] ?? $key;
                if (!isset($livrosUnicos[$livroId])) {
                    $livrosUnicos[$livroId] = (float) $livro['valor'];
                }
            }
        }

        return [
            'totalAutores' => $totalAutores,
            'totalObras' => count($livrosUnicos),
            'valorTotal' => (float) array_sum($livrosUnicos),
        ];
    }

    /**
     * Constrói todos os gráficos analíticos do relatório gerencial.
     *
     * @return array{chartLivros: Chart, chartValores: Chart}
     */
    public function buildCharts(): array
    {
        $metricas = $this->getMetricasGraficos();

        return [
            'chartLivros' => $this->buildChartLivrosPorAutor($metricas['autores']),
            'chartValores' => $this->buildChartValoresPorAutor($metricas['autores']),
        ];
    }

    /**
     * Constrói o gráfico de barras com a quantidade de livros por autor.
     *
     * @param array{labels: string[], quantidades: int[], valores: float[]} $metricasAutores
     */
    public function buildChartLivrosPorAutor(array $metricasAutores): Chart
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData([
            'labels' => $metricasAutores['labels'],
            'datasets' => [
                [
                    'label' => 'Qtd. de Obras',
                    'backgroundColor' => 'rgba(13, 110, 253, 0.75)',
                    'borderColor' => 'rgb(13, 110, 253)',
                    'borderWidth' => 1,
                    'borderRadius' => 4,
                    'data' => $metricasAutores['quantidades'],
                ],
            ],
        ]);
        $chart->setOptions([
            'responsive' => true,
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['stepSize' => 1],
                ],
            ],
        ]);

        return $chart;
    }

    /**
     * Constrói o gráfico de rosca (doughnut) com os valores financeiros do acervo por autor.
     *
     * @param array{labels: string[], quantidades: int[], valores: float[]} $metricasAutores
     */
    public function buildChartValoresPorAutor(array $metricasAutores): Chart
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $chart->setData([
            'labels' => $metricasAutores['labels'],
            'datasets' => [
                [
                    'label' => 'Valor em Acervo (R$)',
                    'backgroundColor' => [
                        '#198754',
                        '#0dcaf0',
                        '#ffc107',
                        '#fd7e14',
                        '#6f42c1',
                        '#20c997',
                        '#d63384',
                    ],
                    'borderWidth' => 1,
                    'data' => $metricasAutores['valores'],
                ],
            ],
        ]);
        $chart->setOptions([
            'responsive' => true,
        ]);

        return $chart;
    }
}
