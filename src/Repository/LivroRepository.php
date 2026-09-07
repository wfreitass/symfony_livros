<?php

namespace App\Repository;

use App\Entity\Livro;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Livro>
 */
class LivroRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livro::class);
    }


    /**
     * Retorna todos os livros trazendo autores e assuntos em uma única consulta SQL (evita N+1).
     *
     * @return Livro[]
     */
    public function findAllWithAutoresAndAssuntos(): array
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.autores', 'a')
            ->addSelect('a')
            ->leftJoin('l.assuntos', 's')
            ->addSelect('s')
            ->orderBy('l.titulo', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
