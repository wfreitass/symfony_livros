<?php

namespace App\Service;

use Doctrine\DBAL\Connection;

class RelatorioService
{
    public function __construct(
        private readonly Connection $connection
    ) {}

    /**
     * Consulta a VIEW SQL "vw_relatorio_livros" e agrupa os livros e assuntos por autor.
     *
     * @return array Estrutura hierárquica: Autor -> Livros -> Assuntos
     */
    public function getDadosRelatorioAgrupadosPorAutor(): array
    {
        // Consulta obrigatória proveniente da VIEW criada no banco de dados
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
}
