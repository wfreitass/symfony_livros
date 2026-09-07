<?php

namespace App\Controller;

use App\Service\PdfService;
use App\Service\RelatorioService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/relatorio')]
class RelatorioController extends AbstractController
{
    public function __construct(
        private readonly RelatorioService $relatorioService,
        private readonly PdfService $pdfService
    ) {}

    #[Route('/', name: 'app_relatorio_index', methods: ['GET'])]
    public function index(): Response
    {
        $dados = $this->relatorioService->getDadosRelatorioAgrupadosPorAutor();

        // Métricas para exibir no dashboard do relatório
        $totalAutores = count($dados);
        $totalObras = 0;
        $valorTotal = 0.0;

        foreach ($dados as $autor) {
            $totalObras += count($autor['livros']);
            foreach ($autor['livros'] as $livro) {
                $valorTotal += (float) $livro['valor'];
            }
        }

        return $this->render('relatorio/index.html.twig', [
            'dados' => $dados,
            'totalAutores' => $totalAutores,
            'totalObras' => $totalObras,
            'valorTotal' => $valorTotal,
        ]);
    }

    #[Route('/pdf', name: 'app_relatorio_pdf', methods: ['GET'])]
    public function exportPdf(): Response
    {
        $dados = $this->relatorioService->getDadosRelatorioAgrupadosPorAutor();

        $html = $this->renderView('relatorio/pdf.html.twig', [
            'dados' => $dados,
            'geradoEm' => new \DateTimeImmutable(),
        ]);

        $pdfContent = $this->pdfService->generatePdf($html);

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="relatorio_livros_por_autor.pdf"',
        ]);
    }
}
