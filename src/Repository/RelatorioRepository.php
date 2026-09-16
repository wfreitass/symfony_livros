<?php

namespace App\Repository;

use App\Contract\RelatorioRepositoryInterface;
use Doctrine\DBAL\Connection;

class RelatorioRepository implements RelatorioRepositoryInterface
{
    public function __construct(
        private readonly Connection $connection
    ) {}

    /**
     * Retorna todas as linhas da VIEW vw_relatorio_livros ordenadas por autor, livro e assunto.
     */
    public function findDadosViewRelatorio(): array
    {
        $sql = '
            SELECT 
                autor_id,
                autor_nome,
                livro_id,
                livro_titulo,
                livro_editora,
                livro_edicao,
                livro_ano_publicacao,
                livro_valor,
                assunto_id,
                assunto_descricao
            FROM vw_relatorio_livros 
            ORDER BY autor_nome ASC, livro_titulo ASC, assunto_descricao ASC
        ';

        return $this->connection->executeQuery($sql)->fetchAllAssociative();
    }

    /**
     * Retorna quantidade de livros e soma de valores por autor a partir da VIEW vw_relatorio_livros.
     * Utiliza subconsulta na VIEW para desduplicar livros com múltiplos assuntos antes de somar os valores.
     */
    public function findMetricasObrasPorAutor(): array
    {
        $sql = '
            SELECT 
                autor_nome,
                COUNT(DISTINCT livro_id) AS total_livros,
                SUM(livro_valor) AS valor_total
            FROM (
                SELECT DISTINCT autor_id, autor_nome, livro_id, livro_valor 
                FROM vw_relatorio_livros
            ) v
            GROUP BY autor_id, autor_nome
            ORDER BY total_livros DESC, valor_total DESC
        ';

        return $this->connection->executeQuery($sql)->fetchAllAssociative();
    }

    /**
     * Retorna quantidade de obras por assunto a partir da VIEW vw_relatorio_livros.
     */
    public function findMetricasObrasPorAssunto(): array
    {
        $sql = '
            SELECT 
                assunto_descricao,
                COUNT(DISTINCT livro_id) AS total_livros
            FROM vw_relatorio_livros
            WHERE assunto_descricao IS NOT NULL
            GROUP BY assunto_id, assunto_descricao
            ORDER BY total_livros DESC
        ';

        return $this->connection->executeQuery($sql)->fetchAllAssociative();
    }
}
