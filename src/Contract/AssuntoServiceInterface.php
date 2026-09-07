<?php

namespace App\Contract;

use App\Entity\Assunto;
use Knp\Component\Pager\Pagination\PaginationInterface;

interface AssuntoServiceInterface
{
    /**
     * @return Assunto[]
     */
    public function listAll(): array;

    /**
     * Retorna os assuntos paginados ordenados por descrição.
     *
     * @return PaginationInterface<int, Assunto>
     */
    public function listPaginated(int $page = 1, int $limit = 5): PaginationInterface;

    public function save(Assunto $assunto): void;

    public function delete(Assunto $assunto): void;
}
