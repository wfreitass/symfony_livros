<?php

namespace App\Controller;

use App\Contract\PdfServiceInterface;
use App\Contract\RelatorioServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\RateLimit;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/relatorio')]
#[RateLimit('main_app')]
class RelatorioController extends AbstractController
{
    public function __construct(
        private readonly RelatorioServiceInterface $relatorioService,
        private readonly PdfServiceInterface $pdfService
    ) {}

    #[Route('/', name: 'app_relatorio_index', methods: ['GET'])]
    public function index(): Response
    {
        $dados = $this->relatorioService->getDadosRelatorioAgrupadosPorAutor();
        $totais = $this->relatorioService->calcularTotais($dados);
        $charts = $this->relatorioService->buildCharts();

        return $this->render('relatorio/index.html.twig', [
            'dados' => $dados,
            'totalAutores' => $totais['totalAutores'],
            'totalObras' => $totais['totalObras'],
            'valorTotal' => $totais['valorTotal'],
            'chartLivros' => $charts['chartLivros'],
            'chartValores' => $charts['chartValores'],
        ]);
    }

    #[Route('/pdf', name: 'app_relatorio_pdf', methods: ['GET'])]
    #[RateLimit('pdf_export')]
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
