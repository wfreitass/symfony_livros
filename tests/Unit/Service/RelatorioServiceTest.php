<?php

namespace App\Tests\Unit\Service;

use App\Service\RelatorioService;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RelatorioServiceTest extends TestCase
{
    #[Test]
    public function testCalcularTotaisComDados(): void
    {
        $connection = $this->createStub(Connection::class);
        $service = new RelatorioService($connection);

        $dados = [
            1 => [
                'id' => 1,
                'nome' => 'Autor 1',
                'livros' => [
                    10 => ['valor' => '50.00'],
                    11 => ['valor' => '25.50'],
                ],
            ],
            2 => [
                'id' => 2,
                'nome' => 'Autor 2',
                'livros' => [
                    12 => ['valor' => '100.00'],
                ],
            ],
        ];

        $totais = $service->calcularTotais($dados);

        $this->assertSame(2, $totais['totalAutores']);
        $this->assertSame(3, $totais['totalObras']);
        $this->assertSame(175.50, $totais['valorTotal']);
    }

    #[Test]
    public function testCalcularTotaisComDadosVazios(): void
    {
        $connection = $this->createStub(Connection::class);
        $service = new RelatorioService($connection);

        $totais = $service->calcularTotais([]);

        $this->assertSame(0, $totais['totalAutores']);
        $this->assertSame(0, $totais['totalObras']);
        $this->assertSame(0.0, $totais['valorTotal']);
    }
}
