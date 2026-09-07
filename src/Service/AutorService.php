<?php

namespace App\Service;

use App\Contract\AutorServiceInterface;
use App\Entity\Autor;
use App\Exception\EntityInUseException;
use App\Repository\AutorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class AutorService extends AbstractEntityService implements AutorServiceInterface
{
    public function __construct(
        EntityManagerInterface $entityManager,
        private readonly AutorRepository $autorRepository,
        private readonly PaginatorInterface $paginator
    ) {
        parent::__construct($entityManager);
    }

    /**
     * Retorna todos os autores ordenados por nome.
     *
     * @return Autor[]
     */
    public function listAll(): array
    {
        return $this->autorRepository->findAllOrderedByName();
    }

    /**
     * Retorna os autores paginados ordenados por nome.
     *
     * @return PaginationInterface<int, Autor>
     */
    public function listPaginated(int $page = 1, int $limit = 5): PaginationInterface
    {
        $qb = $this->autorRepository->createAllOrderedByNameQueryBuilder();

        return $this->paginator->paginate($qb, $page, $limit);
    }

    /**
     * Salva ou atualiza um autor.
     */
    public function save(Autor $autor): void
    {
        $this->persistAndFlush($autor);
    }

    /**
     * Exclui um autor, lançando exceção específica caso esteja vinculado a livros.
     */
    public function delete(Autor $autor): void
    {
        if ($autor->getLivros()->count() > 0) {
            throw new EntityInUseException(
                sprintf('Não é possível excluir o autor "%s" porque ele está vinculado a um ou mais livros.', $autor->getNome())
            );
        }

        $this->safeRemoveAndFlush(
            $autor,
            sprintf('Não é possível excluir o autor "%s" porque ele está vinculado a um ou mais livros.', $autor->getNome())
        );
    }
}
