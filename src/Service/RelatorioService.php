<?php

namespace App\Service;

use Doctrine\DBAL\Connection;

class RelatorioService
{
    public function __construct(
        private readonly Connection $connection
    ) {}

    /**
     * Retorna a estrutura agrupada por Autor para a tabela e o PDF.
     */
    public function getDadosRelatorioAgrupadosPorAutor(): array
    {
        $sql = 'SELECT * FROM vw_relatorio_livros ORDER BY autor_nome ASC, livro_titulo ASC, assunto_descricao ASC';
        $rows = $this->connection->executeQuery($sql)->fetchAllAssociative();

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
        // 1. Quantidade de Livros e Soma de Valores por Autor a partir da VIEW
        $sqlAutores = '
            SELECT 
                autor_nome,
                COUNT(DISTINCT livro_id) AS total_livros,
                SUM(livro_valor) AS valor_total
            FROM vw_relatorio_livros
            GROUP BY autor_id, autor_nome
            ORDER BY total_livros DESC, valor_total DESC
        ';
        $dadosAutores = $this->connection->executeQuery($sqlAutores)->fetchAllAssociative();

        // 2. Quantidade de Obras por Assunto a partir da VIEW
        $sqlAssuntos = '
            SELECT 
                assunto_descricao,
                COUNT(DISTINCT livro_id) AS total_livros
            FROM vw_relatorio_livros
            WHERE assunto_descricao IS NOT NULL
            GROUP BY assunto_id, assunto_descricao
            ORDER BY total_livros DESC
        ';
        $dadosAssuntos = $this->connection->executeQuery($sqlAssuntos)->fetchAllAssociative();

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
}
