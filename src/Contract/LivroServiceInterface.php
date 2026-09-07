<?php

namespace App\Contract;

use App\Entity\Livro;
use Knp\Component\Pager\Pagination\PaginationInterface;

interface LivroServiceInterface
{
    /**
     * @return Livro[]
     */
    public function listAll(): array;

    /**
     * Retorna os livros paginados trazendo autores e assuntos.
     *
     * @return PaginationInterface<int, Livro>
     */
    public function listPaginated(int $page = 1, int $limit = 5): PaginationInterface;

    public function save(Livro $livro): void;

    public function delete(Livro $livro): void;
}
