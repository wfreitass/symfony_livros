<?php

namespace App\Contract;

use App\Entity\Autor;

interface AutorServiceInterface
{
    /**
     * @return Autor[]
     */
    public function listAll(): array;

    public function save(Autor $autor): void;

    public function delete(Autor $autor): void;
}
