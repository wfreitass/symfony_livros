<?php

namespace App\Contract;

use App\Entity\Livro;

/**
 * Contrato de serviço para gerenciamento da entidade Livro.
 *
 * @extends BaseServiceInterface<Livro>
 */
interface LivroServiceInterface extends BaseServiceInterface
{
    /**
     * Retorna os últimos 5 livros cadastrados.
     *
     * @return Livro[]
     */
    public function listFiveLast(): array;
}
