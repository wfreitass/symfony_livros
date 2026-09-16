<?php

namespace App\Contract;

use Symfony\UX\Chartjs\Model\Chart;

interface RelatorioServiceInterface
{
    public function getDadosRelatorioAgrupadosPorAutor(): array;

    public function getMetricasGraficos(): array;

    public function calcularTotais(array $dados): array;

    /**
     * Constrói todos os gráficos analíticos do relatório gerencial.
     *
     * @return array{chartLivros: Chart, chartValores: Chart}
     */
    public function buildCharts(): array;

    /**
     * Constrói o gráfico de barras com a quantidade de obras por autor.
     *
     * @param array{labels: string[], quantidades: int[], valores: float[]} $metricasAutores
     */
    public function buildChartLivrosPorAutor(array $metricasAutores): Chart;

    /**
     * Constrói o gráfico de rosca (doughnut) com a distribuição de valor por autor.
     *
     * @param array{labels: string[], quantidades: int[], valores: float[]} $metricasAutores
     */
    public function buildChartValoresPorAutor(array $metricasAutores): Chart;
}
