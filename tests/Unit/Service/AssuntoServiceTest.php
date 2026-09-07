<?php

namespace App\Tests\Unit\Service;

use App\Entity\Assunto;
use App\Exception\EntityInUseException;
use App\Repository\AssuntoRepository;
use App\Service\AssuntoService;
use Doctrine\DBAL\Driver\Exception as DriverException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AssuntoServiceTest extends TestCase
{
    #[Test]
    public function testListAllDelegatesToRepository(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $repo = $this->createMock(AssuntoRepository::class);
        $service = new AssuntoService($em, $repo);

        $assuntos = [new Assunto(), new Assunto()];
        $repo->expects($this->once())
            ->method('findAllOrderedByDescricao')
            ->willReturn($assuntos);

        $result = $service->listAll();
        $this->assertSame($assuntos, $result);
    }

    #[Test]
    public function testSavePersistsAndFlushes(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createStub(AssuntoRepository::class);
        $service = new AssuntoService($em, $repo);

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
        $service = new AssuntoService($em, $repo);

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
        $service = new AssuntoService($em, $repo);

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
