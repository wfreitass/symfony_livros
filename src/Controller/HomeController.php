<?php

namespace App\Controller;

use App\Contract\AssuntoServiceInterface;
use App\Contract\AutorServiceInterface;
use App\Contract\LivroServiceInterface;
use App\Contract\RelatorioServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly LivroServiceInterface $livroService,
        private readonly AutorServiceInterface $autorService,
        private readonly AssuntoServiceInterface $assuntoService,
        private readonly RelatorioServiceInterface $relatorioService
    ) {}

    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(): Response
    {
        $livros = $this->livroService->listAll();
        $autores = $this->autorService->listAll();
        $assuntos = $this->assuntoService->listAll();

        $dadosRelatorio = $this->relatorioService->getDadosRelatorioAgrupadosPorAutor();
        $totais = $this->relatorioService->calcularTotais($dadosRelatorio);

        return $this->render('home/index.html.twig', [
            'totalLivros' => count($livros),
            'totalAutores' => count($autores),
            'totalAssuntos' => count($assuntos),
            'valorTotalAcervo' => $totais['valorTotal'],
            'ultimosLivros' => array_slice($livros, 0, 5),
        ]);
    }
}
