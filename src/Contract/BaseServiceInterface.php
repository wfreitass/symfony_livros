<?php

namespace App\Contract;

use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface base para os serviços de gerenciamento de entidades.
 *
 * @template T of object
 */
interface BaseServiceInterface
{
    /**
     * Retorna todos os registros da entidade.
     *
     * @return array<T>
     */
    public function listAll(): array;

    /**
     * Retorna os registros paginados da entidade.
     *
     * @return PaginationInterface<int, T>
     */
    public function listPaginated(int $page = 1, int $limit = 5): PaginationInterface;

    /**
     * Salva ou atualiza a entidade no repositório.
     *
     * @param T $entity
     */
    public function save(object $entity): void;

    /**
     * Remove a entidade do repositório.
     *
     * @param T $entity
     */
    public function delete(object $entity): void;
}
