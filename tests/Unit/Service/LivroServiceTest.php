<?php

namespace App\Tests\Unit\Service;

use App\Entity\Livro;
use App\Repository\LivroRepository;
use App\Service\LivroService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LivroServiceTest extends TestCase
{
    #[Test]
    public function testListAllDelegatesToRepository(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $repo = $this->createMock(LivroRepository::class);
        $paginator = $this->createStub(PaginatorInterface::class);
        $service = new LivroService($em, $repo, $paginator);

        $livros = [new Livro(), new Livro()];
        $repo->expects($this->once())
            ->method('findAllWithAutoresAndAssuntos')
            ->willReturn($livros);

        $result = $service->listAll();
        $this->assertSame($livros, $result);
    }

    #[Test]
    public function testListPaginatedDelegatesToPaginator(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $repo = $this->createMock(LivroRepository::class);
        $paginator = $this->createMock(PaginatorInterface::class);
        $service = new LivroService($em, $repo, $paginator);

        $qb = $this->createStub(QueryBuilder::class);
        $pagination = $this->createStub(PaginationInterface::class);

        $repo->expects($this->once())
            ->method('createAllWithAutoresAndAssuntosQueryBuilder')
            ->willReturn($qb);

        $paginator->expects($this->once())
            ->method('paginate')
            ->with($qb, 1, 5)
            ->willReturn($pagination);

        $result = $service->listPaginated(1, 5);
        $this->assertSame($pagination, $result);
    }

    #[Test]
    public function testSavePersistsAndFlushes(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createStub(LivroRepository::class);
        $paginator = $this->createStub(PaginatorInterface::class);
        $service = new LivroService($em, $repo, $paginator);

        $livro = new Livro();
        $em->expects($this->once())
            ->method('persist')
            ->with($livro);
        $em->expects($this->once())
            ->method('flush');

        $service->save($livro);
    }

    #[Test]
    public function testDeleteRemovesAndFlushes(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createStub(LivroRepository::class);
        $paginator = $this->createStub(PaginatorInterface::class);
        $service = new LivroService($em, $repo, $paginator);

        $livro = new Livro();
        $em->expects($this->once())
            ->method('remove')
            ->with($livro);
        $em->expects($this->once())
            ->method('flush');

        $service->delete($livro);
    }
}
