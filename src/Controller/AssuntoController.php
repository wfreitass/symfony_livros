<?php

namespace App\Controller;

use App\Contract\AssuntoServiceInterface;
use App\Entity\Assunto;
use App\Exception\EntityInUseException;
use App\Form\AssuntoType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/assunto')]
class AssuntoController extends AbstractController
{
    public function __construct(
        private readonly AssuntoServiceInterface $assuntoService
    ) {}

    #[Route('/', name: 'app_assunto_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);

        return $this->render('assunto/index.html.twig', [
            'assuntos' => $this->assuntoService->listPaginated($page, 5),
        ]);
    }

    #[Route('/novo', name: 'app_assunto_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $assunto = new Assunto();
        $form = $this->createForm(AssuntoType::class, $assunto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->assuntoService->save($assunto);

            $this->addFlash('success', 'Assunto cadastrado com sucesso!');
            return $this->redirectToRoute('app_assunto_index');
        }

        return $this->render('assunto/new.html.twig', [
            'assunto' => $assunto,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/editar', name: 'app_assunto_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Assunto $assunto): Response
    {
        $form = $this->createForm(AssuntoType::class, $assunto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->assuntoService->save($assunto);

            $this->addFlash('success', 'Assunto atualizado com sucesso!');
            return $this->redirectToRoute('app_assunto_index');
        }

        return $this->render('assunto/edit.html.twig', [
            'assunto' => $assunto,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_assunto_delete', methods: ['POST'])]
    public function delete(Request $request, Assunto $assunto): Response
    {
        if ($this->isCsrfTokenValid('delete' . $assunto->getId(), (string) $request->request->get('_token'))) {
            try {
                $this->assuntoService->delete($assunto);
                $this->addFlash('success', 'Assunto excluído com sucesso!');
            } catch (EntityInUseException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->redirectToRoute('app_assunto_index');
    }
}
