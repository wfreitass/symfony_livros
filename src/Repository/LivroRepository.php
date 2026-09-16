<?php

namespace App\Repository;

use App\Entity\Livro;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
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
     * Retorna a QueryBuilder para paginação de livros trazendo autores e assuntos (evita N+1).
     */
    public function createAllWithAutoresAndAssuntosQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('l')
            ->leftJoin('l.autores', 'a')
            ->addSelect('a')
            ->leftJoin('l.assuntos', 's')
            ->addSelect('s')
            ->orderBy('l.titulo', 'ASC');
    }

    /**
     * Retorna todos os livros trazendo autores e assuntos em uma única consulta SQL (evita N+1).
     *
     * @return Livro[]
     */
    public function findAllWithAutoresAndAssuntos(): array
    {
        return $this->createAllWithAutoresAndAssuntosQueryBuilder()
            ->getQuery()
            ->getResult();
    }

    /**
     * Retorna os últimos 5 livros cadastrados com autores e assuntos (usando Paginator para evitar corte de joins N+1).
     *
     * @return Livro[]
     */
    public function findFiveLast(): array
    {
        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.autores', 'a')
            ->addSelect('a')
            ->leftJoin('l.assuntos', 's')
            ->addSelect('s')
            ->orderBy('l.id', 'DESC')
            ->setMaxResults(5);
        // return $qb->getQuery()->getResult();
        return iterator_to_array(new Paginator($qb));
    }
}
