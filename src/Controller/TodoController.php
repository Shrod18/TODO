<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Todo;
use App\Form\TodoType;
use App\Repository\TodoRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TodoController extends AbstractController
{
    /**
     * @Route("/", name="todo_index")
     */
    public function index(TodoRepository $todoRepository, Request $request): Response
    {
        $filter = $request->query->get('filter'); // Récupérer le filtre depuis l'URL

        if ($filter === 'done') {
            $todos = $todoRepository->findBy(['done' => true]);
        } elseif ($filter === 'pending') {
            $todos = $todoRepository->findBy(['done' => false]);
        } else {
            $todos = $todoRepository->findAll();
        }

        return $this->render('todo/index.html.twig', [
            'todos' => $todos,
        ]);
    }

    /**
     * @Route("/todo/new", name="todo_new")
     */
    public function create(Request $request): Response
    {
        $todo = new Todo();
        $form = $this->createForm(TodoType::class, $todo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($todo);
            $entityManager->flush();

            return $this->redirectToRoute('todo_index');
        }

        return $this->render('todo/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/todo/{id}/edit", name="todo_edit")
     */
    public function edit(int $id, TodoRepository $todoRepository, Request $request): Response
    {
        $todo = $todoRepository->find($id);
        if (!$todo) {
            throw new NotFoundHttpException("Tâche introuvable !");
        }

        $form = $this->createForm(TodoType::class, $todo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('todo_index');
        }

        return $this->render('todo/update.html.twig', [
            'todo' => $todo,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/todo/{id}/delete", name="todo_delete", methods={"POST", "DELETE"})
     */
    public function delete(Request $request, TodoRepository $todoRepository, int $id): Response
    {
        // Récupérer l'entité manuellement
        $todo = $todoRepository->find($id);
        if (!$todo) {
            throw $this->createNotFoundException("Tâche introuvable !");
        }

        // Vérifier le token CSRF
        if ($this->isCsrfTokenValid('delete' . $todo->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($todo);
            $entityManager->flush();
        }

        return $this->redirectToRoute('todo_index');
    }

}
