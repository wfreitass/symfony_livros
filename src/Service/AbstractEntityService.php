<?php

namespace App\Service;

use App\Exception\EntityInUseException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractEntityService
{
    public function __construct(
        protected readonly EntityManagerInterface $entityManager
    ) {}

    /**
     * Persiste e sincroniza a entidade no banco de dados.
     */
    protected function persistAndFlush(object $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    /**
     * Remove e sincroniza a entidade no banco de dados.
     */
    protected function removeAndFlush(object $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }

    /**
     * Remove a entidade capturando violações de chave estrangeira e lançando exceção de domínio.
     *
     * @throws EntityInUseException
     */
    protected function safeRemoveAndFlush(object $entity, string $errorMessage): void
    {
        try {
            $this->removeAndFlush($entity);
        } catch (ForeignKeyConstraintViolationException $e) {
            throw new EntityInUseException($errorMessage, 0, $e);
        }
    }
}
