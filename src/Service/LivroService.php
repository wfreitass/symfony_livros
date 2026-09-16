<?php

namespace App\Service;

use App\Contract\LivroServiceInterface;
use App\Entity\Livro;
use App\Repository\LivroRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class LivroService extends AbstractEntityService implements LivroServiceInterface
{
    public function __construct(
        EntityManagerInterface $entityManager,
        private readonly LivroRepository $livroRepository,
        private readonly PaginatorInterface $paginator
    ) {
        parent::__construct($entityManager);
    }

    /**
     * Retorna todos os livros com autores e assuntos carregados.
     *
     * @return Livro[]
     */
    public function listAll(): array
    {
        return $this->livroRepository->findAllWithAutoresAndAssuntos();
    }

    /**
     * Retorna os livros paginados com autores e assuntos carregados.
     *
     * @return PaginationInterface<int, Livro>
     */
    public function listPaginated(int $page = 1, int $limit = 5): PaginationInterface
    {
        $qb = $this->livroRepository->createAllWithAutoresAndAssuntosQueryBuilder();

        return $this->paginator->paginate($qb, $page, $limit);
    }

    /**
     * Salva ou atualiza um livro.
     *
     * @param Livro $entity
     */
    public function save(object $entity): void
    {
        $this->persistAndFlush($entity);
    }

    /**
     * Exclui um livro.
     *
     * @param Livro $entity
     */
    public function delete(object $entity): void
    {
        $this->removeAndFlush($entity);
    }


    public function listFiveLast(): array
    {
        return $this->livroRepository->findFiveLast();
    }
}
