<?php

namespace App\Contract;

interface RelatorioRepositoryInterface
{
    /**
     * Retorna todas as linhas da VIEW vw_relatorio_livros ordenadas.
     *
     * @return array<int, array<string, mixed>>
     */
    public function findDadosViewRelatorio(): array;

    /**
     * Retorna métricas de total de obras e valor agrupadas por autor a partir da VIEW.
     *
     * @return array<int, array{autor_nome: string, total_livros: int|numeric-string, valor_total: float|numeric-string}>
     */
    public function findMetricasObrasPorAutor(): array;

    /**
     * Retorna métricas de total de obras agrupadas por assunto a partir da VIEW.
     *
     * @return array<int, array{assunto_descricao: string, total_livros: int|numeric-string}>
     */
    public function findMetricasObrasPorAssunto(): array;
}
