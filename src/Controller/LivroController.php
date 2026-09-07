<?php

namespace App\Controller;

use App\Contract\LivroServiceInterface;
use App\Entity\Livro;
use App\Form\LivroType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/livro')]
class LivroController extends AbstractController
{
    public function __construct(
        private readonly LivroServiceInterface $livroService
    ) {}

    #[Route('/', name: 'app_livro_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('livro/index.html.twig', [
            'livros' => $this->livroService->listAll(),
        ]);
    }

    #[Route('/novo', name: 'app_livro_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $livro = new Livro();
        $form = $this->createForm(LivroType::class, $livro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->livroService->save($livro);

            $this->addFlash('success', 'Livro cadastrado com sucesso!');
            return $this->redirectToRoute('app_livro_index');
        }

        return $this->render('livro/new.html.twig', [
            'livro' => $livro,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/editar', name: 'app_livro_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Livro $livro): Response
    {
        $form = $this->createForm(LivroType::class, $livro);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->livroService->save($livro);

            $this->addFlash('success', 'Livro atualizado com sucesso!');
            return $this->redirectToRoute('app_livro_index');
        }

        return $this->render('livro/edit.html.twig', [
            'livro' => $livro,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_livro_delete', methods: ['POST'])]
    public function delete(Request $request, Livro $livro): Response
    {
        if ($this->isCsrfTokenValid('delete' . $livro->getId(), (string) $request->request->get('_token'))) {
            $this->livroService->delete($livro);
            $this->addFlash('success', 'Livro excluído com sucesso!');
        }

        return $this->redirectToRoute('app_livro_index');
    }
}
