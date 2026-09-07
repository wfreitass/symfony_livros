<?php

namespace App\Tests\Unit\Service;

use App\Entity\Livro;
use App\Repository\LivroRepository;
use App\Service\LivroService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LivroServiceTest extends TestCase
{
    #[Test]
    public function testListAllDelegatesToRepository(): void
    {
        $em = $this->createStub(EntityManagerInterface::class);
        $repo = $this->createMock(LivroRepository::class);
        $service = new LivroService($em, $repo);

        $livros = [new Livro(), new Livro()];
        $repo->expects($this->once())
            ->method('findAllWithAutoresAndAssuntos')
            ->willReturn($livros);

        $result = $service->listAll();
        $this->assertSame($livros, $result);
    }

    #[Test]
    public function testSavePersistsAndFlushes(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createStub(LivroRepository::class);
        $service = new LivroService($em, $repo);

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
        $service = new LivroService($em, $repo);

        $livro = new Livro();
        $em->expects($this->once())
            ->method('remove')
            ->with($livro);
        $em->expects($this->once())
            ->method('flush');

        $service->delete($livro);
    }
}
