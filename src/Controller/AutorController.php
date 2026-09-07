<?php

namespace App\Controller;

use App\Contract\AutorServiceInterface;
use App\Entity\Autor;
use App\Exception\EntityInUseException;
use App\Form\AutorType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/autor')]
class AutorController extends AbstractController
{
    public function __construct(
        private readonly AutorServiceInterface $autorService
    ) {}

    #[Route('/', name: 'app_autor_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('autor/index.html.twig', [
            'autores' => $this->autorService->listAll(),
        ]);
    }

    #[Route('/novo', name: 'app_autor_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $autor = new Autor();
        $form = $this->createForm(AutorType::class, $autor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->autorService->save($autor);

            $this->addFlash('success', 'Autor cadastrado com sucesso!');
            return $this->redirectToRoute('app_autor_index');
        }

        return $this->render('autor/new.html.twig', [
            'autor' => $autor,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/editar', name: 'app_autor_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Autor $autor): Response
    {
        $form = $this->createForm(AutorType::class, $autor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->autorService->save($autor);

            $this->addFlash('success', 'Autor atualizado com sucesso!');
            return $this->redirectToRoute('app_autor_index');
        }

        return $this->render('autor/edit.html.twig', [
            'autor' => $autor,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_autor_delete', methods: ['POST'])]
    public function delete(Request $request, Autor $autor): Response
    {
        if ($this->isCsrfTokenValid('delete' . $autor->getId(), (string) $request->request->get('_token'))) {
            try {
                $this->autorService->delete($autor);
                $this->addFlash('success', 'Autor excluído com sucesso!');
            } catch (EntityInUseException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->redirectToRoute('app_autor_index');
    }
}
