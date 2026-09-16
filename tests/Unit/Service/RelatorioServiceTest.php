<?php

namespace App\Tests\Unit\Service;

use App\Contract\RelatorioRepositoryInterface;
use App\Service\RelatorioService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class RelatorioServiceTest extends TestCase
{
    private function createService(
        ?RelatorioRepositoryInterface $repository = null,
        ?ChartBuilderInterface $chartBuilder = null
    ): RelatorioService {
        return new RelatorioService(
            $repository ?? $this->createStub(RelatorioRepositoryInterface::class),
            $chartBuilder ?? $this->createStub(ChartBuilderInterface::class)
        );
    }

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

        $service = $this->createService($repository);
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

        $service = $this->createService($repository);
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
        $service = $this->createService();

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
    public function testCalcularTotaisDeduplicaLivrosComMultiplosAutores(): void
    {
        $service = $this->createService();

        // Livro 6 com múltiplos autores (Neil Gaiman e Terry Pratchett)
        $dados = [
            5 => [
                'id' => 5,
                'nome' => 'Neil Gaiman',
                'livros' => [
                    6 => ['id' => 6, 'valor' => '64.90'],
                ],
            ],
            6 => [
                'id' => 6,
                'nome' => 'Terry Pratchett',
                'livros' => [
                    6 => ['id' => 6, 'valor' => '64.90'],
                ],
            ],
        ];

        $totais = $service->calcularTotais($dados);

        $this->assertSame(2, $totais['totalAutores']);
        $this->assertSame(1, $totais['totalObras']);
        $this->assertSame(64.90, $totais['valorTotal']);
    }

    #[Test]
    public function testCalcularTotaisComDadosVazios(): void
    {
        $service = $this->createService();

        $totais = $service->calcularTotais([]);

        $this->assertSame(0, $totais['totalAutores']);
        $this->assertSame(0, $totais['totalObras']);
        $this->assertSame(0.0, $totais['valorTotal']);
    }

    #[Test]
    public function testBuildChartLivrosPorAutor(): void
    {
        $chartBuilder = $this->createMock(ChartBuilderInterface::class);
        $chartMock = new Chart(Chart::TYPE_BAR);
        $chartBuilder->expects($this->once())
            ->method('createChart')
            ->with(Chart::TYPE_BAR)
            ->willReturn($chartMock);

        $service = $this->createService(null, $chartBuilder);
        $chart = $service->buildChartLivrosPorAutor([
            'labels' => ['Machado de Assis'],
            'quantidades' => [5],
            'valores' => [200.0],
        ]);

        $this->assertSame($chartMock, $chart);
        $this->assertSame(Chart::TYPE_BAR, $chart->getType());
        $data = $chart->getData();
        $this->assertSame(['Machado de Assis'], $data['labels']);
        $this->assertSame([5], $data['datasets'][0]['data']);
    }

    #[Test]
    public function testBuildChartValoresPorAutor(): void
    {
        $chartBuilder = $this->createMock(ChartBuilderInterface::class);
        $chartMock = new Chart(Chart::TYPE_DOUGHNUT);
        $chartBuilder->expects($this->once())
            ->method('createChart')
            ->with(Chart::TYPE_DOUGHNUT)
            ->willReturn($chartMock);

        $service = $this->createService(null, $chartBuilder);
        $chart = $service->buildChartValoresPorAutor([
            'labels' => ['Machado de Assis'],
            'quantidades' => [5],
            'valores' => [200.0],
        ]);

        $this->assertSame($chartMock, $chart);
        $this->assertSame(Chart::TYPE_DOUGHNUT, $chart->getType());
        $data = $chart->getData();
        $this->assertSame(['Machado de Assis'], $data['labels']);
        $this->assertSame([200.0], $data['datasets'][0]['data']);
    }

    #[Test]
    public function testBuildChartsDelegatesAndReturnsBothCharts(): void
    {
        $repository = $this->createMock(RelatorioRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findMetricasObrasPorAutor')
            ->willReturn([
                ['autor_nome' => 'Guimarães Rosa', 'total_livros' => 2, 'valor_total' => 80.00],
            ]);
        $repository->expects($this->once())
            ->method('findMetricasObrasPorAssunto')
            ->willReturn([]);

        $chartBuilder = $this->createMock(ChartBuilderInterface::class);
        $chartBuilder->expects($this->exactly(2))
            ->method('createChart')
            ->willReturnCallback(function (string $type) {
                return new Chart($type);
            });

        $service = $this->createService($repository, $chartBuilder);
        $charts = $service->buildCharts();

        $this->assertArrayHasKey('chartLivros', $charts);
        $this->assertArrayHasKey('chartValores', $charts);
        $this->assertSame(Chart::TYPE_BAR, $charts['chartLivros']->getType());
        $this->assertSame(Chart::TYPE_DOUGHNUT, $charts['chartValores']->getType());
    }
}
