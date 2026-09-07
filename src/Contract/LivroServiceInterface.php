<?php

namespace App\Contract;

use App\Entity\Livro;

interface LivroServiceInterface
{
    /**
     * @return Livro[]
     */
    public function listAll(): array;

    public function save(Livro $livro): void;

    public function delete(Livro $livro): void;
}
