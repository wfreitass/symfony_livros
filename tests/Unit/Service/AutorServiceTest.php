<?php

namespace App\Tests\Unit\Service;

use App\Entity\Autor;
use App\Exception\EntityInUseException;
use App\Repository\AutorRepository;
use App\Service\AutorService;
use Doctrine\DBAL\Driver\Exception as DriverException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AutorServiceTest extends TestCase
{
    #[Test]
    public function testListAllDelegatesToRepository(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $repo = $this->createMock(AutorRepository::class);
        $service = new AutorService($em, $repo);

        $autores = [new Autor(), new Autor()];
        $repo->expects($this->once())
            ->method('findAllOrderedByName')
            ->willReturn($autores);

        $result = $service->listAll();
        $this->assertSame($autores, $result);
    }

    #[Test]
    public function testSavePersistsAndFlushes(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createStub(AutorRepository::class);
        $service = new AutorService($em, $repo);

        $autor = (new Autor())->setNome('Machado de Assis');
        $em->expects($this->once())
            ->method('persist')
            ->with($autor);
        $em->expects($this->once())
            ->method('flush');

        $service->save($autor);
    }

    #[Test]
    public function testDeleteRemovesAndFlushes(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createStub(AutorRepository::class);
        $service = new AutorService($em, $repo);

        $autor = (new Autor())->setNome('Machado de Assis');
        $em->expects($this->once())
            ->method('remove')
            ->with($autor);
        $em->expects($this->once())
            ->method('flush');

        $service->delete($autor);
    }

    #[Test]
    public function testDeleteThrowsEntityInUseExceptionOnConstraintViolation(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createStub(AutorRepository::class);
        $service = new AutorService($em, $repo);

        $autor = (new Autor())->setNome('Machado de Assis');
        $driverException = $this->createStub(DriverException::class);
        $fkException = new ForeignKeyConstraintViolationException($driverException, null);

        $em->expects($this->once())
            ->method('remove')
            ->with($autor);
        $em->expects($this->once())
            ->method('flush')
            ->willThrowException($fkException);

        $this->expectException(EntityInUseException::class);
        $this->expectExceptionMessage('Não é possível excluir o autor "Machado de Assis" porque ele está vinculado a um ou mais livros.');

        $service->delete($autor);
    }
}
