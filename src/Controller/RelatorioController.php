<?php

namespace App\Controller;

use App\Contract\PdfServiceInterface;
use App\Contract\RelatorioServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route('/relatorio')]
class RelatorioController extends AbstractController
{
    public function __construct(
        private readonly RelatorioServiceInterface $relatorioService,
        private readonly PdfServiceInterface $pdfService,
        private readonly ChartBuilderInterface $chartBuilder
    ) {}

    #[Route('/', name: 'app_relatorio_index', methods: ['GET'])]
    public function index(): Response
    {
        $dados = $this->relatorioService->getDadosRelatorioAgrupadosPorAutor();
        $metricas = $this->relatorioService->getMetricasGraficos();

        // 1. Gráfico de Barras: Quantidade de Livros por Autor (Symfony UX)
        $chartLivros = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $chartLivros->setData([
            'labels' => $metricas['autores']['labels'],
            'datasets' => [
                [
                    'label' => 'Qtd. de Obras',
                    'backgroundColor' => 'rgba(13, 110, 253, 0.75)',
                    'borderColor' => 'rgb(13, 110, 253)',
                    'borderWidth' => 1,
                    'borderRadius' => 4,
                    'data' => $metricas['autores']['quantidades'],
                ],
            ],
        ]);
        $chartLivros->setOptions([
            'responsive' => true,
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['stepSize' => 1],
                ],
            ],
        ]);

        // 2. Gráfico de Rosca (Doughnut): Valor Financeiro do Acervo por Autor (Symfony UX)
        $chartValores = $this->chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $chartValores->setData([
            'labels' => $metricas['autores']['labels'],
            'datasets' => [
                [
                    'label' => 'Valor em Acervo (R$)',
                    'backgroundColor' => [
                        '#198754',
                        '#0dcaf0',
                        '#ffc107',
                        '#fd7e14',
                        '#6f42c1',
                        '#20c997',
                        '#d63384'
                    ],
                    'borderWidth' => 1,
                    'data' => $metricas['autores']['valores'],
                ],
            ],
        ]);
        $chartValores->setOptions([
            'responsive' => true,
        ]);

        // Métricas de topo calculadas pela service
        $totais = $this->relatorioService->calcularTotais($dados);

        return $this->render('relatorio/index.html.twig', [
            'dados' => $dados,
            'totalAutores' => $totais['totalAutores'],
            'totalObras' => $totais['totalObras'],
            'valorTotal' => $totais['valorTotal'],
            'chartLivros' => $chartLivros,
            'chartValores' => $chartValores,
        ]);
    }

    #[Route('/pdf', name: 'app_relatorio_pdf', methods: ['GET'])]
    public function exportPdf(): Response
    {
        $dados = $this->relatorioService->getDadosRelatorioAgrupadosPorAutor();
        $metricas = $this->relatorioService->getMetricasGraficos();
        $totais = $this->relatorioService->calcularTotais($dados);

        $html = $this->renderView('relatorio/pdf.html.twig', [
            'dados' => $dados,
            'totalAutores' => $totais['totalAutores'],
            'totalObras' => $totais['totalObras'],
            'valorTotal' => $totais['valorTotal'],
            'metricas' => $metricas,
            'geradoEm' => new \DateTimeImmutable(),
        ]);

        $pdfContent = $this->pdfService->generatePdf($html);

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="relatorio_gerencial_livros.pdf"',
        ]);
    }
}
