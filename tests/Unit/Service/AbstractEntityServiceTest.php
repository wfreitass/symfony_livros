<?php

namespace App\Tests\Unit\Service;

use App\Exception\EntityInUseException;
use App\Service\AbstractEntityService;
use Doctrine\DBAL\Driver\Exception as DriverException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AbstractEntityServiceTest extends TestCase
{
    #[Test]
    public function testPersistAndFlush(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $entity = new \stdClass();

        $em->expects($this->once())->method('persist')->with($entity);
        $em->expects($this->once())->method('flush');

        $service = new class($em) extends AbstractEntityService {
            public function testPersist(object $entity): void
            {
                $this->persistAndFlush($entity);
            }
        };

        $service->testPersist($entity);
    }

    #[Test]
    public function testRemoveAndFlush(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $entity = new \stdClass();

        $em->expects($this->once())->method('remove')->with($entity);
        $em->expects($this->once())->method('flush');

        $service = new class($em) extends AbstractEntityService {
            public function testRemove(object $entity): void
            {
                $this->removeAndFlush($entity);
            }
        };

        $service->testRemove($entity);
    }

    #[Test]
    public function testSafeRemoveAndFlushThrowsDomainException(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $entity = new \stdClass();
        $driverException = $this->createStub(DriverException::class);
        $fkException = new ForeignKeyConstraintViolationException($driverException, null);

        $em->expects($this->once())->method('remove')->with($entity);
        $em->expects($this->once())->method('flush')->willThrowException($fkException);

        $service = new class($em) extends AbstractEntityService {
            public function testSafeRemove(object $entity, string $msg): void
            {
                $this->safeRemoveAndFlush($entity, $msg);
            }
        };

        $this->expectException(EntityInUseException::class);
        $this->expectExceptionMessage('Erro customizado de vínculo');

        $service->testSafeRemove($entity, 'Erro customizado de vínculo');
    }
}
