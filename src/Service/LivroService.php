<?php

namespace App\Service;

use App\Contract\LivroServiceInterface;
use App\Entity\Livro;
use App\Repository\LivroRepository;
use Doctrine\ORM\EntityManagerInterface;

class LivroService extends AbstractEntityService implements LivroServiceInterface
{
    public function __construct(
        EntityManagerInterface $entityManager,
        private readonly LivroRepository $livroRepository
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
     * Salva ou atualiza um livro.
     */
    public function save(Livro $livro): void
    {
        $this->persistAndFlush($livro);
    }

    /**
     * Exclui um livro.
     */
    public function delete(Livro $livro): void
    {
        $this->removeAndFlush($livro);
    }
}
