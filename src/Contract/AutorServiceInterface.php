<?php

namespace App\Contract;

use App\Entity\Autor;
use Knp\Component\Pager\Pagination\PaginationInterface;

interface AutorServiceInterface
{
    /**
     * @return Autor[]
     */
    public function listAll(): array;

    /**
     * Retorna os autores paginados ordenados por nome.
     *
     * @return PaginationInterface<int, Autor>
     */
    public function listPaginated(int $page = 1, int $limit = 5): PaginationInterface;

    public function save(Autor $autor): void;

    public function delete(Autor $autor): void;
}
