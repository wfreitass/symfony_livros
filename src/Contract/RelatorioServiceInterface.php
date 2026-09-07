<?php

namespace App\Contract;

interface RelatorioServiceInterface
{
    public function getDadosRelatorioAgrupadosPorAutor(): array;

    public function getMetricasGraficos(): array;

    public function calcularTotais(array $dados): array;
}
