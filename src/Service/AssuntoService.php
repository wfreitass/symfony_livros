<?php

namespace App\Service;

use App\Contract\AssuntoServiceInterface;
use App\Entity\Assunto;
use App\Exception\EntityInUseException;
use App\Repository\AssuntoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class AssuntoService extends AbstractEntityService implements AssuntoServiceInterface
{
    public function __construct(
        EntityManagerInterface $entityManager,
        private readonly AssuntoRepository $assuntoRepository,
        private readonly PaginatorInterface $paginator
    ) {
        parent::__construct($entityManager);
    }

    /**
     * Retorna todos os assuntos ordenados por descrição.
     *
     * @return Assunto[]
     */
    public function listAll(): array
    {
        return $this->assuntoRepository->findAllOrderedByDescricao();
    }

    /**
     * Retorna os assuntos paginados ordenados por descrição.
     *
     * @return PaginationInterface<int, Assunto>
     */
    public function listPaginated(int $page = 1, int $limit = 5): PaginationInterface
    {
        $qb = $this->assuntoRepository->createAllOrderedByDescricaoQueryBuilder();

        return $this->paginator->paginate($qb, $page, $limit);
    }

    /**
     * Salva ou atualiza um assunto.
     */
    public function save(Assunto $assunto): void
    {
        $this->persistAndFlush($assunto);
    }

    /**
     * Exclui um assunto, lançando exceção específica caso esteja vinculado a livros.
     */
    public function delete(Assunto $assunto): void
    {
        if ($assunto->getLivros()->count() > 0) {
            throw new EntityInUseException(
                sprintf('Não é possível excluir o assunto "%s" porque ele está vinculado a um ou mais livros.', $assunto->getDescricao())
            );
        }

        $this->safeRemoveAndFlush(
            $assunto,
            sprintf('Não é possível excluir o assunto "%s" porque ele está vinculado a um ou mais livros.', $assunto->getDescricao())
        );
    }
}
