<?php

namespace App\Tests\Unit\Service;

use App\Contract\RelatorioRepositoryInterface;
use App\Service\RelatorioService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RelatorioServiceTest extends TestCase
{
    #[Test]
    public function testGetDadosRelatorioAgrupadosPorAutor(): void
    {
        $repository = $this->createMock(RelatorioRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findDadosViewRelatorio')
            ->willReturn([
                [
                    'autor_id' => 1,
                    'autor_nome' => 'Machado de Assis',
                    'livro_id' => 10,
                    'livro_titulo' => 'Dom Casmurro',
                    'livro_editora' => 'Garnier',
                    'livro_edicao' => 1,
                    'livro_ano_publicacao' => '1899',
                    'livro_valor' => '49.90',
                    'assunto_id' => 1,
                    'assunto_descricao' => 'Romance',
                ],
                [
                    'autor_id' => 1,
                    'autor_nome' => 'Machado de Assis',
                    'livro_id' => 10,
                    'livro_titulo' => 'Dom Casmurro',
                    'livro_editora' => 'Garnier',
                    'livro_edicao' => 1,
                    'livro_ano_publicacao' => '1899',
                    'livro_valor' => '49.90',
                    'assunto_id' => 2,
                    'assunto_descricao' => 'Realismo',
                ],
            ]);

        $service = new RelatorioService($repository);
        $dados = $service->getDadosRelatorioAgrupadosPorAutor();

        $this->assertCount(1, $dados);
        $this->assertArrayHasKey(1, $dados);
        $this->assertSame('Machado de Assis', $dados[1]['nome']);
        $this->assertCount(1, $dados[1]['livros']);
        $this->assertSame('Dom Casmurro', $dados[1]['livros'][10]['titulo']);
        $this->assertCount(2, $dados[1]['livros'][10]['assuntos']);
        $this->assertContains('Romance', $dados[1]['livros'][10]['assuntos']);
        $this->assertContains('Realismo', $dados[1]['livros'][10]['assuntos']);
    }

    #[Test]
    public function testGetMetricasGraficos(): void
    {
        $repository = $this->createMock(RelatorioRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findMetricasObrasPorAutor')
            ->willReturn([
                ['autor_nome' => 'Clarice Lispector', 'total_livros' => 3, 'valor_total' => 150.00],
            ]);
        $repository->expects($this->once())
            ->method('findMetricasObrasPorAssunto')
            ->willReturn([
                ['assunto_descricao' => 'Filosofia', 'total_livros' => 2],
            ]);

        $service = new RelatorioService($repository);
        $metricas = $service->getMetricasGraficos();

        $this->assertSame(['Clarice Lispector'], $metricas['autores']['labels']);
        $this->assertSame([3], $metricas['autores']['quantidades']);
        $this->assertSame([150.0], $metricas['autores']['valores']);
        $this->assertSame(['Filosofia'], $metricas['assuntos']['labels']);
        $this->assertSame([2], $metricas['assuntos']['quantidades']);
    }

    #[Test]
    public function testCalcularTotaisComDados(): void
    {
        $repository = $this->createStub(RelatorioRepositoryInterface::class);
        $service = new RelatorioService($repository);

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
        $repository = $this->createStub(RelatorioRepositoryInterface::class);
        $service = new RelatorioService($repository);

        $totais = $service->calcularTotais([]);

        $this->assertSame(0, $totais['totalAutores']);
        $this->assertSame(0, $totais['totalObras']);
        $this->assertSame(0.0, $totais['valorTotal']);
    }
}
