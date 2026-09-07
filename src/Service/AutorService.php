<?php

namespace App\Service;

use App\Contract\AutorServiceInterface;
use App\Entity\Autor;
use App\Repository\AutorRepository;
use Doctrine\ORM\EntityManagerInterface;

class AutorService extends AbstractEntityService implements AutorServiceInterface
{
    public function __construct(
        EntityManagerInterface $entityManager,
        private readonly AutorRepository $autorRepository
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
        $this->safeRemoveAndFlush(
            $autor,
            sprintf('Não é possível excluir o autor "%s" porque ele está vinculado a um ou mais livros.', $autor->getNome())
        );
    }
}
