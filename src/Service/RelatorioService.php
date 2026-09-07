<?php

namespace App\Service;

use App\Contract\RelatorioRepositoryInterface;
use App\Contract\RelatorioServiceInterface;

class RelatorioService implements RelatorioServiceInterface
{
    public function __construct(
        private readonly RelatorioRepositoryInterface $relatorioRepository
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
     * Calcula métricas totais a partir dos dados agrupados.
     *
     * @param array<int, array{id: int, nome: string, livros: array<int, array{valor: string|float}>}> $dados
     * @return array{totalAutores: int, totalObras: int, valorTotal: float}
     */
    public function calcularTotais(array $dados): array
    {
        $totalAutores = count($dados);
        $totalObras = 0;
        $valorTotal = 0.0;

        foreach ($dados as $autor) {
            $totalObras += count($autor['livros']);
            foreach ($autor['livros'] as $livro) {
                $valorTotal += (float) $livro['valor'];
            }
        }

        return [
            'totalAutores' => $totalAutores,
            'totalObras' => $totalObras,
            'valorTotal' => $valorTotal,
        ];
    }
}
