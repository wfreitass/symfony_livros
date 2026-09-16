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
        $todosLivros = $this->livroService->listAll();
        $ultimosLivros = $this->livroService->listFiveLast();
        $autores = $this->autorService->listAll();
        $assuntos = $this->assuntoService->listAll();

        $dadosRelatorio = $this->relatorioService->getDadosRelatorioAgrupadosPorAutor();
        $totais = $this->relatorioService->calcularTotais($dadosRelatorio);

        return $this->render('home/index.html.twig', [
            'totalLivros' => count($todosLivros),
            'totalAutores' => count($autores),
            'totalAssuntos' => count($assuntos),
            'valorTotalAcervo' => $totais['valorTotal'],
            'ultimosLivros' => $ultimosLivros,
        ]);
    }
}
