<?php

namespace App\Tests\Unit\Service;

use App\Entity\Assunto;
use App\Exception\EntityInUseException;
use App\Repository\AssuntoRepository;
use App\Service\AssuntoService;
use Doctrine\DBAL\Driver\Exception as DriverException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AssuntoServiceTest extends TestCase
{
    #[Test]
    public function testListAllDelegatesToRepository(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $repo = $this->createMock(AssuntoRepository::class);
        $paginator = $this->createStub(PaginatorInterface::class);
        $service = new AssuntoService($em, $repo, $paginator);

        $assuntos = [new Assunto(), new Assunto()];
        $repo->expects($this->once())
            ->method('findAllOrderedByDescricao')
            ->willReturn($assuntos);

        $result = $service->listAll();
        $this->assertSame($assuntos, $result);
    }

    #[Test]
    public function testListPaginatedDelegatesToPaginator(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $repo = $this->createMock(AssuntoRepository::class);
        $paginator = $this->createMock(PaginatorInterface::class);
        $service = new AssuntoService($em, $repo, $paginator);

        $qb = $this->createStub(QueryBuilder::class);
        $pagination = $this->createStub(PaginationInterface::class);

        $repo->expects($this->once())
            ->method('createAllOrderedByDescricaoQueryBuilder')
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
        $repo = $this->createStub(AssuntoRepository::class);
        $paginator = $this->createStub(PaginatorInterface::class);
        $service = new AssuntoService($em, $repo, $paginator);

        $assunto = (new Assunto())->setDescricao('Ficção Científica');
        $em->expects($this->once())
            ->method('persist')
            ->with($assunto);
        $em->expects($this->once())
            ->method('flush');

        $service->save($assunto);
    }

    #[Test]
    public function testDeleteRemovesAndFlushes(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createStub(AssuntoRepository::class);
        $paginator = $this->createStub(PaginatorInterface::class);
        $service = new AssuntoService($em, $repo, $paginator);

        $assunto = (new Assunto())->setDescricao('Ficção Científica');
        $em->expects($this->once())
            ->method('remove')
            ->with($assunto);
        $em->expects($this->once())
            ->method('flush');

        $service->delete($assunto);
    }

    #[Test]
    public function testDeleteThrowsEntityInUseExceptionOnConstraintViolation(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createStub(AssuntoRepository::class);
        $paginator = $this->createStub(PaginatorInterface::class);
        $service = new AssuntoService($em, $repo, $paginator);

        $assunto = (new Assunto())->setDescricao('Ficção Científica');
        $driverException = $this->createStub(DriverException::class);
        $fkException = new ForeignKeyConstraintViolationException($driverException, null);

        $em->expects($this->once())
            ->method('remove')
            ->with($assunto);
        $em->expects($this->once())
            ->method('flush')
            ->willThrowException($fkException);

        $this->expectException(EntityInUseException::class);
        $this->expectExceptionMessage('Não é possível excluir o assunto "Ficção Científica" porque ele está vinculado a um ou mais livros.');

        $service->delete($assunto);
    }
}
