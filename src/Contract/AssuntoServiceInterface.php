<?php

namespace App\Contract;

use App\Entity\Assunto;

interface AssuntoServiceInterface
{
    /**
     * @return Assunto[]
     */
    public function listAll(): array;

    public function save(Assunto $assunto): void;

    public function delete(Assunto $assunto): void;
}
